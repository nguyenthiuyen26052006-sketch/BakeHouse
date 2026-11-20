<?php /** @var array $config */ /** @var ?array $row */ /** @var string $action */
$base = '/admin/' . $config['route']; ?>
<form class="adm-card adm-form" style="max-width:520px;display:block" method="post" action="<?= e($action) ?>" novalidate>
    <?= csrf_field() ?>
    <?php foreach ($config['fields'] as $field) : $name = $field['name']; $value = $row[$name] ?? ''; ?>
        <?php if ($field['type'] === 'checkbox') : ?>
            <div class="form-check form-switch mb-3"><input class="form-check-input" type="checkbox" id="f_<?= e($name) ?>" name="<?= e($name) ?>" value="1"<?= (!$row || $value) ? ' checked' : '' ?>><label class="form-check-label" for="f_<?= e($name) ?>"><?= e($field['label']) ?></label></div>
        <?php else : ?>
            <div class="mb-3"><label class="form-label" for="f_<?= e($name) ?>"><?= e($field['label']) ?></label>
                <input class="form-control" id="f_<?= e($name) ?>" name="<?= e($name) ?>" value="<?= old($name, (string) $value) ?>" required autofocus><?= form_error($name) ?></div>
        <?php endif; ?>
    <?php endforeach; ?>
    <div class="d-flex gap-2"><button class="bh-btn bh-btn--primary" type="submit">Lưu</button><a class="bh-btn bh-btn--outline" href="<?= e($base) ?>">Hủy</a></div>
</form>
