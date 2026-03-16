<?php

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('APP_NAME', 'Login Starter');
define('DB_PATH', __DIR__ . '/data/app.sqlite');
define('BASE_URL', '');
