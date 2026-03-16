<?php

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('APP_NAME', 'Login Starter');
define('DB_PATH', __DIR__ . '/data/app.sqlite');

$scriptName = str_replace('\\', '/', (string)($_SERVER['SCRIPT_NAME'] ?? ''));
$basePath = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
if ($basePath === '.' || $basePath === '/') {
    $basePath = '';
}

define('BASE_PATH', $basePath);

function app_path(string $path = '/'): string
{
    $normalized = '/' . ltrim($path, '/');

    if (BASE_PATH === '') {
        return $normalized;
    }

    return BASE_PATH . $normalized;
}

function app_asset(string $path): string
{
    return app_path($path);
}
