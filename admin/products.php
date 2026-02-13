<?php
$title = 'Products';
require_once __DIR__ . '/_header.php';
requirePermission('products.manage');

$categories = getCategories(false);
$industries = getIndustries(false);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf()) {
    if (!empty($_POST['delete_id'])) {
        $id = (int) $_POST['delete_id'];
        db()->prepare('DELETE FROM product_industries WHERE product_id=:id')->execute([':id' => $id]);
        db()->prepare('DELETE FROM products WHERE id=:id')->execute([':id' => $id]);
    } else {
        $id = (int) ($_POST['id'] ?? 0);
        $imageName = $_POST['existing_image'] ?? '';
        if (!empty($_FILES['image']['name']) && is_uploaded_file($_FILES['image']['tmp_name'])) {
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $imageName = uniqid('prod_', true) . '.' . strtolower($ext);
            move_uploaded_file($_FILES['image']['tmp_name'], __DIR__ . '/uploads/' . $imageName);
        }

        $payload = [
            ':category_id' => (int) $_POST['category_id'], ':name' => trim($_POST['name']), ':description' => trim($_POST['description']),
            ':image' => $imageName, ':price_type' => $_POST['price_type'], ':price' => $_POST['price_type'] === 'Fixed' ? (float) $_POST['price'] : null,
            ':status' => (int) $_POST['status'],
        ];

        if ($id) {
            $payload[':id'] = $id;
            db()->prepare('UPDATE products SET category_id=:category_id,name=:name,description=:description,image=:image,price_type=:price_type,price=:price,status=:status WHERE id=:id')->execute($payload);
            db()->prepare('DELETE FROM product_industries WHERE product_id=:id')->execute([':id' => $id]);
        } else {
            db()->prepare('INSERT INTO products(category_id,name,description,image,price_type,price,status,created_at) VALUES(:category_id,:name,:description,:image,:price_type,:price,:status,NOW())')->execute($payload);
            $id = (int) db()->lastInsertId();
        }

        foreach (($_POST['industry_ids'] ?? []) as $industryId) {
            db()->prepare('INSERT INTO product_industries(product_id,industry_id) VALUES(:pid,:iid)')->execute([':pid' => $id, ':iid' => (int) $industryId]);
        }
    }
}

$edit = !empty($_GET['edit']) ? getProductById((int) $_GET['edit']) : null;
$count = (int) db()->query('SELECT COUNT(*) FROM products')->fetchColumn();
[$page, $pages, $offset, $limit] = pagination($count, 8);
$stmt = db()->prepare('SELECT p.*, c.category_name FROM products p JOIN categories c ON c.id = p.category_id ORDER BY p.id DESC LIMIT :offset,:lim');
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
$stmt->execute();
$rows = $stmt->fetchAll();
?>
<h1>Product Management</h1>
<form method="post" enctype="multipart/form-data" class="admin-form-grid">
    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
    <input type="hidden" name="id" value="<?= (int)($edit['id'] ?? 0) ?>">
    <input type="hidden" name="existing_image" value="<?= e($edit['image'] ?? '') ?>">
    <input name="name" placeholder="Product Name" value="<?= e($edit['name'] ?? '') ?>" required>
    <select name="category_id" required><?php foreach($categories as $c): ?><option value="<?= $c['id'] ?>" <?= isset($edit['category_id']) && $edit['category_id']==$c['id']?'selected':'' ?>><?= e($c['category_name']) ?></option><?php endforeach; ?></select>
    <textarea name="description" placeholder="Description"><?= e($edit['description'] ?? '') ?></textarea>
    <label>Image <input type="file" name="image" accept="image/*"></label>
    <label>Price Type <select name="price_type"><option value="Fixed" <?= isset($edit['price_type']) && $edit['price_type']==='Fixed'?'selected':'' ?>>Fixed</option><option value="Hidden" <?= isset($edit['price_type']) && $edit['price_type']==='Hidden'?'selected':'' ?>>Hidden</option></select></label>
    <input name="price" type="number" step="0.01" placeholder="Price" value="<?= e($edit['price'] ?? '') ?>">
    <label>Status <select name="status"><option value="1">Active</option><option value="0" <?= isset($edit['status']) && !$edit['status']?'selected':'' ?>>Inactive</option></select></label>
    <fieldset><legend>Industries</legend><?php foreach($industries as $i): ?><label><input type="checkbox" name="industry_ids[]" value="<?= $i['id'] ?>" <?= isset($edit['industry_ids']) && in_array($i['id'], $edit['industry_ids'])?'checked':'' ?>><?= e($i['industry_name']) ?></label><?php endforeach; ?></fieldset>
    <button class="btn"><?= $edit ? 'Update Product' : 'Add Product' ?></button>
</form>
<table><tr><th>ID</th><th>Name</th><th>Category</th><th>Price</th><th>Status</th><th>Action</th></tr><?php foreach($rows as $r): ?><tr><td><?= $r['id'] ?></td><td><?= e($r['name']) ?></td><td><?= e($r['category_name']) ?></td><td><?= $r['price_type']==='Fixed' ? '₹'.number_format((float)$r['price'],2):'Hidden' ?></td><td><?= $r['status']?'Active':'Inactive' ?></td><td><a href="?edit=<?= $r['id'] ?>">Edit</a><form method="post" class="inline"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="delete_id" value="<?= $r['id'] ?>"><button onclick="return confirm('Delete product?')">Delete</button></form></td></tr><?php endforeach; ?></table>
<p>Page <?= $page ?> of <?= $pages ?></p>
<?php require_once __DIR__ . '/_footer.php'; ?>
