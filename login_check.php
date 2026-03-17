<?php

declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (empty($_SESSION['logged_in'])) {
    header('Location: /login/index.php?next=' . rawurlencode('/?page=index'));
    exit;
}
