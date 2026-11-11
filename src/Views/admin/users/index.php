<?php /** @var array $rows */ /** @var \App\Core\Paginator $paginator */ /** @var string $search */ /** @var string $role */ /** @var string $active */ /** @var int $currentId */
partial('admin-filter', ['search' => $search, 'placeholder' => 'Tìm theo email, SĐT, họ tên…', 'selects' => [
    'role' => ['Mọi vai trò', ['user' => 'Người dùng', 'admin' => 'Quản trị viên'], $role],
    'active' => ['Mọi trạng thái', ['1' => 'Đang hoạt động', '0' => 'Đã khóa'], $active],
]]); ?>
<div class="bh-panel p-0 table-responsive"><table class="table table-hover align-middle mb-0 bh-table">
    <thead><tr><th>#</th><th>Họ tên</th><th>Email / SĐT</th><th>Vai trò</th><th>Trạng thái</th><th>Ngày tạo</th><th class="text-end">Thao tác</th></tr></thead><tbody>
    <?php foreach ($rows as $user) : $self = (int) $user['id'] === $currentId; ?>
        <tr>
            <td><?= (int) $user['id'] ?></td><td><?= e($user['full_name']) ?></td><td><?= e($user['email'] ?? $user['phone']) ?></td>
            <td><span class="badge <?= $user['role'] === 'admin' ? 'bg-warning text-dark' : 'bg-secondary' ?>"><?= e(\App\Enums\UserRole::from($user['role'])->label()) ?></span></td>
            <td><?= $user['is_active'] ? '<span class="badge bg-success">Hoạt động</span>' : '<span class="badge bg-danger">Đã khóa</span>' ?></td>
            <td><?= e(date('d/m/Y', strtotime($user['created_at']))) ?></td>
            <td class="text-end text-nowrap"><a class="bh-btn bh-btn--outline bh-btn--sm" href="/admin/users/<?= (int) $user['id'] ?>"><i class="bi bi-eye"></i></a>
                <?php if (!$self) : ?><form class="d-inline" method="post" action="/admin/users/<?= (int) $user['id'] ?>/toggle-active" data-confirm="<?= $user['is_active'] ? 'Vô hiệu hóa tài khoản này?' : 'Kích hoạt lại tài khoản này?' ?>"><?= csrf_field() ?>
                    <button class="bh-btn bh-btn--dark bh-btn--sm" type="submit"><?= $user['is_active'] ? 'Khóa' : 'Mở khóa' ?></button></form><?php endif; ?></td>
        </tr>
    <?php endforeach; ?>
    <?php if ($rows === []) : ?><tr><td colspan="7" class="text-center bh-muted py-4">Không có người dùng.</td></tr><?php endif; ?>
</tbody></table></div>
<div class="mt-3"><?= $paginator->render() ?></div>
