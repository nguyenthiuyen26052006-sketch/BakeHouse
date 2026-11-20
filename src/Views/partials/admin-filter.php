<?php
/** @var string $search */ /** @var string $placeholder */
/** @var array $selects  [name => [label, options(value=>label), current]] */
/** @var array $hidden   [name => value] kept when filtering (e.g. the active tab) */
/** @var ?string $createUrl */ /** @var ?string $createLabel */
$active = $search !== '';
$keep = array_filter($hidden ?? [], static fn ($v): bool => (string) $v !== '');
$clearUrl = current_path() . ($keep !== [] ? '?' . http_build_query($keep) : '');
foreach (($selects ?? []) as [, , $current]) {
    $active = $active || (string) $current !== '';
}
?>
<form method="get" class="adm-toolbar" role="search">
    <?php foreach (($hidden ?? []) as $name => $value) : if ((string) $value !== '') : ?><input type="hidden" name="<?= e($name) ?>" value="<?= e((string) $value) ?>"><?php endif; endforeach; ?>
    <div class="adm-search"><i class="bi bi-search" aria-hidden="true"></i><input class="form-control" type="search" name="q" value="<?= e($search) ?>" placeholder="<?= e($placeholder ?? 'Tìm kiếm…') ?>" aria-label="<?= e($placeholder ?? 'Tìm kiếm') ?>"></div>
    <?php foreach (($selects ?? []) as $name => [$label, $options, $current]) : ?>
        <select class="form-select" name="<?= e($name) ?>" aria-label="<?= e($label) ?>" data-autosubmit>
            <option value=""><?= e($label) ?></option>
            <?php foreach ($options as $value => $text) : ?><option value="<?= e((string) $value) ?>"<?= (string) $current === (string) $value ? ' selected' : '' ?>><?= e($text) ?></option><?php endforeach; ?>
        </select>
    <?php endforeach; ?>
    <?php if ($active) : ?><a class="adm-clear" href="<?= e($clearUrl) ?>"><i class="bi bi-x-circle"></i> Xóa lọc</a><?php endif; ?>
    <?php if (!empty($createUrl)) : ?><div class="adm-end"><a class="bh-btn bh-btn--primary bh-btn--sm" href="<?= e($createUrl) ?>"><i class="bi bi-plus-lg"></i> <?= e($createLabel ?? 'Thêm mới') ?></a></div><?php endif; ?>
</form>
