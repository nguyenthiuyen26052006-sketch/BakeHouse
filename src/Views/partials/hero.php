<?php /** @var string $heroTitle */ /** @var array<int, array{0: string, 1: ?string}> $crumbs */ ?>
<section class="bh-hero">
    <h1 class="bh-hero__title"><?= e($heroTitle) ?></h1>
    <nav class="bh-breadcrumb" aria-label="breadcrumb">
        <?php foreach ($crumbs ?? [] as $index => [$label, $href]) : ?>
            <?= $index > 0 ? ' / ' : '' ?><?= $href ? '<a href="' . e($href) . '">' . e($label) . '</a>' : e($label) ?>
        <?php endforeach; ?>
    </nav>
</section>
