<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/lib/auth.php';

include __DIR__ . '/header.php';
?>
<section class="card hero">
  <h1>汎用ログインパッケージ</h1>
  <p>このフォルダを配置するだけで、ログイン・管理画面・保護URL制御を追加できます。</p>
  <div class="actions">
    <?php if (is_logged_in()): ?>
      <a class="btn" href="/dashboard.php">ダッシュボードへ</a>
    <?php else: ?>
      <a class="btn" href="/login.php">ログインする</a>
    <?php endif; ?>
    <a class="btn btn-secondary" href="/protected-demo.php">保護URLサンプル</a>
  </div>
</section>

<section class="grid two">
  <article class="card">
    <h2>デフォルト管理者</h2>
    <ul>
      <li>mail: admin@example.com</li>
      <li>password: password123</li>
    </ul>
  </article>
  <article class="card">
    <h2>主な機能</h2>
    <ul>
      <li>SQLiteのユーザ管理（ID/mail/password/status/line_name）</li>
      <li>管理画面でユーザCRUDと遷移先設定</li>
      <li>保護URLパターンに対する認証ミドルウェア</li>
    </ul>
  </article>
</section>
<?php include __DIR__ . '/footer.php'; ?>
