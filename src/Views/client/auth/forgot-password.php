<?php /** @var string $step */ ?>
<section class="bh-auth-page">
    <div class="container">
        <div class="bh-auth bh-panel">
            <?php if ($step === 'reset') : ?>
                <h1 class="bh-auth__title">Đặt mật khẩu mới</h1>
                <p class="bh-auth__sub">Nhập mật khẩu mới cho tài khoản của bạn.</p>
                <form method="post" action="/forgot-password/reset" novalidate>
                    <?= csrf_field() ?>
                    <div class="mb-3"><label class="form-label" for="password">Mật khẩu mới</label>
                        <input class="form-control" id="password" type="password" name="password" autocomplete="new-password" required>
                        <div class="form-text">Tối thiểu 8 ký tự, gồm chữ hoa, chữ thường và số.</div><?= form_error('password') ?></div>
                    <div class="mb-3"><label class="form-label" for="password_confirmation">Xác nhận mật khẩu</label>
                        <input class="form-control" id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" required></div>
                    <button class="bh-btn bh-btn--primary w-100" type="submit">Xác nhận</button>
                    <div class="bh-auth__links justify-content-center"><a href="/login">Quay lại đăng nhập</a></div>
                </form>
            <?php else : ?>
                <h1 class="bh-auth__title">Quên mật khẩu</h1>
                <p class="bh-auth__sub">Nhập email hoặc số điện thoại đã đăng ký để đặt mật khẩu mới.</p>
                <form method="post" action="/forgot-password" novalidate>
                    <?= csrf_field() ?>
                    <div class="mb-3"><label class="form-label" for="identity">Email hoặc số điện thoại</label>
                        <input class="form-control" id="identity" name="identity" value="<?= old('identity') ?>" required><?= form_error('identity') ?></div>
                    <button class="bh-btn bh-btn--primary w-100" type="submit">Tiếp tục</button>
                    <div class="bh-auth__links justify-content-center"><a href="/login">Quay lại đăng nhập</a></div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</section>
