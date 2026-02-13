<?php
$title = 'Home';
require_once __DIR__ . '/app/includes/header.php';
require_once __DIR__ . '/app/models/ProductModel.php';
$featured = array_slice(getProducts(), 0, 3);
?>
<section class="hero">
    <div class="container">
        <h1><?= t('hero_title') ?></h1>
        <p><?= t('hero_sub') ?></p>
        <a class="btn" href="<?= site_url('products.php') ?>"><?= t('products') ?></a>
        <a class="btn alt" href="<?= site_url('contact.php') ?>"><?= t('enquiry') ?></a>
    </div>
</section>
<section class="container section">
    <h2>Featured Products</h2>
    <div class="cards">
        <?php foreach ($featured as $product): ?>
            <?php $priceText = $product['price_type'] === 'Fixed' ? '₹' . number_format((float) $product['price'], 2) : t('contact_best_price'); ?>
            <?php $channels = enquiry_channels($product['name'], $priceText); ?>
            <article class="card">
                <img src="<?= site_url('admin/uploads/' . e($product['image'] ?: 'placeholder.svg')) ?>" alt="<?= e($product['name']) ?>">
                <h3><?= e($product['name']) ?></h3>
                <p><?= e($product['category_name']) ?></p>
                <p><strong><?= e($priceText) ?></strong></p>
                <div class="btn-row">
                    <a class="btn small" target="_blank" href="<?= e($channels['whatsapp']) ?>"><?= t('whatsapp') ?></a>
                    <a class="btn small alt" href="<?= e($channels['email']) ?>"><?= t('email') ?></a>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>
<?php require_once __DIR__ . '/app/includes/footer.php'; ?>
