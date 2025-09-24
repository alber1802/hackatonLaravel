<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class CurlServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Configurar cURL para desarrollo local
        if ($this->app->environment('local')) {
            // Configurar Guzzle HTTP client para Socialite
            $this->app->resolving(\GuzzleHttp\Client::class, function ($client, $app) {
                return new \GuzzleHttp\Client([
                    'verify' => false,
                    'timeout' => 30,
                    'connect_timeout' => 10,
                    'curl' => [
                        CURLOPT_SSL_VERIFYPEER => false,
                        CURLOPT_SSL_VERIFYHOST => false,
                    ]
                ]);
            });

            // Configurar HTTP client por defecto
            config([
                'http.verify' => false,
                'http.curl' => [
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_VERIFYHOST => false,
                    CURLOPT_TIMEOUT => 30,
                    CURLOPT_CONNECTTIMEOUT => 10,
                ]
            ]);
        }
    }
}
