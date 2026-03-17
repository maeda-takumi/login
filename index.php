<?php

declare(strict_types=1);

require_once __DIR__ . '/login_check.php';

$page = $_GET['page'] ?? 'index';
if (!is_string($page) || trim($page) === '') {
    $page = 'index';
}

if (!preg_match('/^[a-zA-Z0-9_-]+$/', $page)) {
    header('Location: /?page=index');
    exit;
}

$includeDir = __DIR__ . '/include';
$requested = $includeDir . '/' . $page . '.html';
$realIncludeDir = realpath($includeDir);
$realRequested = realpath($requested);

if (
    $realIncludeDir === false
    || $realRequested === false
    || strncmp($realRequested, $realIncludeDir, strlen($realIncludeDir)) !== 0
    || !is_file($realRequested)
) {
    header('Location: /?page=index');
    exit;
}
?>
<!doctype html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Protected Include Router</title>
  <style>
    body { font-family: sans-serif; margin: 24px; }
    nav a { margin-right: 10px; }
  </style>
</head>
<body>
  <nav>
    <a href="/?page=index">Home</a>
    <a href="/?page=company">Company</a>
    <a href="/login/logout.php">Logout</a>
  </nav>
  <hr>
  <?php include $realRequested; ?>
</body>
</html>