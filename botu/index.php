<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/lib/auth.php';

include __DIR__ . '/header.php';
?>
<section class="card hero">
  <h1>有料コンテンツ向けログインパッケージ</h1>
  <p>指定したURLをログイン必須にし、管理画面からユーザと保護URLを管理できます。</p>
  <div class="actions">
    <a class="btn" href="<?= htmlspecialchars(app_path('/login.php'), ENT_QUOTES, 'UTF-8') ?>">ログインページ</a>
    <a class="btn btn-secondary" href="<?= htmlspecialchars(app_path('/admin.php'), ENT_QUOTES, 'UTF-8') ?>">管理画面</a>
    <a class="btn btn-secondary" href="<?= htmlspecialchars(app_path('/protected-demo.php'), ENT_QUOTES, 'UTF-8') ?>">保護URLサンプル</a>
  </div>
</section>

<section class="grid two">
  <article class="card">
    <h2>運用イメージ</h2>
    <ul>
      <li>管理画面でユーザを手動追加（パスワードは自動発行）</li>
      <li>保護対象URLを複数登録</li>
      <li>ログイン後の遷移先を1つ設定</li>
    </ul>
  </article>
  <article class="card">
    <h2>ログイン要件</h2>
    <ul>
      <li>メールアドレス + パスワードでログイン</li>
      <li>status が無効のユーザはログイン不可</li>
      <li>ログアウト機能あり / セッションは数日維持</li>
    </ul>
  </article>
</section>
<?php include __DIR__ . '/footer.php'; ?>
