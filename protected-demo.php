<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/lib/auth.php';

include __DIR__ . '/header.php';
?>
<section class="card">
  <h1>保護URLサンプルページ</h1>
  <p>このページを管理画面で <code>/protected-demo.php</code> または <code>/*</code> に登録すると、未ログイン時は自動でログインページに転送されます。</p>
</section>
<?php include __DIR__ . '/footer.php'; ?>
