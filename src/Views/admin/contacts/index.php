<?php /** @var array $rows */ /** @var \App\Core\Paginator $paginator */ /** @var string $search */ /** @var string $status */
$labels = ['new' => 'Mới', 'read' => 'Đã đọc', 'replied' => 'Đã trả lời'];
partial('admin-filter', ['search' => $search, 'placeholder' => 'Tìm theo tên, email, nội dung…', 'selects' => ['status' => ['Mọi trạng thái', $labels, $status]]]); ?>
<div class="bh-panel p-0 table-responsive"><table class="table table-hover align-middle mb-0 bh-table">
    <thead><tr><th>Người gửi</th><th>Liên hệ</th><th>Nội dung</th><th>Phản hồi</th><th>Ngày</th></tr></thead><tbody>
    <?php foreach ($rows as $c) : ?>
        <tr><td><?= e($c['full_name']) ?></td><td><?= e($c['email'] ?? '') ?><div class="small bh-muted"><?= e($c['phone'] ?? '') ?></div></td><td style="max-width:300px"><?= nl2br(e($c['message'])) ?></td>
            <td style="min-width:260px">
                <?php if (!empty($c['admin_reply'])) : ?>
                    <p class="small mb-1"><i class="bi bi-chat-left-text text-warning"></i> <?= nl2br(e($c['admin_reply'])) ?></p>
                <?php endif; ?>
                <details>
                    <summary class="small" style="cursor:pointer;color:var(--bh-color-caramel-text)"><?= !empty($c['admin_reply']) ? 'Sửa phản hồi' : 'Trả lời' ?></summary>
                    <form class="mt-2" method="post" action="/admin/contacts/<?= (int) $c['id'] ?>/reply"><?= csrf_field() ?>
                        <textarea class="form-control form-control-sm mb-2" name="admin_reply" rows="2" placeholder="Nhập nội dung phản hồi…"><?= e($c['admin_reply'] ?? '') ?></textarea>
                        <button class="bh-btn bh-btn--outline bh-btn--sm" type="submit">Lưu phản hồi</button>
                    </form>
                </details>
            </td>
            <td><?= e(date('d/m/Y H:i', strtotime($c['created_at']))) ?></td></tr>
    <?php endforeach; ?>
    <?php if ($rows === []) : ?><tr><td colspan="5" class="text-center bh-muted py-4">Chưa có liên hệ.</td></tr><?php endif; ?>
</tbody></table></div>
<div class="mt-3"><?= $paginator->render() ?></div>
