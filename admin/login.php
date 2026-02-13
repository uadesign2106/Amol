<?php
require_once __DIR__ . '/_init.php';
if (isLoggedIn()) { header('Location: dashboard.php'); exit; }
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $stmt = db()->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch();

    if ($user && (int) $user['status'] === 1 && password_verify($_POST['password'] ?? '', $user['password'])) {
        $_SESSION['user_id'] = (int) $user['id'];
        header('Location: dashboard.php');
        exit;
    }
    $error = 'Invalid credentials or inactive user';
}
?>
<!doctype html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Admin Login</title><link rel="stylesheet" href="<?= site_url('assets/css/style.css') ?>"></head>
<body class="login-body"><form method="post" class="login-card"><h1>Admin Login</h1><?php if($error): ?><p class="notice error"><?= e($error) ?></p><?php endif; ?><input type="email" name="email" placeholder="Email" required><input type="password" name="password" placeholder="Password" required><button class="btn">Login</button></form></body></html>
