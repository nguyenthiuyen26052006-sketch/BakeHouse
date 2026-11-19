<?php
$navItems = [
    ['/', 'Trang chủ', '/'], ['/about', 'Giới thiệu', '/about'], ['/products', 'Sản phẩm', '/products'],
    ['/gallery', 'Thư viện', '/gallery'], ['/blog', 'Blog', '/blog'], ['/contact', 'Liên hệ', '/contact'],
];
$user = auth_user();
$cartCount = $user ? cart_count() : 0;
?>
<header class="bh-header">
    <div class="container bh-header__inner d-flex align-items-center justify-content-between">
        <a class="bh-logo" href="/">Bake<span class="bh-logo__accent">House</span></a>
        <nav class="bh-nav d-none d-lg-flex" aria-label="Điều hướng chính">
            <?php foreach ($navItems as [$href, $label, $prefix]) : ?>
                <a class="bh-nav__link<?= nav_active($prefix) ?>" href="<?= e($href) ?>"><?= e($label) ?></a>
            <?php endforeach; ?>
        </nav>
        <div class="d-flex align-items-center gap-2">
            <a class="bh-icon-btn" href="/products" aria-label="Tìm kiếm sản phẩm"><i class="bi bi-search"></i></a>
            <?php if ($user) : ?>
                <div class="dropdown">
                    <button class="bh-icon-btn" type="button" data-bs-toggle="dropdown" aria-label="Tài khoản"><i class="bi bi-person-fill"></i></button>
                    <ul class="dropdown-menu dropdown-menu-end bh-account-menu">
                        <li class="bh-account-menu__header">
                            <div class="bh-account-menu__avatar"><i class="bi bi-person-fill"></i></div>
                            <div class="min-w-0">
                                <div class="bh-account-menu__name"><?= e($user['full_name'] ?: ($user['email'] ?? $user['phone'])) ?></div>
                                <?php if ($user['full_name'] && ($user['email'] ?? $user['phone'])) : ?>
                                    <div class="bh-account-menu__sub"><?= e($user['email'] ?? $user['phone']) ?></div>
                                <?php endif; ?>
                            </div>
                        </li>
                        <li><hr class="dropdown-divider bh-account-menu__divider"></li>
                        <li><a class="dropdown-item bh-account-menu__item" href="/account"><i class="bi bi-person-circle"></i> Tài khoản của tôi</a></li>
                        <li><a class="dropdown-item bh-account-menu__item" href="/orders"><i class="bi bi-receipt"></i> Đơn hàng của tôi</a></li>
                        <li><a class="dropdown-item bh-account-menu__item" href="/contacts"><i class="bi bi-envelope"></i> Liên hệ của tôi</a></li>
                        <?php if (\App\Core\Auth::isAdmin()) : ?>
                            <li><a class="dropdown-item bh-account-menu__item" href="/admin"><i class="bi bi-speedometer2"></i> Trang quản trị</a></li>
                        <?php endif; ?>
                        <li><hr class="dropdown-divider bh-account-menu__divider"></li>
                        <li>
                            <form method="post" action="/logout"><?= csrf_field() ?>
                                <button class="dropdown-item bh-account-menu__item bh-account-menu__item--danger" type="submit"><i class="bi bi-box-arrow-right"></i> Đăng xuất</button>
                            </form>
                        </li>
                    </ul>
                </div>
                <a class="bh-icon-btn" href="/cart" aria-label="Giỏ hàng">
                    <i class="bi bi-cart-fill"></i>
                    <span class="bh-cart-badge" style="<?= $cartCount > 0 ? '' : 'display:none' ?>"><?= $cartCount ?></span>
                </a>
            <?php else : ?>
                <a class="bh-btn bh-btn--header bh-btn--light" href="/login">Đăng nhập</a>
                <a class="bh-btn bh-btn--header bh-btn--primary" href="/register">Đăng ký</a>
            <?php endif; ?>
            <button class="bh-icon-btn d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#bhMenu" aria-label="Menu"><i class="bi bi-list"></i></button>
        </div>
    </div>
    <?php if ($user) : ?>
    <div class="bh-searchbar">
        <div class="container">
            <form action="/products" method="get" role="search" class="bh-search">
                <input class="form-control" type="search" name="q" placeholder="Tìm theo tên hoặc thương hiệu…" aria-label="Tìm kiếm sản phẩm" value="<?= e($_GET['q'] ?? '') ?>">
                <button class="bh-search__btn" type="submit" aria-label="Tìm kiếm"><i class="bi bi-search"></i></button>
            </form>
        </div>
    </div>
    <?php endif; ?>
</header>
<div class="offcanvas offcanvas-start bh-offcanvas" tabindex="-1" id="bhMenu">
    <div class="offcanvas-header">
        <span class="bh-logo">Bake<span class="bh-logo__accent">House</span></span>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Đóng"></button>
    </div>
    <div class="offcanvas-body">
        <?php foreach ($navItems as [$href, $label, $prefix]) : ?>
            <a class="bh-nav__link<?= nav_active($prefix) ?>" href="<?= e($href) ?>"><?= e($label) ?></a>
        <?php endforeach; ?>
    </div>
</div>
