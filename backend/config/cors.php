<?php

/*
|--------------------------------------------------------------------------
| CORS configuration for the Planer kućanstva API
|--------------------------------------------------------------------------
| Merge this into your existing config/cors.php (it's published by
| Laravel's default install). The frontend (Vite dev server) runs on
| http://localhost:5173 by default.
*/

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie', 'storage/*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'http://localhost:5173',
        'http://127.0.0.1:5173',
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    // We use Sanctum Bearer tokens (not cookie-based SPA auth), so
    // credentials/cookies are not required.
    'supports_credentials' => false,
];
