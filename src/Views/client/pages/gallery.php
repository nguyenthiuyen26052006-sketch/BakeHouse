<?php
partial('hero', ['heroTitle' => 'Thư viện', 'crumbs' => [['Trang chủ', '/'], ['Thư viện', null]]]);
$images = [
    ['/assets/img/gallery-bread.svg', 'Bánh mì nướng giòn'],
    ['/assets/img/gallery-cupcake.svg', 'Cupcake phủ kem'],
    ['/assets/img/gallery-cookies.svg', 'Bánh quy bơ'],
    ['/assets/img/gallery-croissant.svg', 'Croissant bơ Pháp'],
    ['/assets/img/gallery-cake.svg', 'Bánh kem nhiều tầng'],
    ['/assets/img/gallery-whisk.svg', 'Góc bếp trộn bột'],
];
?>
<div class="container py-5">
    <div class="row g-3 row-cols-2 row-cols-md-3">
        <?php foreach ($images as [$src, $caption]) : ?>
            <div class="col"><div class="bh-gallery__main"><img loading="lazy" src="<?= e($src) ?>" alt="<?= e($caption) ?>"></div></div>
        <?php endforeach; ?>
    </div>
    <p class="text-center bh-muted mt-4">Hình ảnh sản phẩm và thành phẩm của khách hàng sẽ được cập nhật tại đây.</p>
</div>
