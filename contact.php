<?php
$title = 'Contact';
require_once __DIR__ . '/app/includes/header.php';
require_once __DIR__ . '/app/models/ProductModel.php';
require_once __DIR__ . '/app/models/EnquiryModel.php';

$products = getProducts();
$productId = (int) ($_GET['product_id'] ?? $_POST['product_id'] ?? 0);
$selectedProduct = $productId ? getProductById($productId) : null;
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $error = 'Invalid security token.';
    } else {
        $payload = [
            'name' => $_POST['name'] ?? '',
            'email' => $_POST['email'] ?? '',
            'mobile' => $_POST['mobile'] ?? '',
            'product_id' => $productId,
            'message' => $_POST['message'] ?? '',
            'source' => 'Form',
        ];

        if ($payload['name'] && filter_var($payload['email'], FILTER_VALIDATE_EMAIL) && $payload['mobile']) {
            if (createEnquiry($payload)) {
                notifyAdmin($payload);
                $success = 'Thank you! Enquiry submitted successfully.';
            }
        } else {
            $error = 'Please fill all required fields with valid details.';
        }
    }
}
$priceText = $selectedProduct ? ($selectedProduct['price_type'] === 'Fixed' ? '₹' . number_format((float) $selectedProduct['price'], 2) : t('contact_best_price')) : '';
?>
<section class="container section">
    <h1><?= t('contact') ?> / <?= t('enquiry') ?></h1>
    <?php if ($success): ?><p class="notice success"><?= e($success) ?></p><?php endif; ?>
    <?php if ($error): ?><p class="notice error"><?= e($error) ?></p><?php endif; ?>

    <?php if ($selectedProduct): ?>
        <div class="notice">
            <strong>Selected Product:</strong> <?= e($selectedProduct['name']) ?> | <strong>Price:</strong> <?= e($priceText) ?>
            <?php $channels = enquiry_channels($selectedProduct['name'], $priceText); ?>
            <div class="btn-row top-space">
                <a class="btn small" target="_blank" href="<?= e($channels['whatsapp']) ?>">WhatsApp</a>
                <a class="btn small alt" href="<?= e($channels['email']) ?>">Email</a>
            </div>
        </div>
    <?php endif; ?>

    <form method="post" class="contact-form">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <label><?= t('name') ?>* <input type="text" name="name" required></label>
        <label><?= t('email_addr') ?>* <input type="email" name="email" required></label>
        <label><?= t('phone') ?>* <input type="tel" name="mobile" pattern="[0-9+ ]{10,15}" required></label>
        <label><?= t('product') ?>
            <select name="product_id">
                <option value="">Select Product</option>
                <?php foreach ($products as $product): ?>
                    <option value="<?= (int) $product['id'] ?>" <?= $selectedProduct && $selectedProduct['id'] == $product['id'] ? 'selected' : '' ?>><?= e($product['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label><?= t('message') ?> <textarea name="message" rows="4"></textarea></label>
        <button class="btn" type="submit"><?= t('submit') ?></button>
    </form>

    <p class="top-space"><a class="btn" href="tel:<?= e(app_config()['admin_phone']) ?>"><?= t('call_now') ?></a></p>
</section>
<?php require_once __DIR__ . '/app/includes/footer.php'; ?>
