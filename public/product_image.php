<?php

require __DIR__ . '/../src/bootstrap.php';

$productNo = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1],
]);

if (!$productNo) {
    http_response_code(404);
    exit;
}

try {
    $product = app('products')->find((int) $productNo);
} catch (Throwable) {
    http_response_code(404);
    exit;
}

$imagePath = $product['image_path'] ?? null;
$filename = is_string($imagePath) ? basename($imagePath) : '';

if (!preg_match('/^[a-f0-9]{32}\.(jpg|png|webp)$/', $filename)) {
    http_response_code(404);
    exit;
}

$storageDirectory = dirname(__DIR__) . '/storage/product-images';
$fullPath = $storageDirectory . '/' . $filename;
$realStorageDirectory = realpath($storageDirectory);
$realFullPath = realpath($fullPath);

if (
    !$realStorageDirectory ||
    !$realFullPath ||
    !str_starts_with($realFullPath, $realStorageDirectory . DIRECTORY_SEPARATOR) ||
    !is_file($realFullPath)
) {
    http_response_code(404);
    exit;
}

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = $finfo ? finfo_file($finfo, $realFullPath) : false;
if ($finfo) {
    finfo_close($finfo);
}

$allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
if (!is_string($mimeType) || !in_array($mimeType, $allowedTypes, true)) {
    http_response_code(404);
    exit;
}

header('Content-Type: ' . $mimeType);
header('Content-Length: ' . filesize($realFullPath));
header('X-Content-Type-Options: nosniff');
readfile($realFullPath);
