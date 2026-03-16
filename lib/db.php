<?php

declare(strict_types=1);

require_once __DIR__ . '/../config.php';

function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dir = dirname(DB_PATH);
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }

    $pdo = new PDO('sqlite:' . DB_PATH);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    initialize_schema($pdo);

    return $pdo;
}

function initialize_schema(PDO $pdo): void
{
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            mail TEXT NOT NULL UNIQUE,
            password TEXT NOT NULL,
            status TEXT NOT NULL DEFAULT "active",
            line_name TEXT,
            role TEXT NOT NULL DEFAULT "user",
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
        )'
    );

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS app_settings (
            key TEXT PRIMARY KEY,
            value TEXT NOT NULL
        )'
    );

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS protected_routes (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            pattern TEXT NOT NULL UNIQUE,
            enabled INTEGER NOT NULL DEFAULT 1,
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
        )'
    );

    $stmt = $pdo->prepare('SELECT COUNT(*) FROM users');
    $stmt->execute();
    $count = (int)$stmt->fetchColumn();

    if ($count === 0) {
        $seed = $pdo->prepare('INSERT INTO users (mail, password, status, line_name, role) VALUES (:mail, :password, :status, :line_name, :role)');
        $seed->execute([
            ':mail' => 'admin@example.com',
            ':password' => password_hash('password123', PASSWORD_DEFAULT),
            ':status' => 'active',
            ':line_name' => 'Admin',
            ':role' => 'admin',
        ]);
    }

    set_setting_if_missing($pdo, 'default_redirect_path', app_path('/dashboard.php'));
}

function set_setting_if_missing(PDO $pdo, string $key, string $value): void
{
    $stmt = $pdo->prepare('INSERT OR IGNORE INTO app_settings (key, value) VALUES (:key, :value)');
    $stmt->execute([
        ':key' => $key,
        ':value' => $value,
    ]);
}

function get_setting(string $key, ?string $default = null): ?string
{
    $stmt = db()->prepare('SELECT value FROM app_settings WHERE key = :key LIMIT 1');
    $stmt->execute([':key' => $key]);
    $value = $stmt->fetchColumn();

    if ($value === false) {
        return $default;
    }

    return (string)$value;
}

function set_setting(string $key, string $value): void
{
    $stmt = db()->prepare('INSERT INTO app_settings (key, value) VALUES (:key, :value) ON CONFLICT(key) DO UPDATE SET value = excluded.value');
    $stmt->execute([
        ':key' => $key,
        ':value' => $value,
    ]);
}
