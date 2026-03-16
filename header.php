<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/protected_routes.php';
enforce_auth_middleware();
$user = current_user();
?>
<!doctype html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8') ?></title>
  <link rel="stylesheet" href="css/style.css?v=<?= time() ?>">
</head>
<body>
  <header class="site-header">
    <div class="container header-inner">
      <a class="brand" href="/">Login Starter</a>
      <nav class="nav">
        <?php if ($user): ?>
          <a href="/dashboard.php">Dashboard</a>
          <?php if ($user['role'] === 'admin'): ?>
            <a href="/admin.php">Admin</a>
          <?php endif; ?>
          <a href="/logout.php">Logout</a>
        <?php else: ?>
          <a href="/login.php">Login</a>
        <?php endif; ?>
      </nav>
    </div>
  </header>
  <main class="container main-content">
