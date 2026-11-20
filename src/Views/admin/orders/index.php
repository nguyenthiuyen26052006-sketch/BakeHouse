<?php /** @var array $rows */ /** @var \App\Core\Paginator $paginator */ /** @var array $counts */ /** @var string $search */ /** @var string $status */
$tabs = ['' => 'Tất cả'];
$tabCounts = ['' => $counts['all']];
foreach (\App\Enums\OrderStatus::cases() as $s) { $tabs[$s->value] = $s->label(); $tabCounts[$s->value] = $counts[$s->value]; }
// Action label for moving an order to its next step
$stepLabel = ['processing' => 'Xác nhận', 'shipped' => 'Giao hàng', 'delivered' => 'Hoàn tất'];
partial('admin-tabs', ['param' => 'status', 'current' => $status, 'tabs' => $tabs, 'counts' => $tabCounts, 'alert' => ['pending' => true], 'keep' => ['q' => $search]]);
partial('admin-filter', ['search' => $search, 'placeholder' => 'Tìm theo mã đơn, tên khách, SĐT…', 'hidden' => ['status' => $status]]); ?>
<div class="adm-card adm-card--flush">
    <table class="table adm-table adm-table--stack">
        <thead><tr><th>Mã đơn</th><th>Khách hàng</th><th>Ngày đặt</th><th class="adm-num">Tổng đơn</th><th>Thanh toán</th><th>Trạng thái</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($rows as $order) :
            $current = \App\Enums\OrderStatus::from($order['status']);
            $next = $current->next();
            $needsPayment = $order['payment_method'] === 'vietqr' && $order['payment_status'] === 'unpaid'; ?>
            <tr>
                <td class="adm-main"><a class="adm-name" href="/admin/orders/<?= (int) $order['id'] ?>"><?= e($order['order_code']) ?></a><span class="adm-sub"><?= (int) $order['item_count'] ?> sản phẩm</span></td>
                <td data-label="Khách hàng"><?= e($order['recipient_name']) ?><span class="adm-sub"><?= e($order['shipping_phone']) ?></span></td>
                <td data-label="Ngày đặt"><?= e(date('d/m/Y H:i', strtotime($order['created_at']))) ?></td>
                <td class="adm-num" data-label="Tổng đơn"><strong><?= e(format_vnd((int) $order['total_amount'])) ?></strong></td>
                <td data-label="Thanh toán"><?= $order['payment_method'] === 'vietqr' ? 'VietQR' : 'COD' ?>
                    <?= $order['payment_status'] === 'paid' ? '<span class="badge bg-success">Đã thu</span>' : '<span class="badge bg-secondary">Chưa thu</span>' ?></td>
                <td data-label="Trạng thái"><?= status_badge($order['status']) ?></td>
                <td class="adm-actions-cell">
                    <?php if ($needsPayment) : ?>
                        <form class="d-inline" method="post" action="/admin/orders/<?= (int) $order['id'] ?>/confirm-payment" data-confirm="Xác nhận đã nhận đủ tiền chuyển khoản cho đơn <?= e($order['order_code']) ?>?"><?= csrf_field() ?><input type="hidden" name="from" value="list">
                            <button class="bh-btn bh-btn--outline bh-btn--sm" type="submit" title="Xác nhận đã nhận tiền chuyển khoản"><i class="bi bi-cash-coin"></i> Đã nhận tiền</button></form>
                    <?php endif; ?>
                    <?php if ($next !== null) : ?>
                        <form class="d-inline" method="post" action="/admin/orders/<?= (int) $order['id'] ?>/status"><?= csrf_field() ?><input type="hidden" name="from" value="list"><input type="hidden" name="status" value="<?= e($next->value) ?>">
                            <button class="bh-btn bh-btn--dark bh-btn--sm" type="submit"><?= e($stepLabel[$next->value]) ?> <i class="bi bi-arrow-right-short"></i></button></form>
                    <?php endif; ?>
                    <a class="bh-btn bh-btn--outline bh-btn--sm adm-icon" href="/admin/orders/<?= (int) $order['id'] ?>" title="Chi tiết" aria-label="Chi tiết đơn <?= e($order['order_code']) ?>"><i class="bi bi-eye"></i></a>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if ($rows === []) : ?><tr><td colspan="7" class="adm-empty">Không có đơn hàng phù hợp.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>
<div class="adm-pager"><?= $paginator->render() ?></div>
