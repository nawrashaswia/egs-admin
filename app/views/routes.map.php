<?php
use App\Services\AuthService;

return [
    'views' => [
        '/dashboard' => 'pages/dashboard',
    ],
    
    'controllers' => [
        [
            'method' => 'get',
            'path' => '/',
            'type' => 'class',
            'handler' => [AuthService::class, 'handleRoot'],
        ],
        [
            'method' => 'get',
            'path' => '/login',
            'type' => 'class',
            'handler' => [AuthService::class, 'showLogin'],
        ],
        [
            'method' => 'post',
            'path' => '/login/submit',
            'type' => 'class',
            'handler' => [AuthService::class, 'submitLogin'],
        ],
        [
            'method' => 'get',
            'path' => '/logout',
            'type' => 'class',
            'handler' => [AuthService::class, 'logout'],
        ],
        [
            'method' => 'post',
            'path' => '/api/errors/report',
            'type' => 'file',
            'handler' => 'app/api/errors/errors_api.php',
        ]
    ]
];
