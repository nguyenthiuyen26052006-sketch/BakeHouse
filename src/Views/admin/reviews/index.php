<?php /** @var array $rows */ /** @var \App\Core\Paginator $paginator */ /** @var string $search */ /** @var string $visibility */
partial('admin-filter', ['search' => $search, 'placeholder' => 'Tìm theo sản phẩm, nội dung, email…', 'selects' => ['visibility' => ['Tất cả', ['visible' => 'Đang hiển thị', 'hidden' => 'Đã ẩn'], $visibility]]]); ?>
<div class="bh-panel p-0 table-responsive"><table class="table table-hover align-middle mb-0 bh-table">
    <thead><tr><th>Sản phẩm</th><th>Người đánh giá</th><th>Sao</th><th>Nội dung</th><th>Ngày</th><th>Trạng thái</th><th class="text-end">Thao tác</th></tr></thead><tbody>
    <?php foreach ($rows as $r) : ?>
        <tr><td><a href="/products/<?= e($r['product_slug']) ?>" target="_blank"><?= e($r['product_name']) ?></a></td><td><?= e($r['author_name']) ?></td><td><?= render_stars((float) $r['rating']) ?></td>
            <td style="max-width:300px"><?= e($r['comment'] ?? '') ?></td><td><?= e(date('d/m/Y', strtotime($r['created_at']))) ?></td>
            <td><?= $r['is_hidden'] ? '<span class="badge bg-secondary">Đã ẩn</span>' : '<span class="badge bg-success">Hiển thị</span>' ?></td>
            <td class="text-end text-nowrap">
                <form class="d-inline" method="post" action="/admin/reviews/<?= (int) $r['id'] ?>/toggle"><?= csrf_field() ?><button class="bh-btn bh-btn--outline bh-btn--sm" type="submit"><?= $r['is_hidden'] ? 'Hiện' : 'Ẩn' ?></button></form>
                <form class="d-inline" method="post" action="/admin/reviews/<?= (int) $r['id'] ?>/delete" data-confirm="Xóa vĩnh viễn đánh giá này?"><?= csrf_field() ?><button class="bh-btn bh-btn--dark bh-btn--sm" type="submit"><i class="bi bi-trash"></i></button></form></td></tr>
    <?php endforeach; ?>
    <?php if ($rows === []) : ?><tr><td colspan="7" class="text-center bh-muted py-4">Không có đánh giá.</td></tr><?php endif; ?>
</tbody></table></div>
<div class="mt-3"><?= $paginator->render() ?></div>
