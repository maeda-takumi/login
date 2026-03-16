<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';

function should_skip_protection(string $path): bool
{
    $whitelist = [
        '/login.php',
        '/logout.php',
    ];

    if (in_array($path, $whitelist, true)) {
        return true;
    }

    return str_starts_with($path, '/css/') || str_starts_with($path, '/js/');
}

function route_pattern_matches(string $path, string $pattern): bool
{
    $escaped = preg_quote($pattern, '#');
    $regex = '#^' . str_replace('\\*', '.*', $escaped) . '$#';
    return (bool)preg_match($regex, $path);
}

function is_protected_path(string $path): bool
{
    $stmt = db()->query('SELECT pattern FROM protected_routes WHERE enabled = 1 ORDER BY LENGTH(pattern) DESC');
    $patterns = $stmt->fetchAll(PDO::FETCH_COLUMN);

    foreach ($patterns as $pattern) {
        if (route_pattern_matches($path, (string)$pattern)) {
            return true;
        }
    }

    return false;
}

function enforce_auth_middleware(): void
{
    $path = parse_url(current_path_with_query(), PHP_URL_PATH) ?: '/';

    if (should_skip_protection($path)) {
        return;
    }

    if (is_protected_path($path) && !is_logged_in()) {
        $next = rawurlencode(current_path_with_query());
        header('Location: /login.php?next=' . $next);
        exit;
    }
}
