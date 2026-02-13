<?php
$title = 'Dashboard';
require_once __DIR__ . '/_header.php';
requirePermission('dashboard.view');
$totalProducts = (int) db()->query('SELECT COUNT(*) FROM products')->fetchColumn();
$totalEnquiries = (int) db()->query('SELECT COUNT(*) FROM enquiries')->fetchColumn();
$pending = (int) db()->query("SELECT COUNT(*) FROM enquiries WHERE status='Pending'")->fetchColumn();
$responded = (int) db()->query("SELECT COUNT(*) FROM enquiries WHERE status='Responded'")->fetchColumn();
?>
<h1>Dashboard</h1>
<div class="stats">
<div class="stat"><h3>Total Products</h3><p><?= $totalProducts ?></p></div>
<div class="stat"><h3>Total Enquiries</h3><p><?= $totalEnquiries ?></p></div>
<div class="stat"><h3>Pending</h3><p><?= $pending ?></p></div>
<div class="stat"><h3>Responded</h3><p><?= $responded ?></p></div>
</div>
<?php require_once __DIR__ . '/_footer.php'; ?>
