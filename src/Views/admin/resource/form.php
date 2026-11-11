<?php /** @var array $config */ /** @var ?array $row */ /** @var string $action */
$base = '/admin/' . $config['route']; ?>
<div class="bh-panel" style="max-width:720px">
    <form method="post" action="<?= e($action) ?>" enctype="multipart/form-data" novalidate>
        <?= csrf_field() ?>
        <?php foreach ($config['fields'] as $field) : $name = $field['name']; $value = $row[$name] ?? ''; ?>
            <?php if ($field['type'] === 'checkbox') : ?>
                <div class="form-check form-switch mb-3"><input class="form-check-input" type="checkbox" id="f_<?= e($name) ?>" name="<?= e($name) ?>" value="1"<?= (!$row || $value) ? ' checked' : '' ?>><label class="form-check-label" for="f_<?= e($name) ?>"><?= e($field['label']) ?></label></div>
            <?php else : ?>
                <div class="mb-3"><label class="form-label" for="f_<?= e($name) ?>"><?= e($field['label']) ?></label>
                <?php if ($field['type'] === 'textarea') : ?>
                    <textarea class="form-control" id="f_<?= e($name) ?>" name="<?= e($name) ?>" rows="3"><?= old($name, (string) $value) ?></textarea>
                <?php elseif ($field['type'] === 'image') : ?>
                    <input class="form-control" type="file" id="f_<?= e($name) ?>" name="<?= e($name) ?>" accept="image/jpeg,image/png,image/webp" data-preview="#preview_<?= e($name) ?>">
                    <div class="form-text">JPG/PNG/WEBP, tối đa 2MB.</div>
                    <div id="preview_<?= e($name) ?>" class="mt-2"><?php if ($value) : ?><img class="bh-thumb" style="width:84px;height:84px" src="<?= e(img_url((string) $value)) ?>" alt=""><?php endif; ?></div>
                <?php else : ?>
                    <input class="form-control" id="f_<?= e($name) ?>" name="<?= e($name) ?>" value="<?= old($name, (string) $value) ?>">
                <?php endif; ?>
                <?= form_error($name) ?></div>
            <?php endif; ?>
        <?php endforeach; ?>
        <button class="bh-btn bh-btn--primary" type="submit">Lưu</button>
        <a class="bh-btn bh-btn--outline ms-2" href="<?= e($base) ?>">Hủy</a>
    </form>
</div>
