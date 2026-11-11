<?php
/** @var string $content */
/** @var string|null $title */
$flashes = \App\Core\Session::pullFlash();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?= e(\App\Core\Csrf::token()) ?>">
    <title><?= e(($title ?? '') !== '' ? $title . ' | BakeHouse' : 'BakeHouse') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Poppins:wght@400;500;600&display=swap&subset=vietnamese" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= e(asset('assets/css/tokens.css')) ?>" rel="stylesheet">
    <link href="<?= e(asset('assets/css/app.css')) ?>" rel="stylesheet">
</head>
<body data-shipping-fee="<?= (int) \App\Core\Env::int('SHIPPING_FEE', 30000) ?>">
<?php partial('header'); ?>
<main>
    <?= $content ?>
</main>
<?php partial('footer'); ?>
<?php partial('chatbot'); ?>
<?php partial('confirm-modal'); ?>
<div class="toast-container position-fixed top-0 end-0 p-3" id="bhToasts" style="z-index:1100">
    <?php foreach ($flashes as $flash) : ?>
        <span data-flash="<?= e($flash['type']) ?>" data-message="<?= e($flash['message']) ?>"></span>
    <?php endforeach; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= e(asset('assets/js/app.js')) ?>"></script>
</body>
</html>
