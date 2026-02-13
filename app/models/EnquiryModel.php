<?php
require_once __DIR__ . '/../helpers/common.php';

function createEnquiry(array $data): bool
{
    $sql = 'INSERT INTO enquiries(name, email, mobile, product_id, message, status, source, created_at)
            VALUES(:name, :email, :mobile, :product_id, :message, :status, :source, NOW())';
    $stmt = db()->prepare($sql);

    return $stmt->execute([
        ':name' => trim($data['name']),
        ':email' => trim($data['email']),
        ':mobile' => trim($data['mobile']),
        ':product_id' => $data['product_id'] ?: null,
        ':message' => trim($data['message']),
        ':status' => 'Pending',
        ':source' => $data['source'] ?? 'Form',
    ]);
}

function notifyAdmin(array $data): void
{
    $cfg = app_config();
    $subject = 'New enquiry from ' . $data['name'];
    $body = "Name: {$data['name']}\nEmail: {$data['email']}\nPhone: {$data['mobile']}\nProduct ID: {$data['product_id']}\nMessage: {$data['message']}";
    @mail($cfg['admin_email'], $subject, $body, 'From: noreply@' . $cfg['domain']);
}
