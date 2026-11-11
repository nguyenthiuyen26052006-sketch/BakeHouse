<?php /** @var array $config */ /** @var array $rows */ /** @var \App\Core\Paginator $paginator */ /** @var string $search */
$base = '/admin/' . $config['route'];
partial('admin-filter', ['search' => $search, 'placeholder' => 'Tìm ' . mb_strtolower($config['title']) . '…', 'createUrl' => $base . '/create']); ?>
<div class="bh-panel p-0 table-responsive">
    <table class="table table-hover align-middle mb-0 bh-table">
        <thead><tr><?php foreach ($config['columns'] as $col) : ?><th><?= e($col['label']) ?></th><?php endforeach; ?><th class="text-end">Thao tác</th></tr></thead>
        <tbody>
        <?php foreach ($rows as $row) : ?>
            <tr>
                <?php foreach ($config['columns'] as $col) : $value = $row[$col['key']] ?? null; ?>
                    <td><?php if ($col['type'] === 'image') : ?><img class="bh-thumb" src="<?= e(img_url($value)) ?>" alt="">
                        <?php elseif ($col['type'] === 'bool') : ?><?= $value ? '<span class="badge bg-success">Bật</span>' : '<span class="badge bg-secondary">Tắt</span>' ?>
                        <?php elseif ($col['type'] === 'date') : ?><?= e(date('d/m/Y', strtotime((string) $value))) ?>
                        <?php else : ?><?= e((string) $value) ?><?php endif; ?></td>
                <?php endforeach; ?>
                <td class="text-end text-nowrap">
                    <a class="bh-btn bh-btn--outline bh-btn--sm" href="<?= e($base . '/' . $row['id'] . '/edit') ?>"><i class="bi bi-pencil"></i></a>
                    <form class="d-inline" method="post" action="<?= e($base . '/' . $row['id'] . '/delete') ?>" data-confirm="Bạn có chắc muốn xóa mục này?"><?= csrf_field() ?><button class="bh-btn bh-btn--dark bh-btn--sm" type="submit"><i class="bi bi-trash"></i></button></form>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if ($rows === []) : ?><tr><td colspan="<?= count($config['columns']) + 1 ?>" class="text-center bh-muted py-4">Không có dữ liệu.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>
<div class="mt-3"><?= $paginator->render() ?></div>
