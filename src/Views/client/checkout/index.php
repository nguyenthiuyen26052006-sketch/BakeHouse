<?php /** @var array $items */ /** @var int $subtotal */ /** @var int $shippingFee */ /** @var string $mode */ /** @var ?array $buyNow */ /** @var array $user */
partial('hero', ['heroTitle' => 'Thanh toán', 'crumbs' => [['Trang chủ', '/'], ['Giỏ hàng', '/cart'], ['Thanh toán', null]]]); ?>
<div class="container py-5">
    <form method="post" action="/checkout" novalidate>
        <?= csrf_field() ?>
        <input type="hidden" name="mode" value="<?= e($mode) ?>">
        <?php if ($buyNow) : ?><input type="hidden" name="product_id" value="<?= (int) $buyNow['product_id'] ?>"><input type="hidden" name="qty" value="<?= (int) $buyNow['qty'] ?>"><?php endif; ?>
        <div class="row g-4">
            <div class="col-lg-7">
                <div class="bh-panel">
                    <h5 class="mb-3">Thông tin giao hàng</h5>
                    <div class="mb-3"><label class="form-label" for="recipient_name">Họ tên người nhận</label>
                        <input class="form-control" id="recipient_name" name="recipient_name" value="<?= old('recipient_name', (string) $user['full_name']) ?>" required><?= form_error('recipient_name') ?></div>
                    <div class="mb-3"><label class="form-label" for="shipping_phone">Số điện thoại</label>
                        <input class="form-control" id="shipping_phone" name="shipping_phone" inputmode="tel" value="<?= old('shipping_phone', (string) ($user['shipping_phone'] ?? '')) ?>" required><?= form_error('shipping_phone') ?></div>
                    <div class="mb-3"><label class="form-label" for="shipping_address">Địa chỉ</label>
                        <textarea class="form-control" id="shipping_address" name="shipping_address" rows="2" required><?= old('shipping_address', (string) ($user['shipping_address'] ?? '')) ?></textarea><?= form_error('shipping_address') ?></div>
                    <div class="mb-3"><label class="form-label" for="note">Ghi chú</label>
                        <textarea class="form-control" id="note" name="note" rows="2"><?= old('note') ?></textarea></div>
                    <h5 class="mb-3 mt-4">Phương thức thanh toán</h5>
                    <?php foreach (\App\Enums\PaymentMethod::cases() as $i => $method) : ?>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="payment_method" id="pm_<?= e($method->value) ?>" value="<?= e($method->value) ?>"<?= $i === 0 ? ' checked' : '' ?>>
                            <label class="form-check-label" for="pm_<?= e($method->value) ?>"><?= e($method->label()) ?></label>
                        </div>
                    <?php endforeach; ?>
                    <?= form_error('payment_method') ?>
                    <div class="form-text">Với VietQR, mã QR đúng số tiền và nội dung chuyển khoản sẽ hiển thị sau khi đặt hàng.</div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="bh-panel">
                    <h5 class="mb-3">Đơn hàng của bạn</h5>
                    <?php foreach ($items as $item) : ?>
                        <div class="d-flex justify-content-between gap-3 mb-2"><span><?= e($item['name']) ?> × <?= (int) $item['quantity'] ?></span><span><?= e(format_vnd((int) $item['line_total'])) ?></span></div>
                    <?php endforeach; ?>
                    <hr>
                    <div class="d-flex justify-content-between mb-2"><span>Tạm tính</span><span><?= e(format_vnd($subtotal)) ?></span></div>
                    <div class="d-flex justify-content-between mb-2"><span>Phí vận chuyển</span><span><?= e(format_vnd($shippingFee)) ?></span></div>
                    <div class="d-flex justify-content-between fs-5 mb-3"><span>Tổng cộng</span><strong class="bh-price"><?= e(format_vnd($subtotal + $shippingFee)) ?></strong></div>
                    <button class="bh-btn bh-btn--primary w-100" type="submit">Đặt hàng</button>
                </div>
            </div>
        </div>
    </form>
</div>
