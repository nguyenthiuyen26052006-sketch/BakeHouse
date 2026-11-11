<?php /** @var array $orders */ /** @var \App\Core\Paginator $paginator */
partial('hero', ['heroTitle' => 'Đơn hàng của tôi', 'crumbs' => [['Trang chủ', '/'], ['Đơn hàng', null]]]); ?>
<div class="container py-5">
<?php if ($orders === []) : ?>
    <div class="bh-panel text-center py-5"><p>Bạn chưa có đơn hàng nào.</p><a class="bh-btn bh-btn--primary" href="/products">Mua sắm ngay</a></div>
<?php else : ?>
    <table class="bh-table bh-table--stack">
        <thead><tr><th>Mã đơn</th><th>Ngày đặt</th><th>Sản phẩm</th><th>Tổng tiền</th><th>Thanh toán</th><th>Trạng thái</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($orders as $order) : ?>
            <tr>
                <td data-label="Mã đơn"><strong><?= e($order['order_code']) ?></strong></td>
                <td data-label="Ngày đặt"><?= e(date('d/m/Y H:i', strtotime($order['created_at']))) ?></td>
                <td data-label="Sản phẩm"><?= (int) $order['item_count'] ?></td>
                <td data-label="Tổng tiền" class="bh-price"><?= e(format_vnd((int) $order['total_amount'])) ?></td>
                <td data-label="Thanh toán"><?= $order['payment_status'] === 'paid' ? '<span class="badge bg-success">Đã thanh toán</span>' : '<span class="badge bg-secondary">Chưa thanh toán</span>' ?></td>
                <td data-label="Trạng thái"><?= status_badge($order['status']) ?></td>
                <td><a class="bh-btn bh-btn--outline bh-btn--sm" href="/orders/<?= e($order['order_code']) ?>">Chi tiết</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <div class="mt-4"><?= $paginator->render() ?></div>
<?php endif; ?>
</div>
