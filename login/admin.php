<?php

declare(strict_types=1);

require_once __DIR__ . '/users_store.php';

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

$message = '';
$error = '';
if (isset($_GET['message']) && is_string($_GET['message'])) {
    $message = $_GET['message'];
}
if (isset($_GET['error']) && is_string($_GET['error'])) {
    $error = $_GET['error'];
}

$users = load_users();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string)($_POST['action'] ?? '');

    if ($action === 'create_user') {
        $lineName = trim((string)($_POST['line_name'] ?? ''));
        $email = strtolower(trim((string)($_POST['email'] ?? '')));
        $password = trim((string)($_POST['password'] ?? ''));
        $status = normalize_status($_POST['status'] ?? 'inactive');

        if ($lineName === '' || $email === '' || $password === '') {
            $error = 'LINE名・メールアドレス・パスワードは必須です。';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'メールアドレス形式が正しくありません。';
        } else {
            $exists = false;
            foreach ($users as $user) {
                if (hash_equals((string)($user['email'] ?? ''), $email)) {
                    $exists = true;
                    break;
                }
            }

            if ($exists) {
                $error = '同じメールアドレスのユーザーが既に存在します。';
            } else {
                $users[] = [
                    'id' => next_user_id($users),
                    'line_name' => $lineName,
                    'email' => $email,
                    'password' => $password,
                    'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                    'status' => $status,
                ];
                save_users($users);
                header('Location: admin.php?message=' . urlencode('ユーザを追加しました。'));
                exit;
            }
        }
    }

    if ($action === 'update_user') {
        $id = (int)($_POST['id'] ?? 0);
        $lineName = trim((string)($_POST['line_name'] ?? ''));
        $email = strtolower(trim((string)($_POST['email'] ?? '')));
        $password = trim((string)($_POST['password'] ?? ''));
        $status = normalize_status($_POST['status'] ?? 'inactive');

        if ($lineName === '' || $email === '') {
            $error = 'LINE名・メールアドレスは必須です。';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'メールアドレス形式が正しくありません。';
        } else {
            $duplicate = false;
            foreach ($users as $user) {
                if ((int)($user['id'] ?? 0) === $id) {
                    continue;
                }
                if (hash_equals((string)($user['email'] ?? ''), $email)) {
                    $duplicate = true;
                    break;
                }
            }
            if ($duplicate) {
                $error = '同じメールアドレスのユーザーが既に存在します。';
            } else {
                foreach ($users as &$user) {
                    if ((int)$user['id'] !== $id) {
                        continue;
                    }
                    $user['line_name'] = $lineName;
                    $user['email'] = $email;
                    $user['status'] = $status;
                    if ($password !== '') {
                        $user['password'] = $password;
                        $user['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
                    }
                }
                unset($user);

                save_users($users);
                header('Location: admin.php?message=' . urlencode('ユーザ情報を更新しました。'));
                exit;
            }
        }
    }

    if ($action === 'delete_user') {
        $id = (int)($_POST['id'] ?? 0);
        $users = array_values(array_filter($users, static fn(array $user): bool => (int)$user['id'] !== $id));
        save_users($users);
        header('Location: admin.php?message=' . urlencode('ユーザを削除しました。'));
        exit;
    }

}

$keywordLineName = trim((string)($_GET['line_name'] ?? ''));
$keywordEmail = trim((string)($_GET['email'] ?? ''));

$filteredUsers = array_values(array_filter($users, static function (array $user) use ($keywordLineName, $keywordEmail): bool {
    $lineName = (string)($user['line_name'] ?? '');
    $email = (string)($user['email'] ?? '');

    $lineMatches = $keywordLineName === '' || mb_stripos($lineName, $keywordLineName) !== false;
    $emailMatches = $keywordEmail === '' || mb_stripos($email, $keywordEmail) !== false;

    return $lineMatches && $emailMatches;
}));

$perPage = 25;
$totalUsers = count($filteredUsers);
$totalPages = max(1, (int)ceil($totalUsers / $perPage));
$currentPage = (int)($_GET['page'] ?? 1);
$currentPage = max(1, min($currentPage, $totalPages));
$offset = ($currentPage - 1) * $perPage;
$usersOnPage = array_slice($filteredUsers, $offset, $perPage);

function page_link(int $page, string $lineName, string $email): string
{
    $params = [
        'page' => $page,
    ];
    if ($lineName !== '') {
        $params['line_name'] = $lineName;
    }
    if ($email !== '') {
        $params['email'] = $email;
    }

    return 'admin.php?' . http_build_query($params);
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
        <a href="index.php">Login</a>
      </nav>
    </div>
  </header>

  <main class="wrap admin-wrap">
    <section class="card admin-card">
      <div class="title-row">
        <div>
          <h1>ユーザ管理</h1>
          <p class="muted">登録情報（LINE名 / メールアドレス / password / status）を管理できます。</p>
        </div>
        <button type="button" class="btn-inline" data-modal-target="create-user-modal">+ ユーザ追加</button>
      </div>

      <?php if ($message !== ''): ?><div class="notice success"><?= h($message) ?></div><?php endif; ?>
      <?php if ($error !== ''): ?><div class="notice error"><?= h($error) ?></div><?php endif; ?>

      <form method="get" class="search-row card-sub">
        <label>LINE名検索
          <input type="text" name="line_name" value="<?= h($keywordLineName) ?>" placeholder="例: yamada">
        </label>
        <label>メールアドレス検索
          <input type="text" name="email" value="<?= h($keywordEmail) ?>" placeholder="example@domain.com">
        </label>
        <button type="submit" class="btn-inline center-row">検索</button>
      </form>

      <p class="result-meta"><?= $totalUsers ?>件中 <?= $offset + 1 ?>〜<?= min($offset + $perPage, $totalUsers === 0 ? 0 : $totalUsers) ?>件を表示</p>

      <ul class="user-list">
        <?php foreach ($usersOnPage as $user): ?>
          <li class="user-item">
            <div class="user-main">
              <div class="user-primary"><?= h((string)$user['line_name']) ?></div>
              <div class="user-secondary"><?= h((string)$user['email']) ?></div>
              <div class="user-password">password: <?= h((string)($user['password'] ?? '')) ?></div>
            </div>
            <div class="user-right">
              <span class="status-pill <?= ((string)$user['status'] === 'active') ? 'active' : 'inactive' ?>">
                <?= ((string)$user['status'] === 'active') ? '有効' : '無効' ?>
              </span>
              <div class="actions">
                <button
                  type="button"
                  class="btn-inline"
                  data-modal-target="edit-user-modal"
                  data-id="<?= (int)$user['id'] ?>"
                  data-line-name="<?= h((string)$user['line_name']) ?>"
                  data-email="<?= h((string)$user['email']) ?>"
                  data-password="<?= h((string)($user['password'] ?? '')) ?>"
                  data-status="<?= h((string)$user['status']) ?>"
                >編集</button>
                <form method="post" onsubmit="return confirm('削除しますか？');">
                  <input type="hidden" name="action" value="delete_user">
                  <input type="hidden" name="id" value="<?= (int)$user['id'] ?>">
                  <button type="submit" class="btn-danger">削除</button>
                </form>
              </div>
            </div>
          </li>
        <?php endforeach; ?>
      </ul>

      <?php if ($usersOnPage === []): ?>
        <p class="muted">表示できるユーザがありません。</p>
      <?php endif; ?>

      <div class="pager">
        <a class="pager-link <?= $currentPage <= 1 ? 'disabled' : '' ?>" href="<?= $currentPage <= 1 ? '#' : h(page_link($currentPage - 1, $keywordLineName, $keywordEmail)) ?>">前へ</a>
        <span class="pager-current"><?= $currentPage ?> / <?= $totalPages ?></span>
        <a class="pager-link <?= $currentPage >= $totalPages ? 'disabled' : '' ?>" href="<?= $currentPage >= $totalPages ? '#' : h(page_link($currentPage + 1, $keywordLineName, $keywordEmail)) ?>">次へ</a>
      </div>
    </section>
  </main>

  <dialog id="create-user-modal" class="modal">
    <form method="dialog" class="modal-header">
      <h2>新規ユーザ追加</h2>
      <button type="submit" class="text-btn">✕</button>
    </form>
    <form method="post" class="grid">
      <input type="hidden" name="action" value="create_user">
      <label>LINE名<input type="text" name="line_name" required></label>
      <label>メールアドレス（ログイン情報）<input type="email" name="email" required></label>
        <label>password（ログイン情報）
      <div class="flex_frame">
        <input id="new-user-password" type="text" name="password" required></label>
        <button type="button" id="generate-password" class="btn-sub">パスワード自動生成</button>
    </div>
      <label>status
        <select name="status">
          <option value="active">有効</option>
          <option value="inactive">無効</option>
        </select>
      </label>
      <div>
        <button type="submit">登録</button>
      </div>
    </form>
  </dialog>

  <dialog id="edit-user-modal" class="modal">
    <form method="dialog" class="modal-header">
      <h2>ユーザ編集</h2>
      <button type="submit" class="text-btn">✕</button>
    </form>
    <form method="post" class="grid" id="edit-user-form">
      <input type="hidden" name="action" value="update_user">
      <input type="hidden" name="id" id="edit-id">
      <label>LINE名<input type="text" name="line_name" id="edit-line-name" required></label>
      <label>メールアドレス（ログイン情報）<input type="email" name="email" id="edit-email" required></label>
      <label>password（ログイン情報）<input type="text" name="password" id="edit-password"></label>
      <label>status
        <select name="status" id="edit-status">
          <option value="active">有効</option>
          <option value="inactive">無効</option>
        </select>
      </label>
      <button type="submit">更新</button>
    </form>
  </dialog>
  <script>
    (() => {
      const letters = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789!@#$%^&*';
      const button = document.getElementById('generate-password');
      const input = document.getElementById('new-user-password');

      if (button && input) {
        button.addEventListener('click', () => {
          const length = 12;
          let password = '';
          for (let i = 0; i < length; i += 1) {
            password += letters[Math.floor(Math.random() * letters.length)];
          }
          input.value = password;
        });
      }

      const openButtons = document.querySelectorAll('[data-modal-target]');
      openButtons.forEach((openButton) => {
        openButton.addEventListener('click', () => {
          const modalId = openButton.getAttribute('data-modal-target');
          if (!modalId) {
            return;
          }

          const modal = document.getElementById(modalId);
          if (!(modal instanceof HTMLDialogElement)) {
            return;
          }

          if (modalId === 'edit-user-modal') {
            const idInput = document.getElementById('edit-id');
            const lineNameInput = document.getElementById('edit-line-name');
            const emailInput = document.getElementById('edit-email');
            const passwordInput = document.getElementById('edit-password');
            const statusInput = document.getElementById('edit-status');

            if (!idInput || !lineNameInput || !emailInput || !passwordInput || !statusInput) {
              return;
            }

            idInput.value = openButton.dataset.id ?? '';
            lineNameInput.value = openButton.dataset.lineName ?? '';
            emailInput.value = openButton.dataset.email ?? '';
            passwordInput.value = openButton.dataset.password ?? '';
            statusInput.value = openButton.dataset.status ?? 'inactive';
          }

          modal.showModal();
        });
      });
    })();
  </script>
</body>
</html>
