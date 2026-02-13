<?php
require_once __DIR__ . '/../helpers/common.php';
$config = app_config();
$lang = current_lang();
?>
<!doctype html>
<html lang="<?= e($lang) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? $config['site_name']) ?> | <?= e($config['site_name']) ?></title>
    <meta name="description" content="Green Plus Pune - enquiry based agricultural and allied industry product solutions.">
    <link rel="canonical" href="https://<?= e($config['domain']) . e($_SERVER['REQUEST_URI'] ?? '/') ?>">
    <link rel="stylesheet" href="<?= site_url('assets/css/style.css') ?>">
</head>
<body>
<header class="site-header">
    <div class="container nav-wrap">
        <a class="logo" href="<?= site_url('index.php') ?>">Green Plus</a>
        <nav>
            <a href="<?= site_url('index.php') ?>"><?= t('home') ?></a>
            <a href="<?= site_url('products.php') ?>"><?= t('products') ?></a>
            <a href="<?= site_url('about.php') ?>"><?= t('about') ?></a>
            <a href="<?= site_url('services.php') ?>"><?= t('services') ?></a>
            <a href="<?= site_url('contact.php') ?>"><?= t('contact') ?></a>
        </nav>
        <div class="lang-switch">
            <a href="?lang=en">EN</a> | <a href="?lang=mr">मराठी</a>
        </div>
    </div>
</header>
<main>
