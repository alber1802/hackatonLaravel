<?php

/**
 * Configuración de cURL para desarrollo local
 * Este archivo se ejecuta antes de que Laravel inicie completamente
 */

if (env('APP_ENV') === 'local') {
    // Configurar opciones por defecto de cURL para deshabilitar verificación SSL
    $curlDefaults = [
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 3,
    ];

    // Configurar stream context por defecto para HTTPS
    $context = stream_context_create([
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true,
        ],
        'http' => [
            'timeout' => 30,
        ]
    ]);
    
    stream_context_set_default($context);
}
