<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/lib/auth.php';

$user = current_user();

include __DIR__ . '/header.php';
?>
<section class="card">
  <h1>Dashboard</h1>
  <?php if ($user): ?>
    <p>ようこそ、<?= htmlspecialchars((string)$user['line_name'], ENT_QUOTES, 'UTF-8') ?> さん。</p>
    <p class="muted">mail: <?= htmlspecialchars((string)$user['mail'], ENT_QUOTES, 'UTF-8') ?></p>
  <?php else: ?>
    <p>このページは公開ページです。保護ページのログイン動作確認は <code>/protected-demo.php</code> をご利用ください。</p>
  <?php endif; ?>
</section>
<?php include __DIR__ . '/footer.php'; ?>
