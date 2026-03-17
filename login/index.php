<?php

declare(strict_types=1);

session_start();
require_once __DIR__ . '/users_store.php';
$error = '';

$scriptName = (string)($_SERVER['SCRIPT_NAME'] ?? '/login/index.php');
$scriptDir = str_replace('\\', '/', dirname($scriptName));
$appBase = str_replace('\\', '/', dirname($scriptDir));
if ($appBase === '.' || $appBase === '/') {
    $appBase = '';
}
$defaultNext = ($appBase === '' ? '' : rtrim($appBase, '/')) . '/?page=index';
$next = $defaultNext;

if (isset($_GET['next']) && is_string($_GET['next']) && str_starts_with($_GET['next'], '/')) {
    $next = $_GET['next'];
}

$nextPath = (string)(parse_url($next, PHP_URL_PATH) ?? '');
if (str_ends_with($nextPath, '/login/index.php')) {
    $next = $defaultNext;
}
if (!empty($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header('Location: ' . $next);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim((string)($_POST['email'] ?? ''));
    $password = (string)($_POST['password'] ?? '');

    foreach (load_users() as $user) {
        $nameMatches = hash_equals((string)($user['email'] ?? ''), $email);
        $passMatches = password_verify($password, (string)$user['password_hash']);
        $isActive = ((string)($user['status'] ?? 'inactive')) === 'active';
        if ($nameMatches && $passMatches && $isActive) {
            $_SESSION['logged_in'] = true;
            $_SESSION['login_user'] = (string)($user['line_name'] ?? ($user['email'] ?? ''));
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
  <link rel="stylesheet" href="styke.css?v=<?= time() ?>">
</head>
<body class="login-page">
  <form class="card login-card" method="post">
    <h1>ログイン</h1>
    <p class="muted">登録ユーザ情報を入力してください。</p>
    <?php if ($error !== ''): ?>
      <div class="notice error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>
    <label>
      メールアドレス
      <input type="email" name="email" required>
    </label>
    <label>
      パスワード
      <input type="password" name="password" required>
    </label>
    <button type="submit">ログイン</button>
  </form>
</body>
</html>
