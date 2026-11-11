<?php
/** @var array $products */ /** @var array $categories */ /** @var \App\Core\Paginator $paginator */
/** @var array $filters */ /** @var string $sort */ /** @var int $sliderMax */
$sortOptions = ['newest' => 'Mới nhất', 'price_asc' => 'Giá tăng dần', 'price_desc' => 'Giá giảm dần', 'best_selling' => 'Bán chạy', 'top_rated' => 'Đánh giá cao'];
$currentMax = $filters['max_price'] > 0 ? $filters['max_price'] : $sliderMax;
partial('hero', ['heroTitle' => 'Sản Phẩm', 'crumbs' => [['Trang chủ', '/'], ['Sản phẩm', null]]]);
?>
<div class="container py-5">
    <button class="bh-btn bh-btn--outline w-100 mb-4 d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#bhFilters"><i class="bi bi-funnel"></i> Bộ lọc</button>
    <div class="row g-4">
        <aside class="col-lg-3">
            <div class="offcanvas-lg offcanvas-start" tabindex="-1" id="bhFilters">
                <div class="offcanvas-header d-lg-none"><h5>Bộ lọc</h5><button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#bhFilters"></button></div>
                <div class="offcanvas-body d-block p-3 p-lg-0">
                    <form action="/products" method="get" class="mb-4">
                        <?php if ($filters['category'] !== '') : ?><input type="hidden" name="category" value="<?= e($filters['category']) ?>"><?php endif; ?>
                        <input type="hidden" name="sort" value="<?= e($sort) ?>">
                        <div class="input-group">
                            <input class="form-control" type="search" name="q" placeholder="Tên hoặc thương hiệu" value="<?= e($filters['q']) ?>">
                            <button class="bh-btn bh-btn--dark" type="submit" aria-label="Tìm"><i class="bi bi-search"></i></button>
                        </div>
                        <?php if ($filters['max_price'] > 0) : ?><input type="hidden" name="max_price" value="<?= (int) $filters['max_price'] ?>"><?php endif; ?>
                    </form>

                    <h3 class="bh-sidebar__title">Danh mục sản phẩm</h3>
                    <ul class="bh-cat-list">
                        <li><a href="<?= e(query_url([], ['category', 'page'])) ?>" class="<?= $filters['category'] === '' ? 'is-active' : '' ?>"><i class="bi bi-grid"></i> Tất cả sản phẩm</a></li>
                        <?php foreach ($categories as $category) : ?>
                            <li><a href="<?= e(query_url(['category' => $category['slug']], ['page'])) ?>" class="<?= $filters['category'] === $category['slug'] ? 'is-active' : '' ?>"><i class="bi bi-chevron-right"></i> <?= e($category['name']) ?> <small class="bh-muted">(<?= (int) $category['product_count'] ?>)</small></a></li>
                        <?php endforeach; ?>
                    </ul>

                    <h3 class="bh-sidebar__title">Lọc theo giá</h3>
                    <form action="/products" method="get">
                        <?php foreach (['q' => $filters['q'], 'category' => $filters['category'], 'sort' => $sort] as $key => $value) : ?>
                            <?php if ($value !== '') : ?><input type="hidden" name="<?= e($key) ?>" value="<?= e($value) ?>"><?php endif; ?>
                        <?php endforeach; ?>
                        <input class="bh-range" id="bhPriceRange" type="range" name="max_price" min="0" max="<?= $sliderMax ?>" step="10000" value="<?= $currentMax ?>">
                        <div class="d-flex justify-content-between small bh-muted"><span>0 ₫</span><span>Tối đa: <strong id="bhPriceOutput"></strong></span></div>
                        <button class="bh-btn bh-btn--primary w-100 mt-3" type="submit">Áp dụng</button>
                    </form>
                </div>
            </div>
        </aside>

        <section class="col-lg-9">
            <div class="bh-toolbar">
                <span class="bh-muted">Hiển thị <?= $paginator->getFirstItem() ?>–<?= $paginator->getLastItem() ?> của <?= $paginator->getTotalItems() ?> kết quả</span>
                <form method="get" action="/products" class="d-flex align-items-center gap-2">
                    <?php foreach (['q' => $filters['q'], 'category' => $filters['category']] as $key => $value) : ?>
                        <?php if ($value !== '') : ?><input type="hidden" name="<?= e($key) ?>" value="<?= e($value) ?>"><?php endif; ?>
                    <?php endforeach; ?>
                    <?php if ($filters['max_price'] > 0) : ?><input type="hidden" name="max_price" value="<?= (int) $filters['max_price'] ?>"><?php endif; ?>
                    <label class="bh-muted" for="bhSort">Sắp xếp theo:</label>
                    <select class="form-select" id="bhSort" name="sort" data-autosubmit>
                        <?php foreach ($sortOptions as $value => $label) : ?>
                            <option value="<?= e($value) ?>"<?= $sort === $value ? ' selected' : '' ?>><?= e($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>

            <?php if ($products === []) : ?>
                <div class="bh-panel text-center py-5"><i class="bi bi-search fs-1 bh-muted"></i><p class="mt-3 mb-0">Không tìm thấy sản phẩm phù hợp.</p></div>
            <?php else : ?>
                <div class="row g-4 row-cols-1 row-cols-sm-2 row-cols-lg-3">
                    <?php foreach ($products as $product) : ?>
                        <div class="col"><?php partial('product-card', ['product' => $product]); ?></div>
                    <?php endforeach; ?>
                </div>
                <div class="mt-5"><?= $paginator->render() ?></div>
            <?php endif; ?>
        </section>
    </div>
</div>
