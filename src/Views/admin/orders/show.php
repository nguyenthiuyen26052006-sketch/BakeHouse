<?php /** @var array $order */ /** @var ?array $customer */ /** @var array $items */ /** @var array $histories */
$currentStatus = \App\Enums\OrderStatus::from($order['status']);
$next = $currentStatus->next();
$stepLabel = ['processing' => 'Xác nhận đơn', 'shipped' => 'Bắt đầu giao hàng', 'delivered' => 'Hoàn tất đơn'];
$subtotal = array_sum(array_map(static fn (array $i): int => (int) $i['unit_price'] * (int) $i['quantity'], $items)); ?>
<div class="adm-head">
    <a class="bh-btn bh-btn--outline bh-btn--sm" href="/admin/orders"><i class="bi bi-arrow-left"></i> Danh sách đơn</a>
    <div class="d-flex align-items-center gap-2"><span class="bh-muted small">Đặt lúc <?= e(date('d/m/Y H:i', strtotime($order['created_at']))) ?></span><?= status_badge($order['status']) ?></div>
</div>
<div class="row g-3">
    <div class="col-lg-8">
        <section class="adm-card adm-card--flush mb-3">
            <div class="adm-card__head px-3 pt-3 mb-0"><h2>Sản phẩm trong đơn</h2></div>
            <table class="table adm-table adm-table--stack mt-2">
                <thead><tr><th>Sản phẩm</th><th class="adm-num">Đơn giá</th><th class="adm-num">SL</th><th class="adm-num">Thành tiền</th></tr></thead>
                <tbody>
                <?php foreach ($items as $item) : ?>
                    <tr>
                        <td class="adm-main"><div class="adm-cell"><img class="adm-thumb" src="<?= e(img_url($item['image_path'] ?? null)) ?>" alt="" loading="lazy"><span><?= e($item['product_name']) ?></span></div></td>
                        <td class="adm-num" data-label="Đơn giá"><?= e(format_vnd((int) $item['unit_price'])) ?></td>
                        <td class="adm-num" data-label="Số lượng"><?= (int) $item['quantity'] ?></td>
                        <td class="adm-num" data-label="Thành tiền"><?= e(format_vnd((int) $item['unit_price'] * (int) $item['quantity'])) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <div class="px-3 pb-3 pt-2 border-top" style="border-color:var(--bh-color-border)!important">
                <div class="d-flex justify-content-between"><span class="bh-muted">Tạm tính</span><span><?= e(format_vnd($subtotal)) ?></span></div>
                <div class="d-flex justify-content-between"><span class="bh-muted">Phí vận chuyển</span><span><?= e(format_vnd((int) $order['shipping_fee'])) ?></span></div>
                <div class="d-flex justify-content-between fs-5 mt-1"><strong>Tổng đơn</strong><strong class="bh-price"><?= e(format_vnd((int) $order['total_amount'])) ?></strong></div>
            </div>
        </section>
        <section class="adm-card">
            <div class="adm-card__head"><h2>Lịch sử xử lý</h2></div>
            <ul class="list-unstyled mb-0 small">
            <?php foreach ($histories as $h) : ?>
                <li class="mb-2"><i class="bi bi-clock-history me-1 bh-muted"></i><?= e(date('d/m/Y H:i', strtotime($h['created_at']))) ?> — <?= $h['from_status'] ? status_badge($h['from_status']) . ' → ' : 'Tạo đơn: ' ?><?= status_badge($h['to_status']) ?> <span class="bh-muted">bởi <?= e($h['changed_by_name'] ?? 'hệ thống') ?></span></li>
            <?php endforeach; ?></ul>
        </section>
    </div>
    <div class="col-lg-4">
        <section class="adm-card mb-3">
            <div class="adm-card__head"><h2>Giao hàng</h2></div>
            <div class="fw-semibold"><?= e($order['recipient_name']) ?></div>
            <div><i class="bi bi-telephone bh-muted"></i> <?= e($order['shipping_phone']) ?></div>
            <div><i class="bi bi-geo-alt bh-muted"></i> <?= e($order['shipping_address']) ?></div>
            <?php if ($order['note']) : ?><div class="mt-2 p-2 rounded" style="background:#faf6ef"><i class="bi bi-chat-left-text bh-muted"></i> <?= e($order['note']) ?></div><?php endif; ?>
            <?php if ($next === null) : ?><p class="bh-muted mt-3 mb-0"><i class="bi bi-check-circle text-success"></i> Đơn hàng đã hoàn tất.</p>
            <?php else : ?>
                <form class="mt-3" method="post" action="/admin/orders/<?= (int) $order['id'] ?>/status"><?= csrf_field() ?><input type="hidden" name="status" value="<?= e($next->value) ?>">
                    <button class="bh-btn bh-btn--dark w-100" type="submit"><?= e($stepLabel[$next->value]) ?> <i class="bi bi-arrow-right-short"></i></button>
                    <div class="form-text">Đơn chuyển lần lượt từng bước, không nhảy cóc hoặc quay lại.</div>
                </form>
            <?php endif; ?>
        </section>
        <section class="adm-card">
            <div class="adm-card__head"><h2>Thanh toán</h2></div>
            <div><?= e(\App\Enums\PaymentMethod::from($order['payment_method'])->label()) ?></div>
            <div class="mb-2"><?= $order['payment_status'] === 'paid' ? '<span class="badge bg-success">Đã thanh toán</span>' : '<span class="badge bg-secondary">Chưa thanh toán</span>' ?></div>
            <?php if ($order['payment_method'] === 'vietqr' && $order['payment_status'] === 'unpaid') : ?>
                <form method="post" action="/admin/orders/<?= (int) $order['id'] ?>/confirm-payment" data-confirm="Xác nhận đã nhận đủ tiền chuyển khoản cho đơn này?"><?= csrf_field() ?>
                    <button class="bh-btn bh-btn--primary w-100" type="submit"><i class="bi bi-cash-coin"></i> Xác nhận đã nhận tiền</button></form>
            <?php endif; ?>
        </section>
    </div>
</div>
