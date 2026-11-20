<?php
/** @var array<string, string> $tabs   value => label */
/** @var array<string, int> $counts    value => count (optional) */
/** @var string $current */ /** @var string $param  query-string key (default "status") */
/** @var array<string, string> $keep   other query params to keep (e.g. search) */
/** @var array<string, bool> $alert    values whose count should be highlighted */
$param = $param ?? 'status';
?>
<nav class="adm-tabs" aria-label="Lọc nhanh">
    <?php foreach ($tabs as $value => $label) :
        $query = array_filter(array_merge($keep ?? [], [$param => $value]), static fn ($v): bool => (string) $v !== '');
        $on = (string) $current === (string) $value; ?>
        <a class="adm-tab<?= $on ? ' is-active' : '' ?>" href="<?= e(current_path() . ($query ? '?' . http_build_query($query) : '')) ?>"<?= $on ? ' aria-current="true"' : '' ?>>
            <?= e($label) ?><?php if (isset($counts[$value])) : ?><span class="adm-tab__n<?= !empty($alert[$value]) && $counts[$value] > 0 ? ' is-alert' : '' ?>"><?= (int) $counts[$value] ?></span><?php endif; ?>
        </a>
    <?php endforeach; ?>
</nav>
