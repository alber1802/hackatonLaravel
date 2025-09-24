<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard - Classroom</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <div class="bg-blue-500 w-10 h-10 rounded-lg flex items-center justify-center">
                        <i class="fas fa-graduation-cap text-white"></i>
                    </div>
                    <h1 class="ml-3 text-xl font-semibold text-gray-900">Classroom Dashboard</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="flex items-center space-x-3">
                        @if($user->avatar ?? false)
                            <img src="{{ $user->avatar }}" alt="Avatar" class="w-8 h-8 rounded-full">
                        @else
                            <div class="w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center">
                                <i class="fas fa-user text-gray-600 text-sm"></i>
                            </div>
                        @endif
                        <span class="text-sm font-medium text-gray-700">{{ $user->name }}</span>
                    </div>
                    <form action="/logout" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="text-gray-500 hover:text-gray-700 transition-colors">
                            <i class="fas fa-sign-out-alt"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <!-- Messages -->
        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6">
                <div class="flex items-center">
                    <i class="fas fa-check-circle mr-2"></i>
                    <span>{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <span>{{ session('error') }}</span>
                </div>
            </div>
        @endif

        <!-- Welcome Section -->
        <div class="bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg p-6 mb-6 text-white">
            <h2 class="text-2xl font-bold mb-2">¡Bienvenido, {{ $user->name }}!</h2>
            <p class="text-blue-100">Aquí tienes un resumen de tu actividad en Google Classroom</p>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-blue-100 rounded-lg">
                        <i class="fas fa-chalkboard-teacher text-blue-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Clases</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $classroomData['courses_count'] ?? 0 }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-green-100 rounded-lg">
                        <i class="fas fa-tasks text-green-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Tareas</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $classroomData['assignments_count'] ?? 0 }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-yellow-100 rounded-lg">
                        <i class="fas fa-clock text-yellow-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Pendientes</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $classroomData['pending_assignments'] ?? 0 }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-purple-100 rounded-lg">
                        <i class="fas fa-users text-purple-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Estudiantes</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $classroomData['students_count'] ?? 0 }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Dashboard Grid -->
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            <!-- Left Column - Tasks and Statistics -->
            <div class="xl:col-span-2 space-y-6">
                <!-- Task Statistics Component -->
                @include('components.task-statistics', [
                    'statistics' => $classroomData['task_statistics']
                ])

                <!-- Task List Component -->
                @include('components.task-list', [
                    'tasks' => $classroomData['tasks']
                ])
            </div>

            <!-- Right Column - Notifications and Profile -->
            <div class="space-y-6">
                <!-- Notifications Component -->
                @include('components.notifications', [
                    'notifications' => $classroomData['notifications'],
                    'unread_count' => $classroomData['unread_notifications'],
                    'settings' => ['auto_notifications' => true, 'task_reminders' => true]
                ])

                <!-- User Profile Card -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Mi Perfil</h3>
                        <div class="space-y-4">
                            <div class="flex items-center space-x-3">
                                @if($user->avatar)
                                    <img src="{{ $user->avatar }}" alt="Avatar" class="w-12 h-12 rounded-full">
                                @else
                                    <div class="w-12 h-12 bg-gray-300 rounded-full flex items-center justify-center">
                                        <i class="fas fa-user text-gray-600"></i>
                                    </div>
                                @endif
                                <div>
                                    <p class="font-medium text-gray-900">{{ $user->name }}</p>
                                    <p class="text-sm text-gray-600">{{ $user->email }}</p>
                                </div>
                            </div>
                            <div class="pt-4 border-t border-gray-200">
                                <div class="grid grid-cols-2 gap-4 text-center">
                                    <div>
                                        <div class="text-2xl font-bold text-blue-600">{{ $classroomData['courses_count'] }}</div>
                                        <div class="text-xs text-gray-500">Clases</div>
                                    </div>
                                    <div>
                                        <div class="text-2xl font-bold text-green-600">{{ $classroomData['task_statistics']['completion_percentage'] }}%</div>
                                        <div class="text-xs text-gray-500">Completado</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Classroom Courses -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Mis Clases</h3>
                        @if(!empty($classroomData['courses']))
                            <div class="space-y-3 max-h-64 overflow-y-auto">
                                @foreach($classroomData['courses'] as $course)
                                    <div class="border border-gray-200 rounded-lg p-3 hover:bg-gray-50 transition-colors">
                                        <div class="flex items-start justify-between">
                                            <div class="flex-1">
                                                <h4 class="font-medium text-gray-900 text-sm">{{ $course['name'] ?? 'Clase sin nombre' }}</h4>
                                                <p class="text-xs text-gray-600">{{ $course['section'] ?? '' }}</p>
                                            </div>
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                {{ $course['enrollmentCode'] ?? 'N/A' }}
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-6">
                                <i class="fas fa-chalkboard text-gray-300 text-3xl mb-3"></i>
                                <p class="text-gray-500 text-sm">No se encontraron clases</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        @if(!empty($classroomData['recent_activity']))
        <div class="mt-6 bg-white rounded-lg shadow">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Actividad Reciente</h3>
                <div class="space-y-4">
                    @foreach($classroomData['recent_activity'] as $activity)
                        <div class="flex items-start space-x-3">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-bell text-blue-600 text-sm"></i>
                                </div>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm text-gray-900">{{ $activity['description'] }}</p>
                                <p class="text-xs text-gray-500">{{ $activity['time'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <!-- API Status -->
        <div class="mt-6 bg-white rounded-lg shadow">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Estado de la API</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="flex items-center space-x-3">
                        <div class="w-3 h-3 bg-green-400 rounded-full"></div>
                        <span class="text-sm text-gray-600">Google OAuth: Conectado</span>
                    </div>
                    <div class="flex items-center space-x-3">
                        <div class="w-3 h-3 {{ !empty($classroomData['api_connected']) ? 'bg-green-400' : 'bg-yellow-400' }} rounded-full"></div>
                        <span class="text-sm text-gray-600">
                            Classroom API: {{ !empty($classroomData['api_connected']) ? 'Conectado' : 'Limitado' }}
                        </span>
                    </div>
                </div>
                @if(empty($classroomData['api_connected']))
                    <div class="mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                        <div class="flex">
                            <i class="fas fa-exclamation-triangle text-yellow-400 mt-0.5"></i>
                            <div class="ml-3">
                                <h4 class="text-sm font-medium text-yellow-800">Permisos limitados</h4>
                                <p class="text-sm text-yellow-700 mt-1">
                                    Para acceder a todos los datos de Classroom, necesitas configurar permisos adicionales en Google Cloud Console.
                                </p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        // Add some interactivity
        document.addEventListener('DOMContentLoaded', function() {
            // Add loading states for future AJAX calls
            console.log('Dashboard loaded successfully');
            
            // You can add more JavaScript functionality here
            // For example, real-time updates, charts, etc.
        });
    </script>
</body>
</html>
