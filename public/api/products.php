<?php

require __DIR__ . '/../../src/bootstrap.php';

header('Access-Control-Allow-Origin: http://localhost:5173');
header('Content-Type: application/json');

try {
    echo json_encode(app('products')->available());
} catch (Throwable $error) {
    http_response_code(500);
    echo json_encode([
        'error' => dbErrorMessage($error)
    ]);
}