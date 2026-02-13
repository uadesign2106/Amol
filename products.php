<?php
$title = 'Products';
require_once __DIR__ . '/app/includes/header.php';
require_once __DIR__ . '/app/models/ProductModel.php';
$categories = getCategories();
$industries = getIndustries();
$filters = ['category_id' => $_GET['category_id'] ?? '', 'industry_id' => $_GET['industry_id'] ?? ''];
$products = getProducts($filters);
?>
<section class="container section">
    <h1><?= t('products') ?></h1>
    <form method="get" class="filters">
        <select name="category_id">
            <option value=""><?= t('all_categories') ?></option>
            <?php foreach ($categories as $category): ?>
                <option value="<?= (int) $category['id'] ?>" <?= $filters['category_id'] == $category['id'] ? 'selected' : '' ?>><?= e($category['category_name']) ?></option>
            <?php endforeach; ?>
        </select>
        <select name="industry_id">
            <option value=""><?= t('all_industries') ?></option>
            <?php foreach ($industries as $industry): ?>
                <option value="<?= (int) $industry['id'] ?>" <?= $filters['industry_id'] == $industry['id'] ? 'selected' : '' ?>><?= e($industry['industry_name']) ?></option>
            <?php endforeach; ?>
        </select>
        <button class="btn" type="submit"><?= t('filter') ?></button>
        <a class="btn alt" href="products.php"><?= t('reset') ?></a>
    </form>

    <div class="cards">
        <?php foreach ($products as $product): ?>
            <?php $priceText = $product['price_type'] === 'Fixed' ? '₹' . number_format((float) $product['price'], 2) : t('contact_best_price'); ?>
            <?php $channels = enquiry_channels($product['name'], $priceText); ?>
            <article class="card">
                <img src="<?= site_url('admin/uploads/' . e($product['image'] ?: 'placeholder.svg')) ?>" alt="<?= e($product['name']) ?>">
                <h3><?= e($product['name']) ?></h3>
                <p><strong><?= t('category') ?>:</strong> <?= e($product['category_name']) ?></p>
                <p><strong><?= t('industries') ?>:</strong> <?= e($product['industries'] ?: '-') ?></p>
                <p><strong><?= e($priceText) ?></strong></p>
                <div class="btn-row">
                    <a class="btn small" target="_blank" href="<?= e($channels['whatsapp']) ?>"><?= t('whatsapp') ?></a>
                    <a class="btn small alt" href="<?= e($channels['email']) ?>"><?= t('email') ?></a>
                    <a class="btn small" href="<?= site_url('contact.php?product_id=' . (int) $product['id']) ?>">Form</a>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>
<?php require_once __DIR__ . '/app/includes/footer.php'; ?>
