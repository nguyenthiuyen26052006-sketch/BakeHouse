<?php /** @var array<string, mixed> $product */
$inStock = (int) $product['stock_quantity'] > 0; ?>
<article class="bh-card">
    <a class="bh-card__media" href="/products/<?= e($product['slug']) ?>">
        <img loading="lazy" src="<?= e(img_url($product['image_path'])) ?>" alt="<?= e($product['name']) ?>">
        <?php if (!empty($product['is_best'])) : ?><span class="bh-badge bh-badge--best">Best Seller</span><?php endif; ?>
        <?php if (!empty($product['is_new'])) : ?><span class="bh-badge bh-badge--new">New</span><?php endif; ?>
    </a>
    <div class="bh-card__body">
        <div class="bh-card__category"><?= e($product['category_name']) ?></div>
        <h3 class="bh-card__title"><a href="/products/<?= e($product['slug']) ?>"><?= e($product['name']) ?></a></h3>
        <div class="bh-card__meta">
            <?php if (!empty($product['brand_name'])) : ?><?= e($product['brand_name']) ?> · <?php endif; ?>
            <?= render_stars((float) $product['rating_avg']) ?> <span>(<?= (int) $product['rating_count'] ?>)</span>
        </div>
        <div class="bh-card__footer">
            <span class="bh-price"><?= e(format_vnd((int) $product['price'])) ?></span>
            <?php if ($inStock) : ?>
                <button type="button" class="bh-btn bh-btn--primary bh-btn--sm" data-add-to-cart="<?= (int) $product['id'] ?>">
                    <i class="bi bi-cart-plus"></i> Thêm vào giỏ
                </button>
            <?php else : ?>
                <span class="badge text-bg-secondary">Hết hàng</span>
            <?php endif; ?>
        </div>
    </div>
</article>
