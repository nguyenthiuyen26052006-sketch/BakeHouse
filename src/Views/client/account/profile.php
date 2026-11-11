<?php /** @var array $user */ partial('hero', ['heroTitle' => 'Tài khoản', 'crumbs' => [['Trang chủ', '/'], ['Tài khoản', null]]]); ?>
<div class="container py-5">
    <div class="row g-4 justify-content-center">
        <div class="col-lg-7">
            <div class="bh-panel">
                <form method="post" action="/account" enctype="multipart/form-data" novalidate>
                    <?= csrf_field() ?>
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <img class="rounded-circle" style="width:84px;height:84px;object-fit:cover;background:#f3e9db" src="<?= e(img_url($user['avatar_path'] ?? null, 'assets/img/product-placeholder.svg')) ?>" alt="Avatar" id="bhAvatarPreviewImg">
                        <div class="flex-grow-1">
                            <label class="form-label" for="avatar">Ảnh đại diện (JPG/PNG/WEBP ≤ 2MB)</label>
                            <input class="form-control" type="file" id="avatar" name="avatar" accept="image/jpeg,image/png,image/webp"><?= form_error('avatar') ?>
                        </div>
                    </div>
                    <div class="mb-3"><label class="form-label">Đăng nhập bằng</label>
                        <input class="form-control" value="<?= e($user['email'] ?? $user['phone']) ?>" disabled></div>
                    <div class="mb-3"><label class="form-label" for="full_name">Họ tên</label>
                        <input class="form-control" id="full_name" name="full_name" value="<?= old('full_name', (string) $user['full_name']) ?>" required><?= form_error('full_name') ?></div>
                    <div class="mb-3"><label class="form-label" for="shipping_phone">Số điện thoại nhận hàng</label>
                        <input class="form-control" id="shipping_phone" name="shipping_phone" value="<?= old('shipping_phone', (string) $user['shipping_phone']) ?>"><?= form_error('shipping_phone') ?></div>
                    <div class="mb-3"><label class="form-label" for="shipping_address">Địa chỉ giao hàng</label>
                        <textarea class="form-control" id="shipping_address" name="shipping_address" rows="2"><?= old('shipping_address', (string) $user['shipping_address']) ?></textarea><?= form_error('shipping_address') ?></div>
                    <button class="bh-btn bh-btn--primary" type="submit">Lưu thay đổi</button>
                    <a class="bh-btn bh-btn--outline ms-2" href="/orders">Đơn hàng của tôi</a>
                </form>
            </div>
        </div>
    </div>
</div>
