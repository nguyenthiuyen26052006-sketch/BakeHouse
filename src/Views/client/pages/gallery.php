<?php partial('hero', ['heroTitle' => 'Thư viện', 'crumbs' => [['Trang chủ', '/'], ['Thư viện', null]]]); ?>
<div class="container py-5">
    <div class="row g-3 row-cols-2 row-cols-md-3">
        <?php for ($i = 1; $i <= 6; $i++) : ?>
            <div class="col"><div class="bh-gallery__main"><img loading="lazy" src="<?= e(img_url(null)) ?>" alt="Ảnh thư viện <?= $i ?>"></div></div>
        <?php endfor; ?>
    </div>
    <p class="text-center bh-muted mt-4">Hình ảnh sản phẩm và thành phẩm của khách hàng sẽ được cập nhật tại đây.</p>
</div>
