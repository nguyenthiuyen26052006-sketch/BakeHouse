<?php
/** @var array $product */ /** @var array $images */ /** @var array $tags */ /** @var array $related */
/** @var array $reviews */ /** @var \App\Core\Paginator $paginator */ /** @var ?array $myReview */ /** @var bool $canReview */
$gallery = [];
if (!empty($product['image_path'])) { $gallery[] = img_url($product['image_path']); }
foreach ($images as $image) { $gallery[] = img_url($image['image_path']); }
$main = $gallery[0] ?? img_url(null);
$inStock = (int) $product['stock_quantity'] > 0;
partial('hero', ['heroTitle' => $product['name'], 'crumbs' => [['Trang chủ', '/'], ['Sản phẩm', '/products'], [$product['category_name'], '/products?category=' . $product['category_slug']]]]);
?>
<div class="container py-5">
    <div class="row g-5">
        <div class="col-md-6">
            <div class="bh-gallery__main mb-3"><img id="bhGalleryMain" src="<?= e($main) ?>" alt="<?= e($product['name']) ?>"></div>
            <?php if (count($gallery) > 1) : ?>
                <div class="d-flex gap-2 flex-wrap">
                    <?php foreach ($gallery as $i => $src) : ?>
                        <img class="bh-gallery__thumb<?= $i === 0 ? ' is-active' : '' ?>" src="<?= e($src) ?>" data-gallery-thumb="<?= e($src) ?>" alt="Ảnh <?= $i + 1 ?>">
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <div class="col-md-6">
            <div class="bh-card__category"><?= e($product['category_name']) ?></div>
            <h2 class="my-2"><?= e($product['name']) ?></h2>
            <div class="mb-3 bh-muted">
                <?php if (!empty($product['brand_name'])) : ?>Thương hiệu: <strong><?= e($product['brand_name']) ?></strong> · <?php endif; ?>
                <?= render_stars((float) $product['rating_avg']) ?> <?= e(number_format((float) $product['rating_avg'], 1)) ?> (<?= (int) $product['rating_count'] ?> đánh giá)
            </div>
            <div class="bh-price fs-2 mb-3"><?= e(format_vnd((int) $product['price'])) ?> <small class="bh-muted fs-6">/ <?= e($product['unit']) ?></small></div>
            <p class="bh-muted"><?= nl2br(e($product['description'] ?? '')) ?></p>
            <p>Tình trạng: <?= $inStock ? '<span class="text-success fw-semibold">Còn ' . (int) $product['stock_quantity'] . ' sản phẩm</span>' : '<span class="text-danger fw-semibold">Hết hàng</span>' ?></p>
            <?php foreach ($tags as $tag) : ?><span class="bh-tag">#<?= e($tag['name']) ?></span><?php endforeach; ?>

            <?php if ($inStock) : ?>
                <form id="bhBuyNow" action="/checkout" method="get" class="d-flex flex-wrap gap-3 align-items-center mt-4">
                    <input type="hidden" name="buy_now" value="<?= (int) $product['id'] ?>">
                    <div class="bh-qty">
                        <button type="button" data-step="-1" aria-label="Giảm">−</button>
                        <input id="bhQty" type="number" name="qty" value="1" min="1" max="<?= (int) $product['stock_quantity'] ?>">
                        <button type="button" data-step="1" aria-label="Tăng">+</button>
                    </div>
                    <button type="button" class="bh-btn bh-btn--outline" data-add-to-cart="<?= (int) $product['id'] ?>" data-qty-input="#bhQty"><i class="bi bi-cart-plus"></i> Thêm vào giỏ</button>
                    <button type="submit" class="bh-btn bh-btn--primary"><i class="bi bi-lightning-charge"></i> Mua ngay</button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <section id="reviews" class="mt-5 pt-3">
        <h3 class="mb-4">Đánh giá sản phẩm</h3>
        <?php if ($canReview && $myReview) : ?>
            <div class="bh-panel mb-4">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                    <div>
                        <h5 class="mb-1">Đánh giá của bạn</h5>
                        <?= render_stars((float) $myReview['rating']) ?>
                        <p class="mb-0 mt-2"><?= nl2br(e($myReview['comment'] ?? '')) ?></p>
                    </div>
                    <div class="d-flex gap-2 flex-shrink-0">
                        <button class="bh-btn bh-btn--outline bh-btn--sm" type="button" data-bs-toggle="collapse" data-bs-target="#bhEditReview"><i class="bi bi-pencil"></i> Chỉnh sửa</button>
                        <form method="post" action="/products/<?= e($product['slug']) ?>/review/delete" data-confirm="Bạn có chắc muốn xóa đánh giá của mình?">
                            <?= csrf_field() ?>
                            <button class="bh-btn bh-btn--dark bh-btn--sm" type="submit"><i class="bi bi-trash"></i> Xóa đánh giá</button>
                        </form>
                    </div>
                </div>
                <div class="collapse<?= form_error('rating') || form_error('comment') ? ' show' : '' ?> mt-3" id="bhEditReview">
                    <hr>
                    <form method="post" action="/products/<?= e($product['slug']) ?>/review">
                        <?= csrf_field() ?>
                        <?php $currentRating = (int) ($myReview['rating'] ?? 5); ?>
                        <div class="bh-rating-input mb-2">
                            <?php for ($star = 5; $star >= 1; $star--) : ?>
                                <input type="radio" id="star<?= $star ?>" name="rating" value="<?= $star ?>"<?= $currentRating === $star ? ' checked' : '' ?>>
                                <label for="star<?= $star ?>" title="<?= $star ?> sao"><i class="bi bi-star-fill"></i></label>
                            <?php endfor; ?>
                        </div>
                        <textarea class="form-control mb-3" name="comment" rows="3" maxlength="1000" placeholder="Chia sẻ cảm nhận của bạn…"><?= e($myReview['comment'] ?? '') ?></textarea>
                        <?= form_error('rating') ?><?= form_error('comment') ?>
                        <button class="bh-btn bh-btn--primary" type="submit">Cập nhật đánh giá</button>
                    </form>
                </div>
            </div>
        <?php elseif ($canReview) : ?>
            <div class="bh-panel mb-4">
                <h5>Viết đánh giá</h5>
                <form method="post" action="/products/<?= e($product['slug']) ?>/review">
                    <?= csrf_field() ?>
                    <div class="bh-rating-input mb-2">
                        <?php for ($star = 5; $star >= 1; $star--) : ?>
                            <input type="radio" id="star<?= $star ?>" name="rating" value="<?= $star ?>"<?= $star === 5 ? ' checked' : '' ?>>
                            <label for="star<?= $star ?>" title="<?= $star ?> sao"><i class="bi bi-star-fill"></i></label>
                        <?php endfor; ?>
                    </div>
                    <textarea class="form-control mb-3" name="comment" rows="3" maxlength="1000" placeholder="Chia sẻ cảm nhận của bạn…"></textarea>
                    <?= form_error('rating') ?><?= form_error('comment') ?>
                    <button class="bh-btn bh-btn--primary" type="submit">Gửi đánh giá</button>
                </form>
            </div>
        <?php elseif (auth_user()) : ?>
            <p class="bh-muted">Chỉ khách hàng đã nhận sản phẩm này mới có thể đánh giá.</p>
        <?php else : ?>
            <p class="bh-muted"><a href="/login">Đăng nhập</a> và mua hàng để đánh giá sản phẩm.</p>
        <?php endif; ?>

        <?php $otherReviews = $myReview ? array_filter($reviews, static fn (array $r): bool => (int) $r['id'] !== (int) $myReview['id']) : $reviews; ?>
        <?php if ($otherReviews === []) : ?>
            <p class="bh-muted"><?= $myReview ? 'Chưa có đánh giá nào khác.' : 'Chưa có đánh giá nào.' ?></p>
        <?php endif; ?>
        <?php foreach ($otherReviews as $review) : ?>
            <div class="bh-panel mb-3">
                <div class="d-flex justify-content-between flex-wrap"><strong><?= e($review['author_name']) ?></strong><small class="bh-muted"><?= e(date('d/m/Y', strtotime($review['created_at']))) ?></small></div>
                <?= render_stars((float) $review['rating']) ?>
                <p class="mb-0 mt-2"><?= nl2br(e($review['comment'] ?? '')) ?></p>
                <?php if (!empty($review['admin_reply'])) : ?>
                    <div class="mt-3 ps-3 border-start border-3" style="border-color: var(--bh-color-caramel) !important;">
                        <strong class="small text-uppercase" style="color: var(--bh-color-caramel-text)">Phản hồi từ BakeHouse</strong>
                        <p class="mb-0 mt-1 small"><?= nl2br(e($review['admin_reply'])) ?></p>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
        <?= $paginator->render() ?>
    </section>

    <?php if ($related !== []) : ?>
        <section class="mt-5">
            <h3 class="mb-4">Sản phẩm liên quan</h3>
            <div class="row g-4 row-cols-1 row-cols-sm-2 row-cols-lg-4">
                <?php foreach ($related as $item) : ?><div class="col"><?php partial('product-card', ['product' => $item]); ?></div><?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>
</div>
