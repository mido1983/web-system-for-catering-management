<?php
return [
    'db' => [
        'host' => 'localhost',
        'name' => 'catering_db',
        'user' => 'db_user',
        'pass' => 'db_pass',
        'charset' => 'utf8mb4',
    ],
    'app' => [
        'name_he' => 'מערכת ניהול הסעדה',
        'base_url' => '',
        'timezone' => 'Asia/Jerusalem',
    ],
    'session' => [
        'name' => 'catering_sess',
        'secure' => false,
        'samesite' => 'Lax',
    ],
    'logging' => [
        'file' => __DIR__ . '/../logs/app.log',
    ],
];