<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';

function should_skip_protection(string $path): bool
{
    $whitelist = [
        app_path('/login.php'),
        app_path('/logout.php'),
        app_path('/admin.php'),
    ];

    if (in_array($path, $whitelist, true)) {
        return true;
    }

    return str_starts_with($path, app_path('/css/')) || str_starts_with($path, app_path('/js/'));
}

function route_matches_request(string $path, string $fullUrl, string $pattern): bool
{
    if (str_starts_with($pattern, '/')) {
        foreach (candidate_paths_for_matching($path) as $candidatePath) {
            if (route_pattern_matches($candidatePath, $pattern)) {
                return true;
            }
        }

        return false;
    }

    $scheme = parse_url($pattern, PHP_URL_SCHEME);
    if (is_string($scheme) && in_array(strtolower($scheme), ['http', 'https'], true)) {
        return route_pattern_matches($fullUrl, $pattern);
    }

    return false;
}
function candidate_paths_for_matching(string $path): array
{
    $candidates = [$path];

    if (BASE_PATH !== '' && str_starts_with($path, BASE_PATH . '/')) {
        $withoutBase = substr($path, strlen(BASE_PATH));
        if (is_string($withoutBase) && $withoutBase !== '') {
            $candidates[] = $withoutBase;
        }
    }

    return array_values(array_unique($candidates));
}
function route_pattern_matches(string $path, string $pattern): bool
{
    $escaped = preg_quote($pattern, '#');
    $regex = '#^' . str_replace('\\*', '.*', $escaped) . '$#';
    return (bool)preg_match($regex, $path);
}

function is_protected_path(string $path): bool
{
    $fullUrl = current_full_url_with_query();
    $stmt = db()->query('SELECT pattern FROM protected_routes WHERE enabled = 1 ORDER BY LENGTH(pattern) DESC');
    $patterns = $stmt->fetchAll(PDO::FETCH_COLUMN);

    foreach ($patterns as $pattern) {
        if (route_matches_request($path, $fullUrl, (string)$pattern)) {
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
        header('Location: ' . app_path('/login.php') . '?next=' . $next);
        exit;
    }
}
