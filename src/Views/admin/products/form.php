<?php /** @var ?array $product */ /** @var array $images */ /** @var array $categories */ /** @var array $brands */
$action = $product ? '/admin/products/' . $product['id'] : '/admin/products';
$v = static fn (string $key, mixed $default = '') => old($key, (string) ($product[$key] ?? $default)); ?>
<form method="post" action="<?= e($action) ?>" enctype="multipart/form-data" novalidate>
    <?= csrf_field() ?>
    <div class="adm-form">
        <section class="adm-card">
            <div class="mb-3"><label class="form-label" for="name">Tên sản phẩm</label><input class="form-control" id="name" name="name" value="<?= $v('name') ?>" required><?= form_error('name') ?></div>
            <div class="row g-3 mb-3">
                <div class="col-sm-6"><label class="form-label" for="category_id">Danh mục</label>
                    <select class="form-select" id="category_id" name="category_id" required><option value="">— Chọn —</option>
                        <?php foreach ($categories as $c) : ?><option value="<?= (int) $c['id'] ?>"<?= (string) ($product['category_id'] ?? old('category_id')) === (string) $c['id'] ? ' selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach; ?></select><?= form_error('category_id') ?></div>
                <div class="col-sm-6"><label class="form-label" for="brand_id">Thương hiệu</label>
                    <select class="form-select" id="brand_id" name="brand_id"><option value="">— Không có —</option>
                        <?php foreach ($brands as $b) : ?><option value="<?= (int) $b['id'] ?>"<?= (string) ($product['brand_id'] ?? old('brand_id')) === (string) $b['id'] ? ' selected' : '' ?>><?= e($b['name']) ?></option><?php endforeach; ?></select></div>
            </div>
            <div class="row g-3 mb-3">
                <div class="col-sm-4"><label class="form-label" for="price">Giá bán (₫)</label><input class="form-control" id="price" type="number" min="0" step="500" name="price" value="<?= $v('price', '0') ?>" required><?= form_error('price') ?></div>
                <div class="col-sm-4"><label class="form-label" for="stock_quantity">Tồn kho</label><input class="form-control" id="stock_quantity" type="number" min="0" name="stock_quantity" value="<?= $v('stock_quantity', '0') ?>" required><?= form_error('stock_quantity') ?></div>
                <div class="col-sm-4"><label class="form-label" for="unit">Đơn vị tính</label><input class="form-control" id="unit" name="unit" value="<?= $v('unit', 'gói') ?>" placeholder="gói 1kg, hộp, cái…" required><?= form_error('unit') ?></div>
            </div>
            <div class="mb-3"><label class="form-label" for="description">Mô tả</label><textarea class="form-control" id="description" name="description" rows="5"><?= $v('description') ?></textarea><?= form_error('description') ?></div>
            <div class="d-flex flex-wrap gap-4">
                <div class="form-check form-switch"><input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"<?= (!$product || $product['is_active']) ? ' checked' : '' ?>><label class="form-check-label" for="is_active">Đang bán</label></div>
                <div class="form-check form-switch"><input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" value="1"<?= !empty($product['is_featured']) ? ' checked' : '' ?>><label class="form-check-label" for="is_featured">Hiển thị nổi bật trên trang chủ</label></div>
            </div>
        </section>
        <section class="adm-card">
            <div class="mb-3"><label class="form-label" for="image">Ảnh chính</label>
                <input class="form-control" type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp" data-preview="#previewMain"><?= form_error('image') ?>
                <div id="previewMain" class="mt-2"><?php if (!empty($product['image_path'])) : ?><img class="bh-thumb" style="width:96px;height:96px" src="<?= e(img_url($product['image_path'])) ?>" alt=""><?php endif; ?></div></div>
            <div class="mb-3"><label class="form-label" for="gallery">Ảnh phụ</label>
                <input class="form-control" type="file" id="gallery" name="gallery[]" multiple accept="image/jpeg,image/png,image/webp" data-preview="#previewGallery">
                <div id="previewGallery" class="mt-2"></div></div>
            <?php if ($images !== []) : ?>
                <div class="form-label">Ảnh hiện có <span class="bh-muted">(tick để xóa)</span></div>
                <div class="adm-imgs">
                    <?php foreach ($images as $image) : ?>
                        <label><img src="<?= e(img_url($image['image_path'])) ?>" alt=""><input type="checkbox" name="delete_images[]" value="<?= (int) $image['id'] ?>"> Xóa</label>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </div>
    <div class="adm-savebar"><button class="bh-btn bh-btn--primary" type="submit">Lưu sản phẩm</button><a class="bh-btn bh-btn--outline" href="/admin/products">Hủy</a></div>
</form>
