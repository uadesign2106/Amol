<?php
$title = 'Enquiries';
require_once __DIR__ . '/_header.php';
requirePermission('enquiries.view');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf()) {
    if (!empty($_POST['delete_id']) && hasPermission('enquiries.manage')) {
        db()->prepare('DELETE FROM enquiries WHERE id=:id')->execute([':id' => (int) $_POST['delete_id']]);
    }
    if (!empty($_POST['status_id']) && !empty($_POST['status']) && hasPermission('enquiries.respond')) {
        db()->prepare('UPDATE enquiries SET status=:status WHERE id=:id')->execute([':status' => $_POST['status'], ':id' => (int) $_POST['status_id']]);
    }
}

$filter = $_GET['status'] ?? '';
$params = [];
$where = '';
if (in_array($filter, ['Pending', 'Responded', 'Closed'], true)) {
    $where = ' WHERE e.status = :status';
    $params[':status'] = $filter;
}
$countStmt = db()->prepare('SELECT COUNT(*) FROM enquiries e' . $where);
$countStmt->execute($params);
$count = (int) $countStmt->fetchColumn();
[$page, $pages, $offset, $limit] = pagination($count, 10);
$sql = 'SELECT e.*, p.name AS product_name FROM enquiries e LEFT JOIN products p ON p.id=e.product_id' . $where . ' ORDER BY e.id DESC LIMIT :offset,:lim';
$stmt = db()->prepare($sql);
foreach ($params as $k => $v) { $stmt->bindValue($k, $v); }
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
$stmt->execute();
$rows = $stmt->fetchAll();
?>
<h1>Enquiry Management</h1>
<form class="inline-form" method="get"><select name="status"><option value="">All Status</option><option <?= $filter==='Pending'?'selected':'' ?>>Pending</option><option <?= $filter==='Responded'?'selected':'' ?>>Responded</option><option <?= $filter==='Closed'?'selected':'' ?>>Closed</option></select><button class="btn">Filter</button></form>
<table><tr><th>ID</th><th>Name</th><th>Contact</th><th>Product</th><th>Message</th><th>Status</th><th>Source</th><th>Action</th></tr>
<?php foreach($rows as $r):
$productName = $r['product_name'] ?: 'General';
$wa = enquiry_channels($productName, 'as discussed')['whatsapp'];
$mail = 'mailto:' . e($r['email']) . '?subject=' . rawurlencode('Re: Your enquiry with Green Plus');
?>
<tr><td><?= $r['id'] ?></td><td><?= e($r['name']) ?></td><td><?= e($r['email']) ?><br><?= e($r['mobile']) ?></td><td><?= e($productName) ?></td><td><?= e($r['message']) ?></td><td>
<?php if (hasPermission('enquiries.respond')): ?>
<form method="post" class="inline"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="status_id" value="<?= $r['id'] ?>"><select name="status" onchange="this.form.submit()"><option <?= $r['status']==='Pending'?'selected':'' ?>>Pending</option><option <?= $r['status']==='Responded'?'selected':'' ?>>Responded</option><option <?= $r['status']==='Closed'?'selected':'' ?>>Closed</option></select></form>
<?php else: ?><?= e($r['status']) ?><?php endif; ?>
</td><td><?= e($r['source']) ?></td><td><a target="_blank" href="<?= $wa ?>">WhatsApp</a> | <a href="<?= $mail ?>">Email</a>
<?php if (hasPermission('enquiries.manage')): ?><form method="post" class="inline"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="delete_id" value="<?= $r['id'] ?>"><button onclick="return confirm('Delete enquiry?')">Delete</button></form><?php endif; ?></td></tr>
<?php endforeach; ?></table>
<p>Page <?= $page ?> of <?= $pages ?></p>
<?php require_once __DIR__ . '/_footer.php'; ?>
