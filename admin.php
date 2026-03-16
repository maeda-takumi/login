<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/lib/db.php';

require_admin();

$pdo = db();
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'save_redirect') {
            $path = trim((string)($_POST['default_redirect_path'] ?? '/dashboard.php'));
            $path = safe_next_path($path) ?? '/dashboard.php';
            set_setting('default_redirect_path', $path);
            $message = 'ログイン後遷移先を更新しました。';
        }

        if ($action === 'create_user') {
            $stmt = $pdo->prepare('INSERT INTO users (mail, password, status, line_name, role) VALUES (:mail, :password, :status, :line_name, :role)');
            $stmt->execute([
                ':mail' => strtolower(trim((string)$_POST['mail'])),
                ':password' => password_hash((string)$_POST['password'], PASSWORD_DEFAULT),
                ':status' => (string)$_POST['status'],
                ':line_name' => trim((string)$_POST['line_name']),
                ':role' => (string)$_POST['role'],
            ]);
            $message = 'ユーザを追加しました。';
        }

        if ($action === 'update_user') {
            $id = (int)$_POST['id'];
            $params = [
                ':id' => $id,
                ':mail' => strtolower(trim((string)$_POST['mail'])),
                ':status' => (string)$_POST['status'],
                ':line_name' => trim((string)$_POST['line_name']),
                ':role' => (string)$_POST['role'],
            ];

            $sql = 'UPDATE users SET mail = :mail, status = :status, line_name = :line_name, role = :role, updated_at = CURRENT_TIMESTAMP';

            $password = trim((string)($_POST['password'] ?? ''));
            if ($password !== '') {
                $sql .= ', password = :password';
                $params[':password'] = password_hash($password, PASSWORD_DEFAULT);
            }

            $sql .= ' WHERE id = :id';
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $message = 'ユーザを更新しました。';
        }

        if ($action === 'delete_user') {
            $id = (int)$_POST['id'];
            if ($id === (int)current_user()['id']) {
                throw new RuntimeException('自分自身は削除できません。');
            }
            $stmt = $pdo->prepare('DELETE FROM users WHERE id = :id');
            $stmt->execute([':id' => $id]);
            $message = 'ユーザを削除しました。';
        }

        if ($action === 'add_protected_route') {
            $pattern = trim((string)$_POST['pattern']);
            if ($pattern === '' || !str_starts_with($pattern, '/')) {
                throw new RuntimeException('保護URLは / から始めてください。');
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

$users = $pdo->query('SELECT id, mail, status, line_name, role, created_at FROM users ORDER BY id ASC')->fetchAll();
$routes = $pdo->query('SELECT id, pattern, enabled, created_at FROM protected_routes ORDER BY id DESC')->fetchAll();
$defaultRedirect = get_setting('default_redirect_path', '/dashboard.php');

include __DIR__ . '/header.php';
?>
<section class="grid two">
  <article class="card">
    <h1>管理画面</h1>
    <p class="muted">ログイン後遷移先の設定とユーザ/保護URL管理を行えます。</p>
    <?php if ($message): ?><div class="notice success"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
    <?php if ($error): ?><div class="notice error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>

    <h2>ログイン後の遷移先</h2>
    <form method="post" class="inline-form">
      <input type="hidden" name="action" value="save_redirect">
      <input type="text" name="default_redirect_path" value="<?= htmlspecialchars($defaultRedirect, ENT_QUOTES, 'UTF-8') ?>" placeholder="/dashboard.php">
      <button class="btn" type="submit">保存</button>
    </form>

    <h2>保護URL</h2>
    <form method="post" class="inline-form">
      <input type="hidden" name="action" value="add_protected_route">
      <input type="text" name="pattern" required placeholder="/protected-demo.php or /admin/*">
      <button class="btn" type="submit">追加</button>
    </form>

    <div class="list">
      <?php foreach ($routes as $route): ?>
        <div class="list-item">
          <div>
            <strong><?= htmlspecialchars((string)$route['pattern'], ENT_QUOTES, 'UTF-8') ?></strong>
            <span class="badge <?= ((int)$route['enabled'] === 1) ? 'on' : 'off' ?>"><?= ((int)$route['enabled'] === 1) ? 'ON' : 'OFF' ?></span>
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
      <input type="email" name="mail" placeholder="mail" required>
      <input type="password" name="password" placeholder="password" required>
      <input type="text" name="line_name" placeholder="line_name">
      <select name="status">
        <option value="active">active</option>
        <option value="inactive">inactive</option>
      </select>
      <select name="role">
        <option value="user">user</option>
        <option value="admin">admin</option>
      </select>
      <button class="btn" type="submit">追加</button>
    </form>

    <h2>ユーザ一覧</h2>
    <div class="table-wrap">
      <table>
        <thead>
          <tr><th>ID</th><th>mail</th><th>line_name</th><th>status</th><th>role</th><th>操作</th></tr>
        </thead>
        <tbody>
          <?php foreach ($users as $u): ?>
            <tr>
              <td><?= (int)$u['id'] ?></td>
              <td><?= htmlspecialchars((string)$u['mail'], ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars((string)$u['line_name'], ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars((string)$u['status'], ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars((string)$u['role'], ENT_QUOTES, 'UTF-8') ?></td>
              <td>
                <details>
                  <summary>編集</summary>
                  <form method="post" class="stack-form mini">
                    <input type="hidden" name="action" value="update_user">
                    <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                    <input type="email" name="mail" value="<?= htmlspecialchars((string)$u['mail'], ENT_QUOTES, 'UTF-8') ?>" required>
                    <input type="password" name="password" placeholder="新しいpassword(任意)">
                    <input type="text" name="line_name" value="<?= htmlspecialchars((string)$u['line_name'], ENT_QUOTES, 'UTF-8') ?>">
                    <select name="status">
                      <option value="active" <?= $u['status'] === 'active' ? 'selected' : '' ?>>active</option>
                      <option value="inactive" <?= $u['status'] === 'inactive' ? 'selected' : '' ?>>inactive</option>
                    </select>
                    <select name="role">
                      <option value="user" <?= $u['role'] === 'user' ? 'selected' : '' ?>>user</option>
                      <option value="admin" <?= $u['role'] === 'admin' ? 'selected' : '' ?>>admin</option>
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
