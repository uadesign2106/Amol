<?php
require_once __DIR__ . '/_init.php';
requireLogin();
$user = currentUser();
?>
<!doctype html>
<html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($title ?? 'Admin') ?> - Green Plus Admin</title>
<link rel="stylesheet" href="<?= site_url('assets/css/style.css') ?>">
</head><body>
<div class="admin-layout">
<aside>
<h2>Admin</h2>
<p style="color:#fff;font-size:.9rem;margin:.25rem 0 .75rem;">Hi, <?= e($user['name']) ?> (<?= e($user['role_name']) ?>)</p>
<?php if (hasPermission('dashboard.view')): ?><a href="<?= site_url('admin/') ?>">Dashboard</a><?php endif; ?>
<?php if (hasPermission('products.manage')): ?><a href="products.php">Products</a><?php endif; ?>
<?php if (hasPermission('categories.manage')): ?><a href="categories.php">Categories</a><?php endif; ?>
<?php if (hasPermission('industries.manage')): ?><a href="industries.php">Industries</a><?php endif; ?>
<?php if (hasPermission('enquiries.view')): ?><a href="enquiries.php">Enquiries</a><?php endif; ?>
<?php if (hasPermission('users.manage')): ?><a href="users.php">Users</a><?php endif; ?>
<a href="logout.php">Logout</a>
</aside><main class="admin-main">
