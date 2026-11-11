<?php /** @var array $rows */ /** @var \App\Core\Paginator $paginator */ /** @var string $search */ /** @var string $status */
$labels = ['new' => 'Mới', 'read' => 'Đã đọc', 'replied' => 'Đã trả lời'];
partial('admin-filter', ['search' => $search, 'placeholder' => 'Tìm theo tên, email, nội dung…', 'selects' => ['status' => ['Mọi trạng thái', $labels, $status]]]); ?>
<div class="bh-panel p-0 table-responsive"><table class="table table-hover align-middle mb-0 bh-table">
    <thead><tr><th>Người gửi</th><th>Liên hệ</th><th>Nội dung</th><th>Ngày</th><th>Trạng thái</th></tr></thead><tbody>
    <?php foreach ($rows as $c) : ?>
        <tr><td><?= e($c['full_name']) ?></td><td><?= e($c['email'] ?? '') ?><div class="small bh-muted"><?= e($c['phone'] ?? '') ?></div></td><td style="max-width:360px"><?= nl2br(e($c['message'])) ?></td>
            <td><?= e(date('d/m/Y H:i', strtotime($c['created_at']))) ?></td>
            <td><form method="post" action="/admin/contacts/<?= (int) $c['id'] ?>/status"><?= csrf_field() ?>
                <select class="form-select form-select-sm" style="min-height:38px" name="status" data-autosubmit><?php foreach ($labels as $v => $l) : ?><option value="<?= e($v) ?>"<?= $c['status'] === $v ? ' selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?></select></form></td></tr>
    <?php endforeach; ?>
    <?php if ($rows === []) : ?><tr><td colspan="5" class="text-center bh-muted py-4">Chưa có liên hệ.</td></tr><?php endif; ?>
</tbody></table></div>
<div class="mt-3"><?= $paginator->render() ?></div>
