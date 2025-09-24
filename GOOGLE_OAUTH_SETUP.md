# Google OAuth2 Authentication with Laravel Socialite

## Configuración Completa

### 1. Variables de Entorno (.env)

Agrega estas variables a tu archivo `.env`:

```env
GOOGLE_CLIENT_ID=tu_google_client_id
GOOGLE_CLIENT_SECRET=tu_google_client_secret
GOOGLE_REDIRECT_URI=http://localhost:8000/auth/callback
```

### 2. Rutas Disponibles

#### Rutas Web (routes/web.php)
- `GET /auth/redirect` - Redirige a Google para autenticación
- `GET /auth/callback` - Maneja el callback de Google

#### Rutas API (routes/api.php)
- `GET /api/auth/token` - Obtiene el token del usuario autenticado
- `GET /api/auth/user` - Obtiene información del usuario por token
- `POST /api/auth/logout` - Cierra sesión del usuario

### 3. Flujo de Autenticación

1. **Iniciar autenticación**: El usuario hace clic en "Login with Google"
2. **Redirección**: Se redirige a `/auth/redirect`
3. **Google OAuth**: El usuario se autentica en Google
4. **Callback**: Google redirige a `/auth/callback`
5. **Procesamiento**: Se crea/actualiza el usuario y se genera un token
6. **Redirección final**: Se redirige al frontend con el token

### 4. Ejemplo de Uso en React

```jsx
// GoogleAuthButton.jsx
import React from 'react';

const GoogleAuthButton = () => {
  const handleGoogleLogin = () => {
    // Redirigir al endpoint de Laravel
    window.location.href = 'http://localhost:8000/auth/redirect';
  };

  return (
    <button 
      onClick={handleGoogleLogin}
      className="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
    >
      Login with Google
    </button>
  );
};

export default GoogleAuthButton;
```

```jsx
// AuthSuccess.jsx - Página que maneja el token después del login
import React, { useEffect } from 'react';
import { useSearchParams, useNavigate } from 'react-router-dom';

const AuthSuccess = () => {
  const [searchParams] = useSearchParams();
  const navigate = useNavigate();

  useEffect(() => {
    const token = searchParams.get('token');
    
    if (token) {
      // Guardar token en localStorage
      localStorage.setItem('auth_token', token);
      
      // Obtener información del usuario
      fetchUserInfo(token);
      
      // Redirigir al dashboard
      navigate('/dashboard');
    }
  }, [searchParams, navigate]);

  const fetchUserInfo = async (token) => {
    try {
      const response = await fetch('http://localhost:8000/api/auth/user', {
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
        },
      });
      
      if (response.ok) {
        const userData = await response.json();
        localStorage.setItem('user_data', JSON.stringify(userData.user));
      }
    } catch (error) {
      console.error('Error fetching user info:', error);
    }
  };

  return (
    <div className="flex justify-center items-center h-screen">
      <div className="text-center">
        <h2 className="text-2xl font-bold mb-4">Autenticación exitosa</h2>
        <p>Redirigiendo...</p>
      </div>
    </div>
  );
};

export default AuthSuccess;
```

```jsx
// API Helper para hacer requests autenticadas
const API_BASE_URL = 'http://localhost:8000/api';

const apiRequest = async (endpoint, options = {}) => {
  const token = localStorage.getItem('auth_token');
  
  const config = {
    headers: {
      'Content-Type': 'application/json',
      ...(token && { 'Authorization': `Bearer ${token}` }),
      ...options.headers,
    },
    ...options,
  };

  const response = await fetch(`${API_BASE_URL}${endpoint}`, config);
  
  if (!response.ok) {
    if (response.status === 401) {
      // Token expirado o inválido
      localStorage.removeItem('auth_token');
      localStorage.removeItem('user_data');
      window.location.href = '/login';
    }
    throw new Error(`HTTP error! status: ${response.status}`);
  }
  
  return response.json();
};

// Ejemplo de uso
const getUserInfo = () => apiRequest('/auth/user');
const logout = () => apiRequest('/auth/logout', { method: 'POST' });
```

### 5. Configuración de Google Cloud Console

1. Ve a [Google Cloud Console](https://console.cloud.google.com/)
2. Crea un nuevo proyecto o selecciona uno existente
3. Habilita la API de Google+ 
4. Ve a "Credenciales" > "Crear credenciales" > "ID de cliente OAuth 2.0"
5. Configura los orígenes autorizados:
   - `http://localhost:8000` (para desarrollo)
6. Configura las URI de redirección autorizadas:
   - `http://localhost:8000/auth/callback`
7. Copia el Client ID y Client Secret a tu archivo `.env`

### 6. Estructura de la Base de Datos

La tabla `users` ahora incluye estos campos adicionales:

```sql
- google_id (string, nullable, unique)
- access_token (text, nullable) 
- refresh_token (text, nullable)
- password (string, nullable) -- Ahora es nullable para usuarios de Google
```

### 7. Respuesta de la API

#### GET /api/auth/user
```json
{
  "user": {
    "id": 1,
    "name": "Juan Pérez",
    "email": "juan@example.com",
    "google_id": "1234567890"
  }
}
```

#### GET /api/auth/token
```json
{
  "token": "base64_encoded_token_here",
  "user": {
    "id": 1,
    "name": "Juan Pérez", 
    "email": "juan@example.com",
    "google_id": "1234567890"
  }
}
```

### 8. Comandos para Ejecutar

```bash
# Ejecutar migraciones
php artisan migrate

# Iniciar servidor de desarrollo
php artisan serve

# Para desarrollo con Vite (si usas React en el mismo proyecto)
npm run dev
```

### 9. Notas Importantes

- El token generado es simple (base64). Para producción, considera usar Laravel Sanctum
- Los tokens de Google (access_token, refresh_token) se guardan para futuras integraciones
- El password es nullable para usuarios que solo usan Google OAuth
- Se verifica automáticamente el email al crear usuarios de Google

### 10. Próximos Pasos (Opcional)

Para mejorar la seguridad en producción:

1. Instalar Laravel Sanctum: `composer require laravel/sanctum`
2. Publicar configuración: `php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"`
3. Ejecutar migraciones: `php artisan migrate`
4. Reemplazar el sistema de tokens simple por Sanctum tokens
