<?php
require_once __DIR__ . '/_init.php';
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}
header('Location: dashboard.php');
exit;
