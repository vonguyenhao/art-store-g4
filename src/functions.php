<?php

declare(strict_types=1);

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function money(float|string $amount): string
{
    return '$' . number_format((float) $amount, 2);
}

function redirect(string $path): never
{
    header('Location: ' . $path);
    exit;
}

function app(string $key): mixed
{
    global $container;
    return $container[$key] ?? throw new InvalidArgumentException("Service [$key] is not registered.");
}

function dbErrorMessage(Throwable $error): string
{
    return 'Database is not ready. Import database/schema.sql and check DB_HOST, DB_NAME, DB_USER, and DB_PASSWORD.';
}
