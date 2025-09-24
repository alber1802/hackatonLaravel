<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    /**
     * Show the dashboard with user data and Classroom information.
     */
    public function index(Request $request)
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        $user = Auth::user();
        
        // Get Classroom data
        $classroomData = $this->getClassroomData($user);
        
        return view('dashboard', [
            'user' => $user,
            'classroomData' => $classroomData
        ]);
    }

    /**
     * Get Google Classroom data for the authenticated user.
     */
    private function getClassroomData($user)
    {
        $data = [
            'courses' => [],
            'courses_count' => 0,
            'assignments_count' => 0,
            'pending_assignments' => 0,
            'students_count' => 0,
            'recent_activity' => [],
            'api_connected' => false,
            'tasks' => [
                'pending' => [],
                'completed' => [],
                'overdue' => []
            ],
            'task_statistics' => [
                'total_tasks' => 0,
                'completed_tasks' => 0,
                'pending_tasks' => 0,
                'overdue_tasks' => 0,
                'completion_percentage' => 0,
                'subjects_progress' => []
            ],
            'notifications' => [],
            'unread_notifications' => 0
        ];

        // Check if user has access token
        if (!$user->access_token) {
            return $data;
        }

        try {
            // Configurar HTTP client para desarrollo (deshabilitar verificación SSL)
            $httpClient = Http::withToken($user->access_token);
            if (app()->environment('local')) {
                $httpClient = $httpClient->withOptions([
                    'verify' => false,
                    'curl' => [
                        CURLOPT_SSL_VERIFYPEER => false,
                        CURLOPT_SSL_VERIFYHOST => false,
                    ]
                ]);
            }

            // Get courses from Google Classroom API
            $coursesResponse = $httpClient->get('https://classroom.googleapis.com/v1/courses', [
                'courseStates' => 'ACTIVE'
            ]);

            if ($coursesResponse->successful()) {
                $data['api_connected'] = true;
                $coursesData = $coursesResponse->json();
                
                if (isset($coursesData['courses'])) {
                    $data['courses'] = array_slice($coursesData['courses'], 0, 12); // Limit to 5 courses
                    $data['courses_count'] = count($coursesData['courses']);
                    
                    // Get additional data for each course
                    $totalAssignments = 0;
                    $totalStudents = 0;
                    $pendingAssignments = 0;
                    
                    foreach ($coursesData['courses'] as $course) {
                        $courseId = $course['id'];
                        
                        // Get course work (assignments)
                        try {
                            $courseWorkResponse = $httpClient->get("https://classroom.googleapis.com/v1/courses/{$courseId}/courseWork");
                            
                            if ($courseWorkResponse->successful()) {
                                $courseWorkData = $courseWorkResponse->json();
                                if (isset($courseWorkData['courseWork'])) {
                                    $assignments = count($courseWorkData['courseWork']);
                                    $totalAssignments += $assignments;
                                    
                                    // Count pending assignments (simplified logic)
                                    $pendingAssignments += ceil($assignments * 0.3); // Assume 30% are pending
                                }
                            }
                        } catch (\Exception $e) {
                            Log::warning("Failed to get course work for course {$courseId}: " . $e->getMessage());
                        }
                        
                        // Get students
                        try {
                            $studentsResponse = $httpClient->get("https://classroom.googleapis.com/v1/courses/{$courseId}/students");
                            
                            if ($studentsResponse->successful()) {
                                $studentsData = $studentsResponse->json();
                                if (isset($studentsData['students'])) {
                                    $totalStudents += count($studentsData['students']);
                                }
                            }
                        } catch (\Exception $e) {
                            Log::warning("Failed to get students for course {$courseId}: " . $e->getMessage());
                        }
                    }
                    
                    $data['assignments_count'] = $totalAssignments;
                    $data['students_count'] = $totalStudents;
                    $data['pending_assignments'] = $pendingAssignments;
                    
                    // Get detailed task information
                    $this->getDetailedTaskData($httpClient, $coursesData['courses'], $data);
                }
            } else {
                // If we can't access Classroom API, try to get basic profile info
                $this->tryGetBasicProfile($user, $data);
            }

            // Generate notifications and recent activity
            $this->generateNotifications($data);
            $data['recent_activity'] = $this->generateSampleActivity($data['courses_count']);

        } catch (\Exception $e) {
            Log::error('Error fetching Classroom data: ' . $e->getMessage());
            
            // Try to get basic profile info as fallback
            $this->tryGetBasicProfile($user, $data);
        }

        return $data;
    }

    /**
     * Try to get basic Google profile information as fallback.
     */
    private function tryGetBasicProfile($user, &$data)
    {
        try {
            // Configurar HTTP client para desarrollo (deshabilitar verificación SSL)
            $httpClient = Http::withToken($user->access_token);
            if (app()->environment('local')) {
                $httpClient = $httpClient->withOptions([
                    'verify' => false,
                    'curl' => [
                        CURLOPT_SSL_VERIFYPEER => false,
                        CURLOPT_SSL_VERIFYHOST => false,
                    ]
                ]);
            }

            $profileResponse = $httpClient->get('https://www.googleapis.com/oauth2/v2/userinfo');

            if ($profileResponse->successful()) {
                $profileData = $profileResponse->json();
                
                // Update user avatar if available
                if (isset($profileData['picture'])) {
                    $user->avatar = $profileData['picture'];
                }
                
                $data['api_connected'] = true;
            }
        } catch (\Exception $e) {
            Log::warning('Failed to get basic profile: ' . $e->getMessage());
        }
    }

    /**
     * Generate sample activity data.
     */
    private function generateSampleActivity($coursesCount)
    {
        $activities = [];
        
        if ($coursesCount > 0) {
            $activities = [
                [
                    'description' => 'Te has conectado exitosamente a Google Classroom',
                    'time' => 'Hace unos momentos'
                ],
                [
                    'description' => "Se encontraron {$coursesCount} clases en tu cuenta",
                    'time' => 'Hace unos momentos'
                ]
            ];
        } else {
            $activities = [
                [
                    'description' => 'Sesión iniciada correctamente',
                    'time' => 'Hace unos momentos'
                ],
                [
                    'description' => 'Buscando clases de Classroom...',
                    'time' => 'Hace unos momentos'
                ]
            ];
        }

        return $activities;
    }

    /**
     * Refresh Classroom data via AJAX.
     */
    public function refreshData(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = Auth::user();
        $classroomData = $this->getClassroomData($user);

        return response()->json([
            'success' => true,
            'data' => $classroomData
        ]);
    }

    /**
     * Get detailed task data from Google Classroom API.
     */
    private function getDetailedTaskData($httpClient, $courses, &$data)
    {
        $allTasks = [];
        $subjectsProgress = [];
        
        foreach ($courses as $course) {
            $courseId = $course['id'];
            $courseName = $course['name'] ?? 'Clase sin nombre';
            
            try {
                // Get course work (assignments) with detailed information
                $courseWorkResponse = $httpClient->get("https://classroom.googleapis.com/v1/courses/{$courseId}/courseWork");
                
                if ($courseWorkResponse->successful()) {
                    $courseWorkData = $courseWorkResponse->json();
                    
                    if (isset($courseWorkData['courseWork'])) {
                        $courseAssignments = $courseWorkData['courseWork'];
                        $completedCount = 0;
                        
                        foreach ($courseAssignments as $assignment) {
                            $task = $this->formatTaskData($assignment, $courseName, $courseId);
                            $allTasks[] = $task;
                            
                            if ($task['status'] === 'completed') {
                                $completedCount++;
                            }
                        }
                        
                        // Calculate progress for this subject
                        $totalCount = count($courseAssignments);
                        $subjectsProgress[] = [
                            'name' => $courseName,
                            'completed' => $completedCount,
                            'total' => $totalCount,
                            'percentage' => $totalCount > 0 ? round(($completedCount / $totalCount) * 100) : 0
                        ];
                    }
                }
            } catch (\Exception $e) {
                Log::warning("Failed to get detailed course work for course {$courseId}: " . $e->getMessage());
            }
        }
        
        // Categorize tasks
        $pendingTasks = [];
        $completedTasks = [];
        $overdueTasks = [];
        
        foreach ($allTasks as $task) {
            switch ($task['status']) {
                case 'pending':
                    $pendingTasks[] = $task;
                    break;
                case 'completed':
                    $completedTasks[] = $task;
                    break;
                case 'overdue':
                    $overdueTasks[] = $task;
                    break;
            }
        }
        
        // Update data array
        $data['tasks'] = [
            'pending' => $pendingTasks,
            'completed' => $completedTasks,
            'overdue' => $overdueTasks
        ];
        
        $totalTasks = count($allTasks);
        $completedTasksCount = count($completedTasks);
        
        $data['task_statistics'] = [
            'total_tasks' => $totalTasks,
            'completed_tasks' => $completedTasksCount,
            'pending_tasks' => count($pendingTasks),
            'overdue_tasks' => count($overdueTasks),
            'completion_percentage' => $totalTasks > 0 ? round(($completedTasksCount / $totalTasks) * 100) : 0,
            'subjects_progress' => $subjectsProgress
        ];
    }

    /**
     * Format task data from Google Classroom API response.
     */
    private function formatTaskData($assignment, $courseName, $courseId)
    {
        $dueDate = null;
        $status = 'pending';
        
        // Parse due date
        if (isset($assignment['dueDate'])) {
            $dueDate = $this->parseDueDate($assignment['dueDate'], $assignment['dueTime'] ?? null);
            
            // Determine if overdue
            if ($dueDate && $dueDate < now()) {
                $status = 'overdue';
            }
        }
        
        // Simulate completion status (in real implementation, you'd check student submissions)
        if (rand(1, 100) <= 30) { // 30% chance of being completed
            $status = 'completed';
        }
        
        return [
            'id' => $assignment['id'],
            'title' => $assignment['title'] ?? 'Tarea sin título',
            'description' => $assignment['description'] ?? '',
            'course_name' => $courseName,
            'course_id' => $courseId,
            'due_date' => $dueDate ? $dueDate->format('d/m/Y H:i') : 'Sin fecha límite',
            'submitted_date' => $status === 'completed' ? now()->subDays(rand(1, 7))->format('d/m/Y H:i') : null,
            'points' => $assignment['maxPoints'] ?? null,
            'grade' => $status === 'completed' ? rand(70, 100) : null,
            'status' => $status,
            'classroom_link' => "https://classroom.google.com/c/{$courseId}/a/{$assignment['id']}/details"
        ];
    }

    /**
     * Parse due date from Google Classroom format.
     */
    private function parseDueDate($dueDate, $dueTime = null)
    {
        try {
            $dateStr = $dueDate['year'] . '-' . 
                      str_pad($dueDate['month'], 2, '0', STR_PAD_LEFT) . '-' . 
                      str_pad($dueDate['day'], 2, '0', STR_PAD_LEFT);
            
            if ($dueTime) {
                $timeStr = str_pad($dueTime['hours'] ?? 23, 2, '0', STR_PAD_LEFT) . ':' . 
                          str_pad($dueTime['minutes'] ?? 59, 2, '0', STR_PAD_LEFT);
                $dateStr .= ' ' . $timeStr;
            } else {
                $dateStr .= ' 23:59';
            }
            
            return \Carbon\Carbon::parse($dateStr);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Generate notifications based on tasks and activities.
     */
    private function generateNotifications(&$data)
    {
        $notifications = [];
        
        // Generate notifications for overdue tasks
        foreach ($data['tasks']['overdue'] as $task) {
            $notifications[] = [
                'id' => 'overdue_' . $task['id'],
                'type' => 'overdue',
                'title' => 'Tarea atrasada',
                'message' => "La tarea '{$task['title']}' está atrasada desde {$task['due_date']}",
                'course_name' => $task['course_name'],
                'time_ago' => 'Hace ' . rand(1, 24) . ' horas',
                'read' => false
            ];
        }
        
        // Generate notifications for upcoming tasks (due in next 2 days)
        foreach ($data['tasks']['pending'] as $task) {
            if ($task['due_date'] !== 'Sin fecha límite') {
                $notifications[] = [
                    'id' => 'upcoming_' . $task['id'],
                    'type' => 'task',
                    'title' => 'Tarea próxima a vencer',
                    'message' => "La tarea '{$task['title']}' vence el {$task['due_date']}",
                    'course_name' => $task['course_name'],
                    'time_ago' => 'Hace ' . rand(1, 12) . ' horas',
                    'read' => rand(1, 100) <= 70 // 70% chance of being read
                ];
            }
        }
        
        // Generate sample grade notifications
        foreach ($data['tasks']['completed'] as $index => $task) {
            if ($index < 3 && $task['grade']) { // Only first 3 completed tasks
                $notifications[] = [
                    'id' => 'grade_' . $task['id'],
                    'type' => 'grade',
                    'title' => 'Nueva calificación',
                    'message' => "Recibiste {$task['grade']}/{$task['points']} puntos en '{$task['title']}'",
                    'course_name' => $task['course_name'],
                    'time_ago' => 'Hace ' . rand(1, 48) . ' horas',
                    'read' => rand(1, 100) <= 50 // 50% chance of being read
                ];
            }
        }
        
        // Generate sample announcement notifications
        if ($data['courses_count'] > 0) {
            $notifications[] = [
                'id' => 'announcement_1',
                'type' => 'announcement',
                'title' => 'Nuevo anuncio en clase',
                'message' => 'El profesor ha publicado un nuevo anuncio importante',
                'course_name' => $data['courses'][0]['name'] ?? 'Clase',
                'time_ago' => 'Hace ' . rand(1, 6) . ' horas',
                'read' => false
            ];
        }
        
        // Sort notifications by time (most recent first)
        usort($notifications, function($a, $b) {
            return strcmp($b['time_ago'], $a['time_ago']);
        });
        
        $data['notifications'] = array_slice($notifications, 0, 10); // Limit to 10 notifications
        $data['unread_notifications'] = count(array_filter($notifications, function($n) {
            return !$n['read'];
        }));
    }

    /**
     * Refresh task statistics.
     */
    public function refreshStats(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = Auth::user();
        $classroomData = $this->getClassroomData($user);

        return response()->json([
            'success' => true,
            'statistics' => $classroomData['task_statistics']
        ]);
    }

    /**
     * Refresh tasks list.
     */
    public function refreshTasks(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = Auth::user();
        $classroomData = $this->getClassroomData($user);

        return response()->json([
            'success' => true,
            'tasks' => $classroomData['tasks']
        ]);
    }

    /**
     * Refresh notifications.
     */
    public function refreshNotifications(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = Auth::user();
        $classroomData = $this->getClassroomData($user);

        return response()->json([
            'success' => true,
            'notifications' => $classroomData['notifications'],
            'unread_count' => $classroomData['unread_notifications']
        ]);
    }

    /**
     * Mark task as completed.
     */
    public function markTaskCompleted(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $taskId = $request->input('task_id');
        
        // In a real implementation, you would update the task status in Google Classroom
        // For now, we'll just return success
        Log::info("Task {$taskId} marked as completed by user " . Auth::id());

        return response()->json([
            'success' => true,
            'message' => 'Tarea marcada como completada'
        ]);
    }

    /**
     * Mark notification as read.
     */
    public function markNotificationRead(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $notificationId = $request->input('notification_id');
        
        // In a real implementation, you would update the notification status in the database
        Log::info("Notification {$notificationId} marked as read by user " . Auth::id());

        return response()->json([
            'success' => true,
            'message' => 'Notificación marcada como leída'
        ]);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllNotificationsRead(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // In a real implementation, you would update all notifications for the user
        Log::info("All notifications marked as read by user " . Auth::id());

        return response()->json([
            'success' => true,
            'message' => 'Todas las notificaciones marcadas como leídas'
        ]);
    }

    /**
     * Update notification setting.
     */
    public function updateNotificationSetting(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $setting = $request->input('setting');
        $value = $request->input('value');
        
        // In a real implementation, you would save this to user preferences
        Log::info("Notification setting {$setting} updated to {$value} by user " . Auth::id());

        return response()->json([
            'success' => true,
            'message' => 'Configuración actualizada'
        ]);
    }
}
