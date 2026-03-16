<?php

declare(strict_types=1);

require_once __DIR__ . '/db.php';

function current_user(): ?array
{
    if (!isset($_SESSION['user_id'])) {
        return null;
    }

    static $cachedUser = null;
    if ($cachedUser !== null) {
        return $cachedUser;
    }

    $stmt = db()->prepare('SELECT id, mail, status, line_name, role FROM users WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $_SESSION['user_id']]);
    $user = $stmt->fetch();

    if (!$user || $user['status'] !== 'active') {
        logout();
        return null;
    }

    $cachedUser = $user;
    return $cachedUser;
}

function is_logged_in(): bool
{
    return current_user() !== null;
}

function is_admin(): bool
{
    $user = current_user();
    return $user !== null && $user['role'] === 'admin';
}

function login(string $mail, string $password): bool
{
    $stmt = db()->prepare('SELECT * FROM users WHERE mail = :mail LIMIT 1');
    $stmt->execute([':mail' => strtolower(trim($mail))]);
    $user = $stmt->fetch();

    if (!$user || $user['status'] !== 'active') {
        return false;
    }

    if (!password_verify($password, $user['password'])) {
        return false;
    }

    $_SESSION['user_id'] = (int)$user['id'];
    return true;
}

function logout(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}

function require_login(): void
{
    if (!is_logged_in()) {
        $next = rawurlencode(current_path_with_query());
        header('Location: /login.php?next=' . $next);
        exit;
    }
}

function require_admin(): void
{
    require_login();
    if (!is_admin()) {
        http_response_code(403);
        echo '403 Forbidden';
        exit;
    }
}

function current_path_with_query(): string
{
    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    return $uri === '' ? '/' : $uri;
}

function safe_next_path(?string $path): ?string
{
    if ($path === null || $path === '') {
        return null;
    }

    if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '//')) {
        return null;
    }

    return str_starts_with($path, '/') ? $path : '/' . ltrim($path, '/');
}
