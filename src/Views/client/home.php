<?php
/** @var array $banners */ /** @var array $categories */ /** @var array $featured */
/** @var array $newest */ /** @var array $bestSelling */ /** @var array $topRated */
$sections = [
    ['Sản phẩm nổi bật', $featured, '/products'],
    ['Mới cập nhật', $newest, '/products?sort=newest'],
    ['Bán chạy', $bestSelling, '/products?sort=best_selling'],
    ['Đánh giá cao', $topRated, '/products?sort=top_rated'],
];
?>
<?php if ($banners !== []) : ?>
<div id="bhBanners" class="carousel slide" data-bs-ride="carousel">
    <div class="carousel-inner">
        <?php foreach ($banners as $i => $banner) : ?>
            <div class="carousel-item<?= $i === 0 ? ' active' : '' ?>">
                <div class="bh-banner" style="background-image:url('<?= e(img_url($banner['image_path'], 'assets/img/banner-placeholder.svg')) ?>')">
                    <div class="container">
                        <h2 class="bh-banner__title"><?= e($banner['title']) ?></h2>
                        <?php if (!empty($banner['link_url'])) : ?>
                            <a class="bh-btn bh-btn--primary mt-3" href="<?= e($banner['link_url']) ?>">Khám phá ngay</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php if (count($banners) > 1) : ?>
        <button class="carousel-control-prev" type="button" data-bs-target="#bhBanners" data-bs-slide="prev"><span class="carousel-control-prev-icon"></span><span class="visually-hidden">Trước</span></button>
        <button class="carousel-control-next" type="button" data-bs-target="#bhBanners" data-bs-slide="next"><span class="carousel-control-next-icon"></span><span class="visually-hidden">Sau</span></button>
    <?php endif; ?>
</div>
<?php endif; ?>

<section class="bh-section">
    <div class="container">
        <div class="bh-section__head"><h2 class="bh-section__title">Danh mục</h2></div>
        <div class="row g-3 row-cols-2 row-cols-md-3 row-cols-lg-5">
            <?php foreach ($categories as $category) : ?>
                <div class="col">
                    <a class="bh-cat-tile" href="/products?category=<?= e($category['slug']) ?>">
                        <i class="bi bi-basket2"></i>
                        <div class="fw-semibold mt-2"><?= e($category['name']) ?></div>
                        <small class="bh-muted"><?= (int) $category['product_count'] ?> sản phẩm</small>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php foreach ($sections as [$heading, $items, $link]) : ?>
    <?php if ($items !== []) : ?>
    <section class="bh-section pt-0">
        <div class="container">
            <div class="bh-section__head">
                <h2 class="bh-section__title"><?= e($heading) ?></h2>
                <a class="bh-btn bh-btn--outline bh-btn--sm" href="<?= e($link) ?>">Xem tất cả</a>
            </div>
            <div class="row g-4 row-cols-1 row-cols-sm-2 row-cols-lg-4">
                <?php foreach ($items as $product) : ?>
                    <div class="col"><?php partial('product-card', ['product' => $product]); ?></div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>
<?php endforeach; ?>
