<?php

return [
    'app_name' => getenv('APP_NAME') ?: 'Darwin Art Store',
    'business_email' => getenv('ORDER_HANDLING_EMAIL') ?: 'orders@example.com',
    'db' => [
        'host' => getenv('DB_HOST') ?: '127.0.0.1',
        'port' => getenv('DB_PORT') ?: '3306',
        'name' => getenv('DB_NAME') ?: 'art_store',
        'user' => getenv('DB_USER') ?: 'root',
        'pass' => getenv('DB_PASSWORD') ?: '',
    ],
];
