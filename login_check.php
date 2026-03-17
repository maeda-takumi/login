<?php

declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (empty($_SESSION['logged_in'])) {
    $scriptDir = str_replace('\\', '/', dirname((string)($_SERVER['SCRIPT_NAME'] ?? '/')));
    $basePath = $scriptDir === '/' ? '' : rtrim($scriptDir, '/');
    $loginPath = $basePath . '/login/index.php';

    header('Location: ' . $loginPath . '?next=' . rawurlencode('/?page=index'));
    exit;
}
