<section class="bh-auth-page">
    <div class="container">
        <div class="bh-auth bh-auth--wide bh-panel">
            <h1 class="bh-auth__title">Đăng ký</h1>
            <p class="bh-auth__sub">Tạo tài khoản để đặt hàng và theo dõi đơn</p>
            <form method="post" action="/register" novalidate>
                <?= csrf_field() ?>
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label" for="full_name">Họ tên</label>
                        <input class="form-control" id="full_name" name="full_name" value="<?= old('full_name') ?>" autocomplete="name" required><?= form_error('full_name') ?></div>
                    <div class="col-md-6"><label class="form-label" for="identity">Email hoặc số điện thoại</label>
                        <input class="form-control" id="identity" name="identity" value="<?= old('identity') ?>" autocomplete="username" required><?= form_error('identity') ?></div>
                    <div class="col-md-6"><label class="form-label" for="password">Mật khẩu</label>
                        <input class="form-control" id="password" type="password" name="password" autocomplete="new-password" required><?= form_error('password') ?></div>
                    <div class="col-md-6"><label class="form-label" for="password_confirmation">Xác nhận mật khẩu</label>
                        <input class="form-control" id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" required><?= form_error('password_confirmation') ?></div>
                </div>
                <div class="form-text mt-2">Mật khẩu tối thiểu 8 ký tự, gồm chữ hoa, chữ thường và số.</div>
                <button class="bh-btn bh-btn--primary w-100 mt-3" type="submit">Tạo tài khoản</button>
                <div class="bh-auth__links justify-content-center"><span>Đã có tài khoản? <a href="/login">Đăng nhập</a></span></div>
            </form>
        </div>
    </div>
</section>
