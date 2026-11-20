<?php /** @var array $rows */ /** @var \App\Core\Paginator $paginator */ /** @var array $categories */ /** @var array $counts */
/** @var string $search */ /** @var int $categoryId */ /** @var string $view */
$restock = \App\Repositories\ProductRepository::RESTOCK_AT;
partial('admin-tabs', [
    'param' => 'view', 'current' => $view,
    'tabs' => ['' => 'Tất cả', 'active' => 'Đang bán', 'restock' => 'Cần nhập thêm', 'hidden' => 'Đã ẩn'],
    'counts' => ['' => $counts['all'], 'active' => $counts['active'], 'restock' => $counts['restock'], 'hidden' => $counts['hidden']],
    'alert' => ['restock' => true],
    'keep' => ['q' => $search, 'category' => $categoryId ?: ''],
]);
partial('admin-filter', [
    'search' => $search, 'placeholder' => 'Tìm theo tên hoặc thương hiệu…',
    'createUrl' => '/admin/products/create', 'createLabel' => 'Thêm sản phẩm',
    'hidden' => ['view' => $view],
    'selects' => ['category' => ['Tất cả danh mục', array_column($categories, 'name', 'id'), $categoryId ?: '']],
]); ?>
<div class="adm-card adm-card--flush">
    <table class="table adm-table adm-table--stack">
        <thead><tr><th>Sản phẩm</th><th>Danh mục</th><th>Giá (₫) / Tồn kho</th><th>Trạng thái</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($rows as $row) : $stock = (int) $row['stock_quantity']; ?>
            <tr>
                <td class="adm-main"><div class="adm-cell">
                    <img class="adm-thumb" src="<?= e(img_url($row['image_path'])) ?>" alt="" loading="lazy">
                    <div class="min-w-0"><a class="adm-name" href="/admin/products/<?= (int) $row['id'] ?>/edit"><?= e($row['name']) ?></a>
                        <span class="adm-sub"><?= e($row['brand_name'] ?? 'Không thương hiệu') ?> · <?= e($row['unit']) ?></span></div>
                </div></td>
                <td class="adm-hide-sm" data-label="Danh mục"><?= e($row['category_name']) ?></td>
                <td data-label="Giá / Tồn kho">
                    <form class="adm-quick" method="post" action="/admin/products/<?= (int) $row['id'] ?>/quick"><?= csrf_field() ?>
                        <input class="form-control adm-price" type="number" min="0" step="500" name="price" value="<?= (int) $row['price'] ?>" aria-label="Giá của <?= e($row['name']) ?>">
                        <input class="form-control adm-stock" type="number" min="0" name="stock_quantity" value="<?= $stock ?>" aria-label="Tồn kho của <?= e($row['name']) ?>">
                        <button class="bh-btn bh-btn--outline bh-btn--sm" type="submit" title="Lưu giá và tồn kho"><i class="bi bi-check2"></i></button>
                    </form>
                </td>
                <td data-label="Trạng thái">
                    <?= $row['is_active'] ? '<span class="badge bg-success">Đang bán</span>' : '<span class="badge bg-secondary">Đã ẩn</span>' ?>
                    <?php if ($row['is_active'] && $stock === 0) : ?><span class="badge bg-danger">Hết hàng</span>
                    <?php elseif ($row['is_active'] && $stock <= $restock) : ?><span class="badge bg-warning text-dark">Sắp hết</span><?php endif; ?>
                </td>
                <td class="adm-actions-cell">
                    <a class="bh-btn bh-btn--outline bh-btn--sm adm-icon" href="/admin/products/<?= (int) $row['id'] ?>/edit" title="Sửa" aria-label="Sửa <?= e($row['name']) ?>"><i class="bi bi-pencil"></i></a>
                    <form class="d-inline" method="post" action="/admin/products/<?= (int) $row['id'] ?>/delete" data-confirm="Xóa sản phẩm này? Nếu đã có đơn hàng, sản phẩm sẽ chỉ bị ẩn."><?= csrf_field() ?><button class="bh-btn bh-btn--danger bh-btn--sm adm-icon" type="submit" title="Xóa" aria-label="Xóa <?= e($row['name']) ?>"><i class="bi bi-trash"></i></button></form>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if ($rows === []) : ?><tr><td colspan="5" class="adm-empty">Không có sản phẩm phù hợp.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>
<div class="adm-pager"><?= $paginator->render() ?></div>
