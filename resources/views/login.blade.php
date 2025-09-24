<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Classroom Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded-2xl shadow-xl max-w-md w-full mx-4">
        <!-- Logo/Header -->
        <div class="text-center mb-8">
            <div class="bg-blue-500 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-graduation-cap text-white text-2xl"></i>
            </div>
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Classroom Dashboard</h1>
            <p class="text-gray-600">Accede a tu panel de control educativo</p>
        </div>
        
        <!-- Messages -->
        @if(session('error'))
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-4">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <span>{{ session('error') }}</span>
                </div>
            </div>
        @endif

        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-4">
                <div class="flex items-center">
                    <i class="fas fa-check-circle mr-2"></i>
                    <span>{{ session('success') }}</span>
                </div>
            </div>
        @endif

        <!-- Login Button -->
        <div class="space-y-6">
            <a href="/auth/redirect" 
               class="w-full bg-white border-2 border-gray-200 hover:border-blue-300 text-gray-700 font-semibold py-4 px-6 rounded-xl text-center block transition duration-300 ease-in-out transform hover:scale-105 hover:shadow-lg group">
                <div class="flex items-center justify-center space-x-3">
                    <svg class="w-6 h-6" viewBox="0 0 24 24">
                        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                    </svg>
                    <span class="text-lg">Continuar con Google</span>
                </div>
            </a>
            
            <!-- Features Info -->
            <div class="mt-8 pt-6 border-t border-gray-100">
                <h3 class="text-sm font-semibold text-gray-700 mb-3 text-center">¿Qué puedes hacer?</h3>
                <div class="grid grid-cols-1 gap-3">
                    <div class="flex items-center space-x-3 text-sm text-gray-600">
                        <i class="fas fa-user-circle text-blue-500 w-4"></i>
                        <span>Ver tu perfil de Google</span>
                    </div>
                    <div class="flex items-center space-x-3 text-sm text-gray-600">
                        <i class="fas fa-chalkboard-teacher text-green-500 w-4"></i>
                        <span>Acceder a tus clases de Classroom</span>
                    </div>
                    <div class="flex items-center space-x-3 text-sm text-gray-600">
                        <i class="fas fa-chart-line text-purple-500 w-4"></i>
                        <span>Ver estadísticas y progreso</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <div class="fixed bottom-4 left-0 right-0 text-center">
        <p class="text-gray-500 text-sm">
            Powered by Google Classroom API
        </p>
    </div>
</body>
</html>
