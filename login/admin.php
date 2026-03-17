<?php

declare(strict_types=1);

require_once __DIR__ . '/users_store.php';

$message = '';
$error = '';
$users = load_users();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string)($_POST['action'] ?? '');

    if ($action === 'create_user') {
        $username = trim((string)($_POST['username'] ?? ''));
        $password = trim((string)($_POST['password'] ?? ''));
        $status = ((string)($_POST['status'] ?? 'inactive')) === 'active' ? 'active' : 'inactive';

        if ($username === '' || $password === '') {
            $error = 'ユーザー名とパスワードを入力してください。';
        } else {
            $users[] = [
                'id' => next_user_id($users),
                'username' => $username,
                'password' => $password,
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                'status' => $status,
            ];
            save_users($users);
            $message = 'ユーザを追加しました。';
        }
    }

    if ($action === 'update_user') {
        $id = (int)($_POST['id'] ?? 0);
        $username = trim((string)($_POST['username'] ?? ''));
        $password = trim((string)($_POST['password'] ?? ''));
        $status = ((string)($_POST['status'] ?? 'inactive')) === 'active' ? 'active' : 'inactive';

        foreach ($users as &$user) {
            if ((int)$user['id'] !== $id) {
                continue;
            }
            $user['username'] = $username;
            $user['status'] = $status;
            if ($password !== '') {
                $user['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
            }
        }
        unset($user);
        save_users($users);
        $message = 'ユーザ情報を更新しました。';
    }

    if ($action === 'delete_user') {
        $id = (int)($_POST['id'] ?? 0);
        $users = array_values(array_filter($users, static fn(array $user): bool => (int)$user['id'] !== $id));
        save_users($users);
        $message = 'ユーザを削除しました。';
    }

    $users = load_users();
}
?>
<!doctype html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ログインユーザ管理</title>
  <link rel="stylesheet" href="styke.css?v=<?= time() ?>">
</head>
<body>
  <header class="header">
    <div class="header-inner">
      <strong>ログイン管理画面</strong>
      <nav class="nav">
        <a href="/?page=index">Home</a>
        <a href="/login/index.php">Login</a>
      </nav>
    </div>
  </header>

  <main class="wrap grid admin-grid">
    <section class="card" style="padding:20px;">
      <h1>ログインユーザ管理</h1>
      <p class="muted">ログインユーザを管理できます。</p>
      <?php if ($message !== ''): ?><div class="notice success"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
      <?php if ($error !== ''): ?><div class="notice error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>

      <div class="table-wrap">
        <table>
          <thead>
          <tr><th>ID</th><th>ユーザー名</th><th>パスワード</th><th>状態</th><th>操作</th></tr>
          </thead>
          <tbody>
          <?php foreach ($users as $user): ?>
            <tr>
              <td><?= (int)$user['id'] ?></td>
              <td><?= htmlspecialchars((string)$user['username'], ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars((string)($user['password'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= ((string)$user['status'] === 'active') ? '有効' : '無効' ?></td>
              <td>
                <form method="post" class="grid" style="gap:8px; min-width: 260px; margin-bottom:8px;">
                  <input type="hidden" name="action" value="update_user">
                  <input type="hidden" name="id" value="<?= (int)$user['id'] ?>">
                  <input type="text" name="username" value="<?= htmlspecialchars((string)$user['username'], ENT_QUOTES, 'UTF-8') ?>" required>
                  <input type="text" name="password" value="<?= htmlspecialchars((string)($user['password'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="パスワード">
                  <select name="status">
                    <option value="active" <?= ((string)$user['status'] === 'active') ? 'selected' : '' ?>>有効</option>
                    <option value="inactive" <?= ((string)$user['status'] === 'inactive') ? 'selected' : '' ?>>無効</option>
                  </select>
                  <button type="submit">更新</button>
                </form>
                <form method="post" onsubmit="return confirm('削除しますか？');">
                  <input type="hidden" name="action" value="delete_user">
                  <input type="hidden" name="id" value="<?= (int)$user['id'] ?>">
                  <button class="btn-danger" type="submit">削除</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </section>

    <section class="card" style="padding:20px;">
      <h2>新規ユーザ追加</h2>
      <form method="post" class="grid" id="create-user-form">
        <input type="hidden" name="action" value="create_user">
        <label>ユーザー名<input type="text" name="username" required></label>
        <label>パスワード
          <div style="display:flex; gap:8px; align-items:center;">
            <input id="new-user-password" type="text" name="password" required>
            <button type="button" id="generate-password">自動生成</button>
          </div>
        </label>
        <label>状態
          <select name="status">
            <option value="active">有効</option>
            <option value="inactive">無効</option>
          </select>
        </label>
        <button type="submit">追加</button>
      </form>
    </section>
  </main>
  <script>
    (() => {
      const letters = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789!@#$%^&*';
      const button = document.getElementById('generate-password');
      const input = document.getElementById('new-user-password');

      if (!button || !input) {
        return;
      }

      button.addEventListener('click', () => {
        const length = 12;
        let password = '';
        for (let i = 0; i < length; i += 1) {
          password += letters[Math.floor(Math.random() * letters.length)];
        }
        input.value = password;
      });
    })();
  </script>
</body>
</html>
