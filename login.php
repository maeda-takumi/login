<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/lib/db.php';

$error = '';
$next = safe_next_path($_GET['next'] ?? null);

if (is_logged_in()) {
    header('Location: ' . ($next ?? get_setting('default_redirect_path', app_path('/dashboard.php'))));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mail = (string)($_POST['mail'] ?? '');
    $password = (string)($_POST['password'] ?? '');
    $next = safe_next_path($_POST['next'] ?? null);

    if (login($mail, $password)) {
        $destination = $next ?? get_setting('default_redirect_path', app_path('/dashboard.php'));
        header('Location: ' . $destination);
        exit;
    }

    $error = 'メールアドレスまたはパスワードが違います。';
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

    <label>Mail
      <input type="email" name="mail" required placeholder="you@example.com">
    </label>

    <label>Password
      <input type="password" name="password" required placeholder="********">
    </label>

    <input type="hidden" name="next" value="<?= htmlspecialchars($next ?? '', ENT_QUOTES, 'UTF-8') ?>">
    <button class="btn" type="submit">ログイン</button>
  </form>
</section>
<?php include __DIR__ . '/footer.php'; ?>
