<?php /** @var string $search */ /** @var string $placeholder */ /** @var array $selects  [name => [label, options(value=>label), current]] */ /** @var ?string $createUrl */ ?>
<form method="get" class="row g-2 align-items-end mb-3">
    <div class="col-12 col-md-4"><input class="form-control" type="search" name="q" value="<?= e($search) ?>" placeholder="<?= e($placeholder ?? 'Tìm kiếm…') ?>"></div>
    <?php foreach (($selects ?? []) as $name => [$label, $options, $current]) : ?>
        <div class="col-6 col-md-2">
            <select class="form-select" name="<?= e($name) ?>" aria-label="<?= e($label) ?>">
                <option value=""><?= e($label) ?></option>
                <?php foreach ($options as $value => $text) : ?><option value="<?= e((string) $value) ?>"<?= (string) $current === (string) $value ? ' selected' : '' ?>><?= e($text) ?></option><?php endforeach; ?>
            </select>
        </div>
    <?php endforeach; ?>
    <div class="col-auto"><button class="bh-btn bh-btn--dark" type="submit"><i class="bi bi-search"></i> Lọc</button></div>
    <div class="col-auto"><a class="bh-btn bh-btn--outline" href="<?= e(current_path()) ?>"><i class="bi bi-x-lg"></i> Xóa lọc</a></div>
    <?php if (!empty($createUrl)) : ?><div class="col-auto ms-md-auto"><a class="bh-btn bh-btn--primary" href="<?= e($createUrl) ?>"><i class="bi bi-plus-lg"></i> Thêm mới</a></div><?php endif; ?>
</form>
