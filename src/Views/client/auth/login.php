<section class="bh-auth-page">
    <div class="container">
        <div class="bh-auth bh-panel">
            <h1 class="bh-auth__title">Đăng nhập</h1>
            <p class="bh-auth__sub">Chào mừng bạn trở lại BakeHouse</p>
            <form method="post" action="/login" novalidate>
                <?= csrf_field() ?>
                <div class="mb-3"><label class="form-label" for="identity">Email hoặc số điện thoại</label>
                    <input class="form-control" id="identity" name="identity" value="<?= old('identity') ?>" autocomplete="username" required><?= form_error('identity') ?></div>
                <div class="mb-3"><label class="form-label" for="password">Mật khẩu</label>
                    <input class="form-control" id="password" type="password" name="password" autocomplete="current-password" required><?= form_error('password') ?></div>
                <button class="bh-btn bh-btn--primary w-100 mt-2" type="submit">Đăng nhập</button>
                <div class="bh-auth__links"><a href="/forgot-password">Quên mật khẩu?</a><span>Chưa có tài khoản? <a href="/register">Đăng ký</a></span></div>
            </form>
        </div>
    </div>
</section>
