<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin requests.
    | This is critical when using Sanctum with a SPA on a separate domain.
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie', 'login', 'logout', 'admin/*'],


    'allowed_methods' => ['*'], 

    'allowed_origins' => [
        'http://localhost:8080', 
        'http://laravel.test',    
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'], 
    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true, 
];

