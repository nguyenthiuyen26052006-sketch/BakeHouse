<?php /** @var array $rows */ /** @var \App\Core\Paginator $paginator */ /** @var array $categories */
/** @var string $search */ /** @var int $categoryId */ /** @var string $status */ /** @var string $scope */ /** @var int $adminId */
partial('admin-filter', [
    'search' => $search, 'placeholder' => 'Tìm theo tên hoặc thương hiệu…', 'createUrl' => '/admin/products/create',
    'selects' => [
        'category' => ['Tất cả danh mục', array_column($categories, 'name', 'id'), $categoryId ?: ''],
        'status' => ['Mọi trạng thái', ['active' => 'Đang bán', 'hidden' => 'Đã ẩn'], $status],
        'scope' => ['Phạm vi', ['mine' => 'Của tôi', 'all' => 'Tất cả (chỉ đọc)'], $scope],
    ],
]); ?>
<div class="bh-panel p-0 table-responsive">
    <table class="table table-hover align-middle mb-0 bh-table">
        <thead><tr><th>Ảnh</th><th>Tên</th><th>Danh mục</th><th>Giá / Tồn kho</th><th>Chủ sở hữu</th><th>Trạng thái</th><th class="text-end">Thao tác</th></tr></thead>
        <tbody>
        <?php foreach ($rows as $row) : $mine = (int) $row['created_by'] === $adminId; ?>
            <tr>
                <td><img class="bh-thumb" src="<?= e(img_url($row['image_path'])) ?>" alt=""></td>
                <td><a class="fw-semibold text-decoration-none" href="/admin/products/<?= (int) $row['id'] ?>"><?= e($row['name']) ?></a><div class="small bh-muted"><?= e($row['brand_name'] ?? '—') ?></div></td>
                <td><?= e($row['category_name']) ?></td>
                <td style="min-width:230px">
                    <?php if ($mine) : ?>
                        <form class="d-flex gap-1" method="post" action="/admin/products/<?= (int) $row['id'] ?>/quick"><?= csrf_field() ?>
                            <input class="form-control form-control-sm" style="min-height:38px" type="number" min="0" name="price" value="<?= (int) $row['price'] ?>" aria-label="Giá">
                            <input class="form-control form-control-sm" style="min-height:38px;max-width:80px" type="number" min="0" name="stock_quantity" value="<?= (int) $row['stock_quantity'] ?>" aria-label="Tồn kho">
                            <button class="bh-btn bh-btn--outline bh-btn--sm" type="submit" title="Lưu giá / tồn kho"><i class="bi bi-check2"></i></button>
                        </form>
                    <?php else : ?><?= e(format_vnd((int) $row['price'])) ?> · <?= (int) $row['stock_quantity'] ?><?php endif; ?>
                </td>
                <td><?= e($row['owner_name']) ?><?= $mine ? ' <span class="badge bg-warning text-dark">Tôi</span>' : '' ?></td>
                <td><?= $row['is_active'] ? '<span class="badge bg-success">Đang bán</span>' : '<span class="badge bg-secondary">Đã ẩn</span>' ?></td>
                <td class="text-end text-nowrap">
                    <?php if ($mine) : ?>
                        <a class="bh-btn bh-btn--outline bh-btn--sm" href="/admin/products/<?= (int) $row['id'] ?>/edit"><i class="bi bi-pencil"></i></a>
                        <form class="d-inline" method="post" action="/admin/products/<?= (int) $row['id'] ?>/delete" data-confirm="Xóa sản phẩm này? Nếu đã có đơn hàng, sản phẩm sẽ chỉ bị ẩn."><?= csrf_field() ?><button class="bh-btn bh-btn--dark bh-btn--sm" type="submit"><i class="bi bi-trash"></i></button></form>
                    <?php else : ?><a class="bh-btn bh-btn--outline bh-btn--sm" href="/admin/products/<?= (int) $row['id'] ?>"><i class="bi bi-eye"></i></a><?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if ($rows === []) : ?><tr><td colspan="7" class="text-center bh-muted py-4">Không có sản phẩm.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>
<div class="mt-3"><?= $paginator->render() ?></div>
