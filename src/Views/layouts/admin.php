<?php
/** @var string $content */ /** @var string|null $title */
$flashes = \App\Core\Session::pullFlash();
$admin = auth_user();
$nav = [
    ['/admin', 'Tổng quan', 'bi-speedometer2', true],
    ['/admin/products', 'Nguyên vật liệu', 'bi-basket2', false],
    ['/admin/orders', 'Đơn hàng', 'bi-receipt', false],
    ['group', 'Danh mục chung'],
    ['/admin/categories', 'Danh mục', 'bi-grid', false],
    ['/admin/brands', 'Thương hiệu', 'bi-award', false],
    ['/admin/tags', 'Tag', 'bi-tags', false],
    ['/admin/banners', 'Banner', 'bi-images', false],
    ['group', 'Hệ thống'],
    ['/admin/users', 'Người dùng', 'bi-people', false],
    ['/admin/reviews', 'Đánh giá', 'bi-star', false],
    ['/admin/contacts', 'Liên hệ', 'bi-envelope', false],
];
$path = current_path();
$renderNav = static function () use ($nav, $path): void {
    foreach ($nav as $item) {
        if ($item[0] === 'group') {
            echo '<div class="bh-admin__group">' . e($item[1]) . '</div>';
            continue;
        }
        $active = $item[3] ? $path === $item[0] : str_starts_with($path, $item[0]);
        echo '<a class="' . ($active ? 'is-active' : '') . '" href="' . e($item[0]) . '"><i class="bi ' . e($item[2]) . '"></i> ' . e($item[1]) . '</a>';
    }
};
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?= e(\App\Core\Csrf::token()) ?>">
    <title><?= e(($title ?? '') !== '' ? $title . ' | Quản trị BakeHouse' : 'Quản trị BakeHouse') ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Poppins:wght@400;500;600&display=swap&subset=vietnamese" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= e(asset('assets/css/tokens.css')) ?>" rel="stylesheet">
    <link href="<?= e(asset('assets/css/app.css')) ?>" rel="stylesheet">
</head>
<body>
<div class="bh-admin">
    <aside class="bh-admin__sidebar d-none d-lg-block">
        <a class="bh-logo bh-admin__brand" href="/admin">Bake<span class="bh-logo__accent">House</span> <small style="font-size:.75rem;font-family:var(--bh-font-body);color:#a8978a">Admin</small></a>
        <nav class="bh-admin__nav" aria-label="Điều hướng quản trị"><?php $renderNav(); ?></nav>
    </aside>
    <div class="offcanvas offcanvas-start bh-offcanvas" tabindex="-1" id="bhAdminMenu" aria-label="Menu quản trị">
        <div class="offcanvas-header">
            <span class="bh-logo">Bake<span class="bh-logo__accent">House</span></span>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Đóng"></button>
        </div>
        <div class="offcanvas-body p-0"><nav class="bh-admin__nav"><?php $renderNav(); ?></nav></div>
    </div>
    <div class="bh-admin__main">
        <header class="bh-admin__topbar">
            <div class="d-flex align-items-center gap-2 min-w-0">
                <button class="bh-btn bh-btn--outline bh-btn--sm d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#bhAdminMenu" aria-label="Mở menu"><i class="bi bi-list"></i></button>
                <h1 class="h4 m-0 text-truncate"><?= e($title ?? '') ?></h1>
            </div>
            <div class="d-flex align-items-center gap-3">
                <a class="small text-decoration-none d-none d-md-inline" href="/" target="_blank" rel="noopener"><i class="bi bi-box-arrow-up-right"></i> Xem website</a>
                <span class="small d-none d-sm-inline"><i class="bi bi-person-circle"></i> <?= e($admin['full_name'] ?: $admin['email']) ?></span>
                <form method="post" action="/admin/logout"><?= csrf_field() ?><button class="bh-btn bh-btn--outline bh-btn--sm" type="submit" aria-label="Đăng xuất"><i class="bi bi-box-arrow-right"></i><span class="d-none d-sm-inline"> Đăng xuất</span></button></form>
            </div>
        </header>
        <div class="bh-admin__content"><?= $content ?></div>
    </div>
</div>
<?php partial('confirm-modal'); ?>
<div class="toast-container position-fixed top-0 end-0 p-3" id="bhToasts" style="z-index:1100">
    <?php foreach ($flashes as $flash) : ?><span data-flash="<?= e($flash['type']) ?>" data-message="<?= e($flash['message']) ?>"></span><?php endforeach; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= e(asset('assets/js/app.js')) ?>"></script>
</body>
</html>
