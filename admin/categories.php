<?php
$title = 'Categories';
require_once __DIR__ . '/_header.php';
requirePermission('categories.manage');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf()) {
    if (!empty($_POST['delete_id'])) {
        db()->prepare('DELETE FROM categories WHERE id = :id')->execute([':id' => (int) $_POST['delete_id']]);
    } elseif (!empty($_POST['id'])) {
        db()->prepare('UPDATE categories SET category_name=:name, status=:status WHERE id=:id')->execute([
            ':name' => trim($_POST['category_name']), ':status' => (int) $_POST['status'], ':id' => (int) $_POST['id'],
        ]);
    } else {
        db()->prepare('INSERT INTO categories(category_name,status) VALUES(:name,:status)')->execute([
            ':name' => trim($_POST['category_name']), ':status' => (int) $_POST['status'],
        ]);
    }
}
$categories = db()->query('SELECT * FROM categories ORDER BY id DESC')->fetchAll();
$edit = !empty($_GET['edit']) ? db()->query('SELECT * FROM categories WHERE id=' . (int) $_GET['edit'])->fetch() : null;
?>
<h1>Category Management</h1>
<form method="post" class="inline-form"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="id" value="<?= (int)($edit['id'] ?? 0) ?>"><input name="category_name" value="<?= e($edit['category_name'] ?? '') ?>" placeholder="Category Name" required><select name="status"><option value="1">Active</option><option value="0" <?= isset($edit['status']) && !$edit['status']?'selected':'' ?>>Inactive</option></select><button class="btn"><?= $edit ? 'Update':'Add' ?></button></form>
<table><tr><th>ID</th><th>Name</th><th>Status</th><th>Action</th></tr><?php foreach($categories as $row): ?><tr><td><?= $row['id'] ?></td><td><?= e($row['category_name']) ?></td><td><?= $row['status']?'Active':'Inactive' ?></td><td><a href="?edit=<?= $row['id'] ?>">Edit</a><form method="post" class="inline"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="delete_id" value="<?= $row['id'] ?>"><button onclick="return confirm('Delete?')">Delete</button></form></td></tr><?php endforeach; ?></table>
<?php require_once __DIR__ . '/_footer.php'; ?>
