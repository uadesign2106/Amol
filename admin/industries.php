<?php
$title = 'Industries';
require_once __DIR__ . '/_header.php';
requirePermission('industries.manage');
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf()) {
    if (!empty($_POST['delete_id'])) {
        db()->prepare('DELETE FROM industries WHERE id = :id')->execute([':id' => (int) $_POST['delete_id']]);
    } elseif (!empty($_POST['id'])) {
        db()->prepare('UPDATE industries SET industry_name=:name, status=:status WHERE id=:id')->execute([
            ':name' => trim($_POST['industry_name']), ':status' => (int) $_POST['status'], ':id' => (int) $_POST['id'],
        ]);
    } else {
        db()->prepare('INSERT INTO industries(industry_name,status) VALUES(:name,:status)')->execute([
            ':name' => trim($_POST['industry_name']), ':status' => (int) $_POST['status'],
        ]);
    }
}
$rows = db()->query('SELECT * FROM industries ORDER BY id DESC')->fetchAll();
$edit = !empty($_GET['edit']) ? db()->query('SELECT * FROM industries WHERE id=' . (int) $_GET['edit'])->fetch() : null;
?>
<h1>Industry Management</h1>
<form method="post" class="inline-form"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="id" value="<?= (int)($edit['id'] ?? 0) ?>"><input name="industry_name" value="<?= e($edit['industry_name'] ?? '') ?>" placeholder="Industry Name" required><select name="status"><option value="1">Active</option><option value="0" <?= isset($edit['status']) && !$edit['status']?'selected':'' ?>>Inactive</option></select><button class="btn"><?= $edit ? 'Update':'Add' ?></button></form>
<table><tr><th>ID</th><th>Name</th><th>Status</th><th>Action</th></tr><?php foreach($rows as $row): ?><tr><td><?= $row['id'] ?></td><td><?= e($row['industry_name']) ?></td><td><?= $row['status']?'Active':'Inactive' ?></td><td><a href="?edit=<?= $row['id'] ?>">Edit</a><form method="post" class="inline"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="delete_id" value="<?= $row['id'] ?>"><button onclick="return confirm('Delete?')">Delete</button></form></td></tr><?php endforeach; ?></table>
<?php require_once __DIR__ . '/_footer.php'; ?>
