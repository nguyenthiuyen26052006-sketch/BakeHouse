<?php /** @var ?array $product */ /** @var array $images */ /** @var array $selectedTags */ /** @var array $categories */ /** @var array $brands */ /** @var array $tags */
$action = $product ? '/admin/products/' . $product['id'] : '/admin/products';
$v = static fn (string $key, mixed $default = '') => old($key, (string) ($product[$key] ?? $default)); ?>
<form method="post" action="<?= e($action) ?>" enctype="multipart/form-data" novalidate>
    <?= csrf_field() ?>
    <div class="row g-4">
        <div class="col-lg-8"><div class="bh-panel">
            <div class="mb-3"><label class="form-label" for="name">Tên nguyên vật liệu</label><input class="form-control" id="name" name="name" value="<?= $v('name') ?>" required><?= form_error('name') ?></div>
            <div class="row g-3 mb-3">
                <div class="col-md-6"><label class="form-label" for="category_id">Danh mục</label>
                    <select class="form-select" id="category_id" name="category_id" required><option value="">— Chọn —</option>
                        <?php foreach ($categories as $c) : ?><option value="<?= (int) $c['id'] ?>"<?= (string) ($product['category_id'] ?? old('category_id')) === (string) $c['id'] ? ' selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach; ?></select><?= form_error('category_id') ?></div>
                <div class="col-md-6"><label class="form-label" for="brand_id">Thương hiệu</label>
                    <select class="form-select" id="brand_id" name="brand_id"><option value="">— Không có —</option>
                        <?php foreach ($brands as $b) : ?><option value="<?= (int) $b['id'] ?>"<?= (string) ($product['brand_id'] ?? old('brand_id')) === (string) $b['id'] ? ' selected' : '' ?>><?= e($b['name']) ?></option><?php endforeach; ?></select></div>
            </div>
            <div class="row g-3 mb-3">
                <div class="col-md-4"><label class="form-label" for="price">Giá (₫)</label><input class="form-control" id="price" type="number" min="0" name="price" value="<?= $v('price', '0') ?>" required><?= form_error('price') ?></div>
                <div class="col-md-4"><label class="form-label" for="stock_quantity">Tồn kho</label><input class="form-control" id="stock_quantity" type="number" min="0" name="stock_quantity" value="<?= $v('stock_quantity', '0') ?>" required><?= form_error('stock_quantity') ?></div>
                <div class="col-md-4"><label class="form-label" for="unit">Đơn vị</label><input class="form-control" id="unit" name="unit" value="<?= $v('unit', 'gói') ?>" required><?= form_error('unit') ?></div>
            </div>
            <div class="mb-3"><label class="form-label" for="description">Mô tả</label><textarea class="form-control" id="description" name="description" rows="5"><?= $v('description') ?></textarea><?= form_error('description') ?></div>
            <div class="mb-3"><label class="form-label d-block">Tags</label>
                <?php foreach ($tags as $tag) : ?>
                    <div class="form-check form-check-inline"><input class="form-check-input" type="checkbox" id="tag<?= (int) $tag['id'] ?>" name="tag_ids[]" value="<?= (int) $tag['id'] ?>"<?= in_array((int) $tag['id'], $selectedTags, true) ? ' checked' : '' ?>><label class="form-check-label" for="tag<?= (int) $tag['id'] ?>"><?= e($tag['name']) ?></label></div>
                <?php endforeach; ?></div>
            <div class="d-flex gap-4">
                <div class="form-check form-switch"><input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" value="1"<?= !empty($product['is_featured']) ? ' checked' : '' ?>><label class="form-check-label" for="is_featured">Nổi bật</label></div>
                <div class="form-check form-switch"><input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"<?= (!$product || $product['is_active']) ? ' checked' : '' ?>><label class="form-check-label" for="is_active">Đang bán</label></div>
            </div>
        </div></div>
        <div class="col-lg-4"><div class="bh-panel">
            <div class="mb-3"><label class="form-label" for="image">Ảnh chính</label>
                <input class="form-control" type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp" data-preview="#previewMain"><?= form_error('image') ?>
                <div id="previewMain" class="mt-2"><?php if (!empty($product['image_path'])) : ?><img class="bh-thumb" style="width:84px;height:84px" src="<?= e(img_url($product['image_path'])) ?>" alt=""><?php endif; ?></div></div>
            <div class="mb-3"><label class="form-label" for="gallery">Thư viện ảnh (chọn nhiều)</label>
                <input class="form-control" type="file" id="gallery" name="gallery[]" multiple accept="image/jpeg,image/png,image/webp" data-preview="#previewGallery">
                <div id="previewGallery" class="mt-2"></div></div>
            <?php if ($images !== []) : ?>
                <label class="form-label">Ảnh hiện có (tick để xóa)</label>
                <div class="d-flex flex-wrap gap-2">
                    <?php foreach ($images as $image) : ?>
                        <label class="text-center small"><img class="bh-thumb d-block" style="width:72px;height:72px" src="<?= e(img_url($image['image_path'])) ?>" alt=""><input type="checkbox" name="delete_images[]" value="<?= (int) $image['id'] ?>"> Xóa</label>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div></div>
    </div>
    <div class="mt-4"><button class="bh-btn bh-btn--primary" type="submit">Lưu sản phẩm</button><a class="bh-btn bh-btn--outline ms-2" href="/admin/products">Hủy</a></div>
</form>
