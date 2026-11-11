<?php /** @var array $product */ /** @var array $images */ /** @var array $tags */ /** @var bool $canModify */ ?>
<div class="bh-panel">
    <div class="row g-4">
        <div class="col-md-4"><img class="img-fluid rounded-3" src="<?= e(img_url($product['image_path'])) ?>" alt="">
            <div class="d-flex gap-2 flex-wrap mt-2"><?php foreach ($images as $image) : ?><img class="bh-thumb" src="<?= e(img_url($image['image_path'])) ?>" alt=""><?php endforeach; ?></div></div>
        <div class="col-md-8">
            <h3><?= e($product['name']) ?></h3>
            <p class="bh-muted"><?= e($product['category_name']) ?> · <?= e($product['brand_name'] ?? 'Không thương hiệu') ?></p>
            <p><strong><?= e(format_vnd((int) $product['price'])) ?></strong> / <?= e($product['unit']) ?> · Tồn kho: <?= (int) $product['stock_quantity'] ?></p>
            <p><?= nl2br(e($product['description'] ?? '')) ?></p>
            <?php foreach ($tags as $tag) : ?><span class="bh-tag">#<?= e($tag['name']) ?></span><?php endforeach; ?>
            <div class="mt-3">
                <?php if ($canModify) : ?><a class="bh-btn bh-btn--primary" href="/admin/products/<?= (int) $product['id'] ?>/edit">Chỉnh sửa</a>
                <?php else : ?><span class="badge bg-secondary">Chỉ đọc — sản phẩm của quản trị viên khác</span><?php endif; ?>
                <a class="bh-btn bh-btn--outline ms-2" href="/admin/products">Quay lại</a>
            </div>
        </div>
    </div>
</div>
