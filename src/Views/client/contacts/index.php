<?php /** @var array $rows */ /** @var \App\Core\Paginator $paginator */
$statusLabels = ['new' => 'Chưa xử lý', 'read' => 'Đã xem', 'replied' => 'Đã trả lời'];
$statusClasses = ['new' => 'bg-secondary', 'read' => 'bg-warning text-dark', 'replied' => 'bg-success'];
partial('hero', ['heroTitle' => 'Liên hệ của tôi', 'crumbs' => [['Trang chủ', '/'], ['Liên hệ', null]]]); ?>
<div class="container py-5">
<?php if ($rows === []) : ?>
    <div class="bh-panel text-center py-5">
        <p>Bạn chưa gửi liên hệ nào.</p>
        <a class="bh-btn bh-btn--primary" href="/contact">Gửi liên hệ</a>
    </div>
<?php else : ?>
    <?php foreach ($rows as $c) : ?>
        <div class="bh-panel mb-3">
            <div class="d-flex justify-content-between flex-wrap gap-2 mb-2">
                <small class="bh-muted"><?= e(date('d/m/Y H:i', strtotime($c['created_at']))) ?></small>
                <span class="badge <?= e($statusClasses[$c['status']] ?? 'bg-secondary') ?>"><?= e($statusLabels[$c['status']] ?? $c['status']) ?></span>
            </div>
            <p class="mb-0"><?= nl2br(e($c['message'])) ?></p>
            <?php if (!empty($c['admin_reply'])) : ?>
                <div class="mt-3 ps-3 border-start border-3" style="border-color: var(--bh-color-caramel) !important;">
                    <strong class="small text-uppercase" style="color: var(--bh-color-caramel-text)">Phản hồi từ BakeHouse</strong>
                    <small class="bh-muted ms-2"><?= e(date('d/m/Y H:i', strtotime($c['admin_replied_at']))) ?></small>
                    <p class="mb-0 mt-1 small"><?= nl2br(e($c['admin_reply'])) ?></p>
                </div>
            <?php else : ?>
                <p class="bh-muted small mt-3 mb-0">Chưa có phản hồi — chúng tôi sẽ trả lời sớm nhất.</p>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
    <div class="mt-4"><?= $paginator->render() ?></div>
<?php endif; ?>
</div>
