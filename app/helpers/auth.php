<?php

require_once __DIR__ . '/common.php';

function isLoggedIn(): bool
{
    return !empty($_SESSION['user_id']);
}

function currentUser(): ?array
{
    if (!isLoggedIn()) {
        return null;
    }

    static $user = null;
    if ($user !== null) {
        return $user;
    }

    $stmt = db()->prepare('SELECT u.*, r.role_name FROM users u JOIN roles r ON r.id = u.role_id WHERE u.id = :id LIMIT 1');
    $stmt->execute([':id' => (int) $_SESSION['user_id']]);
    $user = $stmt->fetch() ?: null;
    return $user;
}

function hasPermission(string $permissionKey): bool
{
    if (!isLoggedIn()) {
        return false;
    }

    static $cache = [];
    $uid = (int) $_SESSION['user_id'];
    if (!isset($cache[$uid])) {
        $stmt = db()->prepare('SELECT p.permission_key
            FROM users u
            JOIN role_permissions rp ON rp.role_id = u.role_id
            JOIN permissions p ON p.id = rp.permission_id
            WHERE u.id = :id AND u.status = 1');
        $stmt->execute([':id' => $uid]);
        $cache[$uid] = array_column($stmt->fetchAll(), 'permission_key');
    }

    return in_array($permissionKey, $cache[$uid], true);
}

function requireLogin(): void
{
    if (!isLoggedIn()) {
        header('Location: ' . site_url('admin/login.php'));
        exit;
    }

    $user = currentUser();
    if (!$user || (int) $user['status'] !== 1) {
        session_destroy();
        header('Location: ' . site_url('admin/login.php'));
        exit;
    }
}

function requirePermission(string $permissionKey): void
{
    requireLogin();
    if (!hasPermission($permissionKey)) {
        http_response_code(403);
        echo '<h2>403 Forbidden</h2><p>You do not have permission to access this module.</p>';
        exit;
    }
}
