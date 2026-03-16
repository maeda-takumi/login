<?php

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    $sessionLifetime = 60 * 60 * 24 * 3; // 3日間
    session_set_cookie_params([
        'lifetime' => $sessionLifetime,
        'path' => '/',
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    ini_set('session.gc_maxlifetime', (string)$sessionLifetime);
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
