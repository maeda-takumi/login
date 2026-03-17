<?php

declare(strict_types=1);

function users_file_path(): string
{
    return __DIR__ . '/users.json';
}

function load_users(): array
{
    $path = users_file_path();
    if (!is_file($path)) {
        $default = [[
            'id' => 1,
            'username' => 'admin',
            'password_hash' => password_hash('password123', PASSWORD_DEFAULT),
            'status' => 'active',
        ]];
        save_users($default);
        return $default;
    }

    $decoded = json_decode((string)file_get_contents($path), true);
    return is_array($decoded) ? $decoded : [];
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
