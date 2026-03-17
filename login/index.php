<?php

declare(strict_types=1);

session_start();

$config = require __DIR__ . '/login_config.php';
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
    $username = (string)($_POST['username'] ?? '');
    $password = (string)($_POST['password'] ?? '');

    $validUser = hash_equals((string)$config['username'], $username);
    $validPass = password_verify($password, (string)$config['password_hash']);

    if ($validUser && $validPass) {
        $_SESSION['logged_in'] = true;
        header('Location: ' . $next);
        exit;
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
  <style>
    body { font-family: sans-serif; margin: 0; display: grid; place-items: center; min-height: 100vh; background: #f5f7fb; }
    .card { background: #fff; padding: 24px; border-radius: 10px; width: 320px; box-shadow: 0 8px 30px rgba(0,0,0,.08); }
    label { display: block; margin-bottom: 12px; }
    input { width: 100%; box-sizing: border-box; padding: 10px; margin-top: 4px; }
    button { width: 100%; padding: 10px; }
    .error { color: #b00020; margin-bottom: 10px; }
  </style>
</head>
<body>
  <form class="card" method="post">
    <h1>ログイン</h1>
    <?php if ($error !== ''): ?>
      <div class="error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
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
