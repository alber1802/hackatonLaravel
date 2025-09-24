<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Google OAuth</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded-lg shadow-md max-w-md w-full">
        <h1 class="text-2xl font-bold text-center mb-6">Test Google OAuth</h1>
        
        <div class="space-y-4">
            <a href="/auth/redirect" 
               class="w-full bg-blue-500 hover:bg-blue-600 text-white font-bold py-3 px-4 rounded-lg text-center block transition duration-200">
                🔐 Login with Google
            </a>
            
            <div class="text-sm text-gray-600">
                <p><strong>Configuración requerida:</strong></p>
                <ul class="list-disc list-inside mt-2 space-y-1">
                    <li>GOOGLE_CLIENT_ID en .env</li>
                    <li>GOOGLE_CLIENT_SECRET en .env</li>
                    <li>GOOGLE_REDIRECT_URI en .env</li>
                </ul>
            </div>
            
            <div class="border-t pt-4">
                <h3 class="font-semibold mb-2">Rutas disponibles:</h3>
                <ul class="text-sm space-y-1">
                    <li><code class="bg-gray-100 px-2 py-1 rounded">GET /auth/redirect</code></li>
                    <li><code class="bg-gray-100 px-2 py-1 rounded">GET /auth/callback</code></li>
                    <li><code class="bg-gray-100 px-2 py-1 rounded">GET /api/auth/user</code></li>
                    <li><code class="bg-gray-100 px-2 py-1 rounded">GET /api/auth/token</code></li>
                </ul>
            </div>
        </div>
    </div>
</body>
</html>
