<section class="bh-auth-page">
    <div class="container">
        <div class="bh-auth bh-panel">
            <h1 class="bh-auth__title">Quên mật khẩu</h1>
            <p class="bh-auth__sub">Nhập email hoặc số điện thoại đã đăng ký, chúng tôi sẽ gửi liên kết đặt lại mật khẩu.</p>
            <form method="post" action="/forgot-password" novalidate>
                <?= csrf_field() ?>
                <div class="mb-3"><label class="form-label" for="identity">Email hoặc số điện thoại</label>
                    <input class="form-control" id="identity" name="identity" value="<?= old('identity') ?>" required><?= form_error('identity') ?></div>
                <button class="bh-btn bh-btn--primary w-100" type="submit">Gửi liên kết</button>
                <div class="bh-auth__links justify-content-center"><a href="/login">Quay lại đăng nhập</a></div>
            </form>
        </div>
    </div>
</section>
