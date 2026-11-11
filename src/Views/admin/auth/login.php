<div class="bh-panel mx-auto mt-4" style="max-width:420px">
    <div class="text-center mb-4"><span class="bh-logo" style="color:var(--bh-color-espresso)">Bake<span class="bh-logo__accent">House</span></span><div class="bh-muted">Khu vực quản trị</div></div>
    <form method="post" action="/admin/login" novalidate>
        <?= csrf_field() ?>
        <div class="mb-3"><label class="form-label" for="identity">Email</label><input class="form-control" id="identity" name="identity" value="<?= old('identity') ?>" required><?= form_error('identity') ?></div>
        <div class="mb-3"><label class="form-label" for="password">Mật khẩu</label><input class="form-control" id="password" type="password" name="password" required><?= form_error('password') ?></div>
        <button class="bh-btn bh-btn--dark w-100" type="submit">Đăng nhập</button>
    </form>
</div>
