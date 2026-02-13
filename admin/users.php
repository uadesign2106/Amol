<?php
$title = 'Users';
require_once __DIR__ . '/_header.php';
requirePermission('users.manage');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf()) {
    if (!empty($_POST['create_user'])) {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $roleId = (int) ($_POST['role_id'] ?? 0);
        $password = (string) ($_POST['password'] ?? '');
        if ($name && filter_var($email, FILTER_VALIDATE_EMAIL) && $roleId && strlen($password) >= 8) {
            $stmt = db()->prepare('INSERT INTO users(name,email,password,role_id,status,created_at) VALUES(:name,:email,:password,:role_id,:status,NOW())');
            $stmt->execute([
                ':name' => $name,
                ':email' => $email,
                ':password' => password_hash($password, PASSWORD_DEFAULT),
                ':role_id' => $roleId,
                ':status' => (int) ($_POST['status'] ?? 1),
            ]);
        }
    }

    if (!empty($_POST['toggle_id'])) {
        db()->prepare('UPDATE users SET status = :status WHERE id = :id')->execute([
            ':status' => (int) $_POST['status'],
            ':id' => (int) $_POST['toggle_id'],
        ]);
    }

    if (!empty($_POST['reset_id']) && !empty($_POST['new_password']) && strlen($_POST['new_password']) >= 8) {
        db()->prepare('UPDATE users SET password = :password WHERE id = :id')->execute([
            ':password' => password_hash($_POST['new_password'], PASSWORD_DEFAULT),
            ':id' => (int) $_POST['reset_id'],
        ]);
    }
}

$roles = db()->query('SELECT id, role_name FROM roles ORDER BY id')->fetchAll();
$users = db()->query('SELECT u.*, r.role_name FROM users u JOIN roles r ON r.id = u.role_id ORDER BY u.id DESC')->fetchAll();
?>
<h1>User Management</h1>
<form method="post" class="admin-form-grid">
    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
    <input type="hidden" name="create_user" value="1">
    <input type="text" name="name" placeholder="Full Name" required>
    <input type="email" name="email" placeholder="Email" required>
    <select name="role_id" required><?php foreach ($roles as $role): ?><option value="<?= (int) $role['id'] ?>"><?= e($role['role_name']) ?></option><?php endforeach; ?></select>
    <select name="status"><option value="1">Active</option><option value="0">Inactive</option></select>
    <input type="password" name="password" placeholder="Password (min 8 chars)" required>
    <button class="btn">Create User</button>
</form>

<table>
    <tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Actions</th></tr>
    <?php foreach ($users as $u): ?>
        <tr>
            <td><?= (int) $u['id'] ?></td>
            <td><?= e($u['name']) ?></td>
            <td><?= e($u['email']) ?></td>
            <td><?= e($u['role_name']) ?></td>
            <td><?= (int) $u['status'] === 1 ? 'Active' : 'Inactive' ?></td>
            <td>
                <form method="post" class="inline">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                    <input type="hidden" name="toggle_id" value="<?= (int) $u['id'] ?>">
                    <input type="hidden" name="status" value="<?= (int) $u['status'] === 1 ? 0 : 1 ?>">
                    <button><?= (int) $u['status'] === 1 ? 'Deactivate' : 'Activate' ?></button>
                </form>
                <form method="post" class="inline">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                    <input type="hidden" name="reset_id" value="<?= (int) $u['id'] ?>">
                    <input type="password" name="new_password" placeholder="New password" required>
                    <button>Reset Password</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
</table>
<?php require_once __DIR__ . '/_footer.php'; ?>
