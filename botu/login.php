<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/lib/db.php';

$error = '';
$next = safe_next_path($_GET['next'] ?? null);

if (is_logged_in()) {
    header('Location: ' . ($next ?? get_setting('default_redirect_path', app_path('/'))));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mail = (string)($_POST['mail'] ?? '');
    $password = (string)($_POST['password'] ?? '');
    $next = safe_next_path($_POST['next'] ?? null);

    $result = authenticate_user($mail, $password);

    if ($result === 'ok') {
        $destination = $next ?? get_setting('default_redirect_path', app_path('/'));
        header('Location: ' . $destination);
        exit;
    }

    if ($result === 'inactive_user') {
        $error = 'ログイン権限がありません。';
    } else {
        $error = 'メールアドレスまたはパスワードが違います。';
    }
}

include __DIR__ . '/header.php';
?>
<section class="auth-wrap">
  <form class="card auth-card" method="post">
    <h1>ログイン</h1>
    <p class="muted">登録済みのメールとパスワードでログインしてください。</p>

    <?php if ($error !== ''): ?>
      <div class="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <label>メールアドレス
      <input type="email" name="mail" required placeholder="you@example.com">
    </label>

    <label>パスワード
      <input type="password" name="password" required placeholder="********">
    </label>

    <input type="hidden" name="next" value="<?= htmlspecialchars($next ?? '', ENT_QUOTES, 'UTF-8') ?>">
    <button class="btn" type="submit">ログイン</button>
  </form>
</section>
<?php include __DIR__ . '/footer.php'; ?>
