<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/lib/db.php';
$pdo = db();
$message = '';
$error = '';
$issuedPassword = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'save_redirect') {
            $path = trim((string)($_POST['default_redirect_path'] ?? app_path('/')));
            $path = safe_next_path($path) ?? app_path('/');
            set_setting('default_redirect_path', $path);
            $message = 'ログイン後遷移先を更新しました。';
        }

        if ($action === 'create_user') {
            $issuedPassword = generate_random_password(10);
            $stmt = $pdo->prepare('INSERT INTO users (mail, password, plain_password, status, line_name, role) VALUES (:mail, :password, :plain_password, :status, :line_name, :role)');
            $stmt->execute([
                ':mail' => strtolower(trim((string)$_POST['mail'])),
                ':password' => password_hash($issuedPassword, PASSWORD_DEFAULT),
                ':plain_password' => $issuedPassword,
                ':status' => (string)$_POST['status'],
                ':line_name' => trim((string)$_POST['line_name']),
                ':role' => 'user',
            ]);
            $message = 'ユーザを追加しました。発行されたパスワードを控えてください。';
        }

        if ($action === 'update_user') {
            $id = (int)$_POST['id'];
            $stmt = $pdo->prepare('UPDATE users SET mail = :mail, status = :status, line_name = :line_name, updated_at = CURRENT_TIMESTAMP WHERE id = :id');
            $stmt->execute([
                ':id' => $id,
                ':mail' => strtolower(trim((string)$_POST['mail'])),
                ':status' => (string)$_POST['status'],
                ':line_name' => trim((string)$_POST['line_name']),
            ]);

            $message = 'ユーザを更新しました。';
        }

        if ($action === 'delete_user') {
            $id = (int)$_POST['id'];
            $stmt = $pdo->prepare('DELETE FROM users WHERE id = :id');
            $stmt->execute([':id' => $id]);
            $message = 'ユーザを削除しました。';
        }

        if ($action === 'add_protected_route') {
            $patternInput = trim((string)$_POST['pattern']);
            $parsedPath = parse_url($patternInput, PHP_URL_PATH);
            $pattern = is_string($parsedPath) ? $parsedPath : $patternInput;
            if ($pattern === '' || !str_starts_with($pattern, '/')) {
                throw new RuntimeException('保護URLは / から始まるパス、またはフルURLで入力してください。');
            }
            $stmt = $pdo->prepare('INSERT INTO protected_routes (pattern, enabled) VALUES (:pattern, 1)');
            $stmt->execute([':pattern' => $pattern]);
            $message = '保護URLを追加しました。';
        }

        if ($action === 'toggle_protected_route') {
            $id = (int)$_POST['id'];
            $enabled = ((int)$_POST['enabled']) === 1 ? 1 : 0;
            $stmt = $pdo->prepare('UPDATE protected_routes SET enabled = :enabled WHERE id = :id');
            $stmt->execute([':enabled' => $enabled, ':id' => $id]);
            $message = '保護URLの状態を更新しました。';
        }

        if ($action === 'delete_protected_route') {
            $id = (int)$_POST['id'];
            $stmt = $pdo->prepare('DELETE FROM protected_routes WHERE id = :id');
            $stmt->execute([':id' => $id]);
            $message = '保護URLを削除しました。';
        }
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

$users = $pdo->query('SELECT id, mail, plain_password, status, line_name, created_at FROM users WHERE role = "user" ORDER BY id ASC')->fetchAll();
$routes = $pdo->query('SELECT id, pattern, enabled, created_at FROM protected_routes ORDER BY id DESC')->fetchAll();
$defaultRedirect = get_setting('default_redirect_path', app_path('/'));

include __DIR__ . '/header.php';
?>
<section class="grid two">
  <article class="card">
    <h1>管理画面</h1>
    <p class="muted">ログイン保護の設定とユーザ管理を行います。</p>
    <?php if ($message): ?><div class="notice success"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
    <?php if ($issuedPassword !== ''): ?><div class="notice issued">発行パスワード: <strong><?= htmlspecialchars($issuedPassword, ENT_QUOTES, 'UTF-8') ?></strong></div><?php endif; ?>
    <?php if ($error): ?><div class="notice error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>

    <h2>ログイン後の共通遷移先</h2>
    <form method="post" class="inline-form">
      <input type="hidden" name="action" value="save_redirect">
      <input type="text" name="default_redirect_path" value="<?= htmlspecialchars($defaultRedirect, ENT_QUOTES, 'UTF-8') ?>" placeholder="<?= htmlspecialchars(app_path('/'), ENT_QUOTES, 'UTF-8') ?>">
      <button class="btn" type="submit">保存</button>
    </form>

    <h2>保護するURL</h2>
    <form method="post" class="inline-form">
      <input type="hidden" name="action" value="add_protected_route">
      <input type="text" name="pattern" required placeholder="/index.php or https://example.com/paid/*">
      <button class="btn" type="submit">追加</button>
    </form>

    <div class="list">
      <?php foreach ($routes as $route): ?>
        <div class="list-item">
          <div>
            <strong><?= htmlspecialchars((string)$route['pattern'], ENT_QUOTES, 'UTF-8') ?></strong>
            <span class="badge <?= ((int)$route['enabled'] === 1) ? 'on' : 'off' ?>"><?= ((int)$route['enabled'] === 1) ? '有効' : '無効' ?></span>
          </div>
          <div class="row-actions">
            <form method="post">
              <input type="hidden" name="action" value="toggle_protected_route">
              <input type="hidden" name="id" value="<?= (int)$route['id'] ?>">
              <input type="hidden" name="enabled" value="<?= ((int)$route['enabled'] === 1) ? 0 : 1 ?>">
              <button class="btn btn-secondary" type="submit"><?= ((int)$route['enabled'] === 1) ? '無効化' : '有効化' ?></button>
            </form>
            <form method="post" onsubmit="return confirm('削除しますか？');">
              <input type="hidden" name="action" value="delete_protected_route">
              <input type="hidden" name="id" value="<?= (int)$route['id'] ?>">
              <button class="btn btn-danger" type="submit">削除</button>
            </form>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </article>

  <article class="card">
    <h2>ユーザ追加</h2>
    <form method="post" class="stack-form">
      <input type="hidden" name="action" value="create_user">
      <input type="email" name="mail" placeholder="メールアドレス" required>
      <input type="text" name="line_name" placeholder="LINE名">
      <select name="status">
        <option value="active">有効</option>
        <option value="inactive">無効</option>
      </select>
      <button class="btn" type="submit">追加（パスワード自動発行）</button>
    </form>

    <h2>ユーザ一覧</h2>
    <div class="table-wrap">
      <table>
        <thead>
          <tr><th>ID</th><th>メール</th><th>パスワード</th><th>LINE名</th><th>状態</th><th>操作</th></tr>
        </thead>
        <tbody>
          <?php foreach ($users as $u): ?>
            <tr>
              <td><?= (int)$u['id'] ?></td>
              <td><?= htmlspecialchars((string)$u['mail'], ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars((string)($u['plain_password'] ?? '未設定'), ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars((string)$u['line_name'], ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= ((string)$u['status'] === 'active') ? '有効' : '無効' ?></td>
              <td>
                <details>
                  <summary>編集</summary>
                  <form method="post" class="stack-form mini">
                    <input type="hidden" name="action" value="update_user">
                    <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                    <input type="email" name="mail" value="<?= htmlspecialchars((string)$u['mail'], ENT_QUOTES, 'UTF-8') ?>" required>
                    <input type="text" name="line_name" value="<?= htmlspecialchars((string)$u['line_name'], ENT_QUOTES, 'UTF-8') ?>">
                    <select name="status">
                      <option value="active" <?= $u['status'] === 'active' ? 'selected' : '' ?>>有効</option>
                      <option value="inactive" <?= $u['status'] === 'inactive' ? 'selected' : '' ?>>無効</option>
                    </select>
                    <button class="btn" type="submit">更新</button>
                  </form>
                  <form method="post" onsubmit="return confirm('ユーザを削除しますか？');">
                    <input type="hidden" name="action" value="delete_user">
                    <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                    <button class="btn btn-danger" type="submit">削除</button>
                  </form>
                </details>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </article>
</section>
<?php include __DIR__ . '/footer.php'; ?>
