<?php

declare(strict_types=1);

require_once __DIR__ . '/login/config.php';
require_once __DIR__ . '/login/lib/auth.php';

include __DIR__ . '/login/header.php';
?>
<section class="card hero">
  <h1>保護先サンプル (sample/index.php)</h1>
  <p>このページはPHP経由なので、保護URLに <code>/sample/index.php</code> を登録すれば認証ミドルウェアが有効になります。</p>
  <div class="actions">
    <a class="btn" href="<?= htmlspecialchars(app_path('/dashboard.php'), ENT_QUOTES, 'UTF-8') ?>">Dashboardへ戻る</a>
    <a class="btn btn-secondary" href="<?= htmlspecialchars(app_path('/logout.php'), ENT_QUOTES, 'UTF-8') ?>">ログアウト</a>
  </div>
</section>

<section class="card">
  <h2>設定メモ</h2>
  <ul>
    <li>管理画面の「保護URL」に <code>/sample/index.php</code> を追加してください。</li>
    <li>未ログイン時にこのページへアクセスすると、<code>login.php?next=...</code> へリダイレクトされます。</li>
    <li>ログイン成功後は元のURL (<code>/sample/index.php</code>) に戻ります。</li>
  </ul>
</section>
<?php include __DIR__ . '/../footer.php'; ?>
