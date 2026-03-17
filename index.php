<?php

declare(strict_types=1);

require_once __DIR__ . 'login_check.php';

$page = $_GET['page'] ?? 'index';
if (!is_string($page) || trim($page) === '') {
    $page = 'index';
}

if (!preg_match('/^[a-zA-Z0-9_-]+$/', $page)) {
    header('Location: ?page=index');
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
    header('Location: ?page=index');
    exit;
}
?>

  <?php include $realRequested; ?>