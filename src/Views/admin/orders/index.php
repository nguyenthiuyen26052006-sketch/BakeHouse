<?php /** @var array $rows */ /** @var \App\Core\Paginator $paginator */ /** @var string $search */ /** @var string $status */
$statusOptions = ['in_progress' => 'Chưa hoàn tất']; foreach (\App\Enums\OrderStatus::cases() as $s) { $statusOptions[$s->value] = $s->label(); }
partial('admin-filter', ['search' => $search, 'placeholder' => 'Tìm theo mã đơn, tên, SĐT…', 'selects' => ['status' => ['Mọi trạng thái', $statusOptions, $status]]]); ?>
<div class="bh-panel p-0 table-responsive"><table class="table table-hover align-middle mb-0 bh-table">
    <thead><tr><th>Mã đơn</th><th>Khách</th><th>Ngày đặt</th><th>Tổng đơn</th><th>Thanh toán</th><th>Trạng thái</th><th></th></tr></thead><tbody>
    <?php foreach ($rows as $order) : ?>
        <tr><td><strong><?= e($order['order_code']) ?></strong></td><td><?= e($order['recipient_name']) ?><div class="small bh-muted"><?= e($order['shipping_phone']) ?></div></td>
            <td><?= e(date('d/m/Y H:i', strtotime($order['created_at']))) ?></td><td><?= e(format_vnd((int) $order['total_amount'])) ?></td>
            <td><?= strtoupper(e($order['payment_method'])) ?> · <?= $order['payment_status'] === 'paid' ? '<span class="badge bg-success">Đã thu</span>' : '<span class="badge bg-secondary">Chưa thu</span>' ?></td>
            <td><?= status_badge($order['status']) ?></td><td><a class="bh-btn bh-btn--outline bh-btn--sm" href="/admin/orders/<?= (int) $order['id'] ?>">Chi tiết</a></td></tr>
    <?php endforeach; ?>
    <?php if ($rows === []) : ?><tr><td colspan="7" class="text-center bh-muted py-4">Không có đơn hàng.</td></tr><?php endif; ?>
</tbody></table></div>
<div class="mt-3"><?= $paginator->render() ?></div>
