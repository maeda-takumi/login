<?php

declare(strict_types=1);

session_start();
require_once __DIR__ . '/users_store.php';
$error = '';
$next = '/?page=index';

if (isset($_GET['next']) && is_string($_GET['next']) && $_GET['next'] !== '') {
    $next = $_GET['next'];
}

if (!empty($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header('Location: ' . $next);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');

    foreach (load_users() as $user) {
        $nameMatches = hash_equals((string)$user['username'], $username);
        $passMatches = password_verify($password, (string)$user['password_hash']);
        $isActive = ((string)($user['status'] ?? 'inactive')) === 'active';
        if ($nameMatches && $passMatches && $isActive) {
            $_SESSION['logged_in'] = true;
            $_SESSION['login_user'] = (string)$user['username'];
            header('Location: ' . $next);
            exit;
        }
    }

    $error = 'ログイン情報が正しくありません。';
}
?>
<!doctype html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ログイン</title>
  <link rel="stylesheet" href="/login/styke.css?v=<?= time() ?>">
</head>
<body class="login-page">
  <form class="card login-card" method="post">
    <h1>ログイン</h1>
    <p class="muted">登録ユーザ情報を入力してください。</p>
    <?php if ($error !== ''): ?>
      <div class="notice error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>
    <label>
      ユーザー名
      <input type="text" name="username" required>
    </label>
    <label>
      パスワード
      <input type="password" name="password" required>
    </label>
    <button type="submit">ログイン</button>
  </form>
</body>
</html>
