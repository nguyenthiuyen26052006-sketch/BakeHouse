<?php /** @var array $cart */ /** @var int $shippingFee */
partial('hero', ['heroTitle' => 'Giỏ hàng', 'crumbs' => [['Trang chủ', '/'], ['Giỏ hàng', null]]]); ?>
<div class="container py-5">
<?php if ($cart['items'] === []) : ?>
    <div class="bh-panel text-center py-5"><i class="bi bi-cart-x fs-1 bh-muted"></i><p class="mt-3">Giỏ hàng của bạn đang trống.</p><a class="bh-btn bh-btn--primary" href="/products">Tiếp tục mua sắm</a></div>
<?php else : ?>
    <div class="row g-4">
        <div class="col-lg-8">
            <table class="bh-table bh-table--stack">
                <thead><tr><th>Sản phẩm</th><th>Đơn giá</th><th>Số lượng</th><th>Thành tiền</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($cart['items'] as $item) : ?>
                    <tr data-cart-row="<?= (int) $item['product_id'] ?>">
                        <td data-label="Sản phẩm"><div class="d-flex align-items-center gap-3 text-start">
                            <img class="bh-thumb" src="<?= e(img_url($item['image_path'])) ?>" alt="">
                            <div><a class="fw-semibold text-decoration-none" href="/products/<?= e($item['slug']) ?>"><?= e($item['name']) ?></a>
                            <?php if (!$item['available']) : ?><div class="small text-danger">Hết hàng / ngừng bán</div><?php endif; ?></div></div></td>
                        <td data-label="Đơn giá"><?= e(format_vnd((int) $item['price'])) ?></td>
                        <td data-label="Số lượng">
                            <div class="bh-qty">
                                <button type="button" data-qty-step="-1" aria-label="Giảm">−</button>
                                <input type="number" data-cart-qty min="1" max="<?= (int) $item['stock_quantity'] ?>" value="<?= (int) $item['quantity'] ?>" aria-label="Số lượng">
                                <button type="button" data-qty-step="1" aria-label="Tăng">+</button>
                            </div></td>
                        <td data-label="Thành tiền" class="bh-price" data-line-total><?= e(format_vnd((int) $item['line_total'])) ?></td>
                        <td><button type="button" class="bh-btn bh-btn--outline bh-btn--sm" data-cart-remove aria-label="Xóa"><i class="bi bi-trash"></i></button></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="col-lg-4">
            <div class="bh-panel">
                <h5>Tóm tắt đơn hàng</h5>
                <div class="d-flex justify-content-between my-2"><span>Tạm tính</span><strong data-cart-subtotal><?= e(format_vnd($cart['subtotal'])) ?></strong></div>
                <div class="d-flex justify-content-between my-2"><span>Phí vận chuyển</span><span><?= e(format_vnd($shippingFee)) ?></span></div>
                <hr>
                <div class="d-flex justify-content-between mb-3 fs-5"><span>Tổng cộng</span><strong class="bh-price" data-cart-total><?= e(format_vnd($cart['subtotal'] + $shippingFee)) ?></strong></div>
                <a class="bh-btn bh-btn--primary w-100" href="/checkout">Tiến hành thanh toán</a>
                <a class="bh-btn bh-btn--outline w-100 mt-2" href="/products">Tiếp tục mua sắm</a>
            </div>
        </div>
    </div>
<?php endif; ?>
</div>
