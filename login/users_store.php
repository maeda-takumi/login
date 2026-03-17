<?php

declare(strict_types=1);

function users_file_path(): string
{
    return __DIR__ . '/users.json';
}

function normalize_status(mixed $status): string
{
    return ((string)$status) === 'active' ? 'active' : 'inactive';
}
function load_users(): array
{
    $path = users_file_path();
    if (!is_file($path)) {
        $defaultPassword = 'password123';
        $default = [[
            'id' => 1,
            'line_name' => 'admin',
            'email' => 'admin@example.com',
            'password' => $defaultPassword,
            'password_hash' => password_hash($defaultPassword, PASSWORD_DEFAULT),
            'status' => 'active',
        ]];
        save_users($default);
        return $default;
    }

    $decoded = json_decode((string)file_get_contents($path), true);
    if (!is_array($decoded)) {
        return [];
    }

    $normalized = [];
    foreach ($decoded as $row) {
        if (!is_array($row)) {
            continue;
        }

        $legacyUsername = trim((string)($row['username'] ?? ''));
        $email = trim((string)($row['email'] ?? ''));
        if ($email === '') {
            $email = $legacyUsername;
        }

        $lineName = trim((string)($row['line_name'] ?? ''));
        if ($lineName === '') {
            $lineName = $legacyUsername !== '' ? $legacyUsername : $email;
        }
        $password = (string)($row['password'] ?? '');
        $passwordHash = (string)($row['password_hash'] ?? '');

        $normalized[] = [
            'id' => (int)($row['id'] ?? 0),
            'line_name' => $lineName,
            'email' => $email,
            'password' => $password,
            'password_hash' => $passwordHash,
            'status' => normalize_status($row['status'] ?? 'inactive'),
        ];
    }

    return $normalized;
}

function save_users(array $users): void
{
    file_put_contents(users_file_path(), json_encode(array_values($users), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
}

function next_user_id(array $users): int
{
    $ids = array_column($users, 'id');
    return $ids === [] ? 1 : ((int)max($ids) + 1);
}
