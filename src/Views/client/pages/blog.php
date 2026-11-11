<?php
partial('hero', ['heroTitle' => 'Blog', 'crumbs' => [['Trang chủ', '/'], ['Blog', null]]]);
$posts = [
    ['Cách chọn bột mì cho từng loại bánh', 'Bột đa dụng, bột số 11 hay bột số 8? Mỗi loại có hàm lượng protein khác nhau và phù hợp với một dòng bánh riêng.'],
    ['Bơ lạt và bơ mặn: khi nào dùng loại nào?', 'Bơ lạt giúp bạn kiểm soát vị mặn của bánh, trong khi bơ mặn phù hợp cho một số công thức bánh mì và bánh quy.'],
    ['5 dụng cụ cần có cho người mới học làm bánh', 'Cân điện tử, phới trộn, khuôn, rây bột và thìa đo lường là bộ dụng cụ cơ bản giúp bạn làm bánh chuẩn xác.'],
];
?>
<div class="container py-5">
    <div class="row g-4 row-cols-1 row-cols-md-3">
        <?php foreach ($posts as [$title, $excerpt]) : ?>
            <div class="col"><article class="bh-card"><div class="bh-card__media"><img loading="lazy" src="<?= e(img_url(null)) ?>" alt=""></div>
                <div class="bh-card__body"><h3 class="bh-card__title"><?= e($title) ?></h3><p class="bh-muted"><?= e($excerpt) ?></p></div></article></div>
        <?php endforeach; ?>
    </div>
</div>
