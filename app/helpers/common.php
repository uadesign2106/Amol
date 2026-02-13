<?php
session_start();

require_once __DIR__ . '/../config/database.php';

function app_config(): array
{
    static $cfg = null;
    if ($cfg === null) {
        $cfg = require __DIR__ . '/../config/config.php';
    }
    return $cfg;
}

function e(?string $text): string
{
    return htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8');
}

function current_lang(): string
{
    if (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'mr'], true)) {
        $_SESSION['lang'] = $_GET['lang'];
    }
    return $_SESSION['lang'] ?? 'en';
}

function t(string $key): string
{
    $lang = current_lang();
    $translations = [
        'en' => [
            'home' => 'Home', 'products' => 'Products', 'about' => 'About Us', 'services' => 'Services',
            'contact' => 'Contact', 'enquiry' => 'Enquiry', 'hero_title' => 'Smart Agricultural Inputs for Better Yields',
            'hero_sub' => 'Green Plus supplies quality products for farms, agri-stores, food processors, and allied industries.',
            'contact_best_price' => 'Contact for Best Price', 'industries' => 'Industries', 'category' => 'Category',
            'filter' => 'Filter', 'reset' => 'Reset', 'submit' => 'Submit Enquiry', 'call_now' => 'Call Now',
            'whatsapp' => 'WhatsApp', 'email' => 'Email', 'name' => 'Name', 'email_addr' => 'Email', 'phone' => 'Phone',
            'message' => 'Message', 'product' => 'Product', 'all_categories' => 'All Categories', 'all_industries' => 'All Industries',
        ],
        'mr' => [
            'home' => 'मुख्यपृष्ठ', 'products' => 'उत्पादने', 'about' => 'आमच्याबद्दल', 'services' => 'सेवा',
            'contact' => 'संपर्क', 'enquiry' => 'चौकशी', 'hero_title' => 'उत्तम उत्पादनासाठी स्मार्ट कृषी उपाय',
            'hero_sub' => 'ग्रीन प्लस शेतकरी आणि उद्योगांसाठी दर्जेदार कृषी उत्पादने उपलब्ध करून देते.',
            'contact_best_price' => 'सर्वोत्तम किमतीसाठी संपर्क करा', 'industries' => 'उद्योग', 'category' => 'वर्ग',
            'filter' => 'फिल्टर', 'reset' => 'रिसेट', 'submit' => 'चौकशी पाठवा', 'call_now' => 'आत्ता कॉल करा',
            'whatsapp' => 'व्हॉट्सअ‍ॅप', 'email' => 'ईमेल', 'name' => 'नाव', 'email_addr' => 'ईमेल', 'phone' => 'फोन',
            'message' => 'संदेश', 'product' => 'उत्पादन', 'all_categories' => 'सर्व वर्ग', 'all_industries' => 'सर्व उद्योग',
        ],
    ];

    return $translations[$lang][$key] ?? $key;
}

function site_url(string $path = ''): string
{
    $base = rtrim(app_config()['base_url'], '/');
    return $base . '/' . ltrim($path, '/');
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf(): bool
{
    return isset($_POST['csrf_token']) && hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token']);
}

function pagination(int $total, int $perPage = 10): array
{
    $page = max(1, (int) ($_GET['page'] ?? 1));
    $pages = (int) ceil($total / $perPage);
    $offset = ($page - 1) * $perPage;
    return [$page, max(1, $pages), $offset, $perPage];
}

function enquiry_channels(string $productName, string $priceText): array
{
    $cfg = app_config();
    $text = rawurlencode("Hello Green Plus, I am interested in {$productName} ({$priceText}). Please share details.");

    return [
        'whatsapp' => "https://wa.me/{$cfg['admin_whatsapp']}?text={$text}",
        'email' => "mailto:{$cfg['admin_email']}?subject=" . rawurlencode("Enquiry: {$productName}") . "&body={$text}",
        'call' => 'tel:' . $cfg['admin_phone'],
    ];
}
