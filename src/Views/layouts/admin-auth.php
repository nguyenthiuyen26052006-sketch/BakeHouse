<?php /** @var string $content */ $flashes = \App\Core\Session::pullFlash(); ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Đăng nhập quản trị | BakeHouse</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Poppins:wght@400;500;600&display=swap&subset=vietnamese" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= e(asset('assets/css/tokens.css')) ?>" rel="stylesheet">
    <link href="<?= e(asset('assets/css/app.css')) ?>" rel="stylesheet">
</head>
<body style="background:var(--bh-color-espresso)">
<div class="container py-5"><?php foreach ($flashes as $flash) : ?>
    <div class="alert alert-<?= e($flash['type']) ?> mx-auto" style="max-width:420px"><?= e($flash['message']) ?></div>
<?php endforeach; ?><?= $content ?></div>
</body>
</html>
