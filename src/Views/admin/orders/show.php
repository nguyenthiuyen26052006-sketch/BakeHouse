<?php /** @var array $order */ /** @var ?array $customer */ /** @var array $items */ /** @var array $histories */
$currentStatus = \App\Enums\OrderStatus::from($order['status']);
$nextStatus = $currentStatus->next(); ?>
<div class="row g-4">
    <div class="col-lg-8">
        <div class="bh-panel mb-4">
            <div class="d-flex justify-content-between flex-wrap gap-2 mb-3"><h5 class="mb-0">Sản phẩm trong đơn</h5><?= status_badge($order['status']) ?></div>
            <div class="table-responsive"><table class="table align-middle bh-table"><thead><tr><th>Sản phẩm</th><th>Đơn giá</th><th>SL</th></tr></thead><tbody>
            <?php foreach ($items as $item) : ?>
                <tr><td><?= e($item['product_name']) ?></td>
                    <td><?= e(format_vnd((int) $item['unit_price'])) ?></td><td><?= (int) $item['quantity'] ?></td></tr>
            <?php endforeach; ?></tbody></table></div>
            <div class="d-flex justify-content-between"><span>Phí vận chuyển</span><span><?= e(format_vnd((int) $order['shipping_fee'])) ?></span></div>
            <div class="d-flex justify-content-between fs-5"><span>Tổng đơn</span><strong class="bh-price"><?= e(format_vnd((int) $order['total_amount'])) ?></strong></div>
        </div>
        <div class="bh-panel"><h5>Lịch sử xử lý</h5>
            <ul class="list-unstyled mb-0">
            <?php foreach ($histories as $h) : ?>
                <li class="mb-2"><i class="bi bi-clock-history me-2"></i><?= e(date('d/m/Y H:i', strtotime($h['created_at']))) ?> — <?= $h['from_status'] ? status_badge($h['from_status']) . ' → ' : 'Tạo đơn: ' ?><?= status_badge($h['to_status']) ?> <small class="bh-muted">bởi <?= e($h['changed_by_name'] ?? 'hệ thống') ?></small></li>
            <?php endforeach; ?></ul></div>
    </div>
    <div class="col-lg-4">
        <div class="bh-panel mb-4"><h5>Khách hàng</h5>
            <div class="fw-semibold"><?= e($order['recipient_name']) ?></div><div><?= e($order['shipping_phone']) ?></div><div><?= e($order['shipping_address']) ?></div>
            <?php if ($customer) : ?><div class="small bh-muted mt-1">Tài khoản: <?= e($customer['email'] ?? $customer['phone']) ?></div><?php endif; ?>
            <?php if ($order['note']) : ?><div class="mt-2 bh-muted">Ghi chú: <?= e($order['note']) ?></div><?php endif; ?></div>
        <div class="bh-panel mb-4"><h5>Thanh toán</h5>
            <div><?= e(\App\Enums\PaymentMethod::from($order['payment_method'])->label()) ?></div>
            <div class="mb-2"><?= $order['payment_status'] === 'paid' ? '<span class="badge bg-success">Đã thanh toán</span>' : '<span class="badge bg-secondary">Chưa thanh toán</span>' ?></div>
            <?php if ($order['payment_method'] === 'vietqr' && $order['payment_status'] === 'unpaid') : ?>
                <form method="post" action="/admin/orders/<?= (int) $order['id'] ?>/confirm-payment" data-confirm="Xác nhận đã nhận đủ tiền chuyển khoản cho đơn này?"><?= csrf_field() ?>
                    <button class="bh-btn bh-btn--primary w-100" type="submit"><i class="bi bi-cash-coin"></i> Xác nhận đã nhận tiền</button></form>
            <?php endif; ?></div>
        <div class="bh-panel"><h5>Cập nhật trạng thái</h5>
            <?php if ($nextStatus === null) : ?><p class="bh-muted mb-0">Đơn hàng đã hoàn tất.</p>
            <?php else : ?>
                <form method="post" action="/admin/orders/<?= (int) $order['id'] ?>/status"><?= csrf_field() ?>
                    <select class="form-select mb-3" name="status">
                        <option value="<?= e($nextStatus->value) ?>"><?= e($nextStatus->label()) ?></option>
                    </select>
                    <button class="bh-btn bh-btn--dark w-100" type="submit">Cập nhật</button>
                    <div class="form-text">Chỉ được chuyển sang trạng thái kế tiếp liền kề, không được nhảy cóc hoặc quay lại.</div>
                </form>
            <?php endif; ?></div>
        <a class="bh-btn bh-btn--outline mt-3" href="/admin/orders">Quay lại</a>
    </div>
</div>
