<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ConfigureCurl
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Configurar cURL para desarrollo local
        if (app()->environment('local')) {
            // Configurar opciones de cURL para deshabilitar verificación SSL
            $curlOptions = [
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_CONNECTTIMEOUT => 10,
            ];
            
            // Aplicar configuración global de cURL
            foreach ($curlOptions as $option => $value) {
                curl_setopt_array(curl_init(), [$option => $value]);
            }
        }

        return $next($request);
    }
}
