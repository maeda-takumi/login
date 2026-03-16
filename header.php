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
  <link rel="stylesheet" href="<?= htmlspecialchars(app_asset('/css/style.css'), ENT_QUOTES, 'UTF-8') ?>?v=<?= time() ?>">
</head>
<body>
  <header class="site-header">
    <div class="container header-inner">
      <a class="brand" href="<?= htmlspecialchars(app_path('/'), ENT_QUOTES, 'UTF-8') ?>">Login Starter</a>
      <nav class="nav">
        <?php if ($user): ?>
          <a href="<?= htmlspecialchars(app_path('/dashboard.php'), ENT_QUOTES, 'UTF-8') ?>">Dashboard</a>
          <?php if ($user['role'] === 'admin'): ?>
            <a href="<?= htmlspecialchars(app_path('/admin.php'), ENT_QUOTES, 'UTF-8') ?>">Admin</a>
          <?php endif; ?>
          <a href="<?= htmlspecialchars(app_path('/logout.php'), ENT_QUOTES, 'UTF-8') ?>">Logout</a>
        <?php else: ?>
          <a href="<?= htmlspecialchars(app_path('/login.php'), ENT_QUOTES, 'UTF-8') ?>">Login</a>
        <?php endif; ?>
      </nav>
    </div>
  </header>
  <main class="container main-content">
