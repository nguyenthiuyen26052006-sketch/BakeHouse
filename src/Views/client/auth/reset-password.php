<?php /** @var string $token */ ?>
<section class="bh-auth-page">
    <div class="container">
        <div class="bh-auth bh-panel">
            <h1 class="bh-auth__title">Đặt lại mật khẩu</h1>
            <p class="bh-auth__sub">Nhập mật khẩu mới cho tài khoản của bạn.</p>
            <form method="post" action="/reset-password/<?= e($token) ?>" novalidate>
                <?= csrf_field() ?>
                <div class="mb-3"><label class="form-label" for="password">Mật khẩu mới</label>
                    <input class="form-control" id="password" type="password" name="password" autocomplete="new-password" required>
                    <div class="form-text">Tối thiểu 8 ký tự, gồm chữ hoa, chữ thường và số.</div><?= form_error('password') ?></div>
                <div class="mb-3"><label class="form-label" for="password_confirmation">Xác nhận mật khẩu</label>
                    <input class="form-control" id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" required></div>
                <button class="bh-btn bh-btn--primary w-100" type="submit">Cập nhật mật khẩu</button>
            </form>
        </div>
    </div>
</section>
