<?php /** @var array $user */ /** @var array $activity */ /** @var bool $isSelf */ ?>
<div class="row g-4">
    <div class="col-lg-6"><div class="bh-panel">
        <div class="d-flex gap-3 align-items-center mb-3"><img class="rounded-circle" style="width:72px;height:72px;object-fit:cover;background:#f3e9db" src="<?= e(img_url($user['avatar_path'] ?? null)) ?>" alt="">
            <div><h4 class="mb-0"><?= e($user['full_name']) ?></h4><div class="bh-muted"><?= e($user['email'] ?? $user['phone']) ?></div></div></div>
        <dl class="row mb-0">
            <dt class="col-5">SĐT nhận hàng</dt><dd class="col-7"><?= e($user['shipping_phone'] ?? '—') ?></dd>
            <dt class="col-5">Địa chỉ</dt><dd class="col-7"><?= e($user['shipping_address'] ?? '—') ?></dd>
            <dt class="col-5">Vai trò</dt><dd class="col-7"><?= e(\App\Enums\UserRole::from($user['role'])->label()) ?></dd>
            <dt class="col-5">Trạng thái</dt><dd class="col-7"><?= $user['is_active'] ? 'Hoạt động' : 'Đã khóa' ?></dd>
            <dt class="col-5">Ngày tạo</dt><dd class="col-7"><?= e(date('d/m/Y H:i', strtotime($user['created_at']))) ?></dd>
            <dt class="col-5">Số đơn hàng</dt><dd class="col-7"><?= (int) $activity['orders'] ?></dd>
            <dt class="col-5">Đã chi tiêu</dt><dd class="col-7"><?= e(format_vnd($activity['spent'])) ?></dd>
            <dt class="col-5">Số đánh giá</dt><dd class="col-7"><?= (int) $activity['reviews'] ?></dd>
        </dl>
    </div></div>
    <div class="col-lg-6"><div class="bh-panel">
        <h5>Quản lý tài khoản</h5>
        <?php if ($isSelf) : ?><p class="bh-muted mb-0">Bạn không thể tự hạ quyền hoặc tự khóa tài khoản của mình.</p>
        <?php else : ?>
            <form class="d-flex gap-2 mb-3" method="post" action="/admin/users/<?= (int) $user['id'] ?>/role" data-confirm="Đổi vai trò của người dùng này?"><?= csrf_field() ?>
                <select class="form-select" name="role"><?php foreach (\App\Enums\UserRole::cases() as $r) : ?><option value="<?= e($r->value) ?>"<?= $user['role'] === $r->value ? ' selected' : '' ?>><?= e($r->label()) ?></option><?php endforeach; ?></select>
                <button class="bh-btn bh-btn--primary" type="submit">Đổi vai trò</button></form>
            <form method="post" action="/admin/users/<?= (int) $user['id'] ?>/toggle-active" data-confirm="Thay đổi trạng thái tài khoản?"><?= csrf_field() ?>
                <button class="bh-btn bh-btn--dark" type="submit"><?= $user['is_active'] ? 'Vô hiệu hóa tài khoản' : 'Kích hoạt tài khoản' ?></button></form>
        <?php endif; ?>
        <a class="bh-btn bh-btn--outline mt-3" href="/admin/users">Quay lại</a>
    </div></div>
</div>
