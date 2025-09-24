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
            'api_connected' => false
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
                    $data['courses'] = array_slice($coursesData['courses'], 0, 5); // Limit to 5 courses
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
                }
            } else {
                // If we can't access Classroom API, try to get basic profile info
                $this->tryGetBasicProfile($user, $data);
            }

            // Generate some sample recent activity
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
}
