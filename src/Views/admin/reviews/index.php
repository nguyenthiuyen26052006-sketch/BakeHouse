<?php /** @var array $rows */ /** @var \App\Core\Paginator $paginator */ /** @var array $counts */ /** @var string $search */ /** @var string $view */ /** @var int $rating */
partial('admin-tabs', [
    'param' => 'reply', 'current' => $view,
    'tabs' => ['' => 'Tất cả', 'none' => 'Chưa phản hồi'],
    'counts' => ['' => $counts['all'], 'none' => $counts['unanswered']],
    'alert' => ['none' => true],
    'keep' => ['q' => $search, 'rating' => $rating ?: ''],
]);
partial('admin-filter', [
    'search' => $search, 'placeholder' => 'Tìm theo sản phẩm, nội dung, email…',
    'hidden' => ['reply' => $view],
    'selects' => ['rating' => ['Mọi số sao', [5 => '5 sao', 4 => '4 sao', 3 => '3 sao', 2 => '2 sao', 1 => '1 sao'], $rating ?: '']],
]); ?>
<div class="adm-reviews">
<?php foreach ($rows as $r) :
    $hasReply = !empty($r['admin_reply']);
    $stars = (int) $r['rating'];
    $tone = $stars >= 4 ? 'high' : ($stars === 3 ? 'mid' : 'low');
    $initial = mb_strtoupper(mb_substr(trim((string) $r['author_name']), 0, 1)) ?: '?'; ?>
    <article class="adm-review is-<?= $tone ?>">
        <aside class="adm-review__aside">
            <img class="adm-review__img" src="<?= e(img_url($r['product_image'])) ?>" alt="" loading="lazy">
            <div class="adm-review__prod">
                <div class="adm-review__pname"><?= e($r['product_name']) ?></div>
                <div class="adm-review__score"><strong><?= $stars ?>.0</strong><?= render_stars((float) $r['rating']) ?></div>
            </div>
        </aside>
        <div class="adm-review__main">
            <header class="adm-review__top">
                <span class="adm-avatar" aria-hidden="true"><?= e($initial) ?></span>
                <div class="adm-review__who">
                    <strong><?= e($r['author_name']) ?></strong>
                    <span class="adm-review__meta"><?= e($r['user_email']) ?> · <?= e(date('d/m/Y', strtotime($r['created_at']))) ?></span>
                </div>
                <?= $hasReply ? '<span class="adm-pill adm-pill--ok">Đã phản hồi</span>' : '<span class="adm-pill adm-pill--warn">Chưa phản hồi</span>' ?>
                <form method="post" action="/admin/reviews/<?= (int) $r['id'] ?>/delete" data-confirm="Xóa vĩnh viễn đánh giá này?"><?= csrf_field() ?><button class="bh-btn bh-btn--danger bh-btn--sm adm-icon" type="submit" title="Xóa" aria-label="Xóa đánh giá"><i class="bi bi-trash"></i></button></form>
            </header>
            <p class="adm-review__text"><?= $r['comment'] !== null && $r['comment'] !== '' ? nl2br(e($r['comment'])) : '<span class="bh-muted">(Không có nội dung)</span>' ?></p>
            <?php if ($hasReply) : ?>
                <div class="adm-review__reply"><strong>Admin</strong><?php if (!empty($r['admin_replied_at'])) : ?> <span class="adm-review__meta">· <?= e(date('d/m/Y', strtotime($r['admin_replied_at']))) ?></span><?php endif; ?><br><?= nl2br(e($r['admin_reply'])) ?></div>
            <?php endif; ?>
            <details class="adm-reply">
                <summary class="bh-btn <?= $hasReply ? 'bh-btn--outline' : 'bh-btn--primary' ?> bh-btn--sm"><i class="bi bi-reply"></i> <?= $hasReply ? 'Sửa phản hồi' : 'Phản hồi' ?></summary>
                <form class="adm-reply__box" method="post" action="/admin/reviews/<?= (int) $r['id'] ?>/reply"><?= csrf_field() ?>
                    <textarea id="reply-<?= (int) $r['id'] ?>" aria-label="Phản hồi gửi tới <?= e($r['author_name']) ?>" class="form-control" name="admin_reply" rows="4" maxlength="1000" placeholder="Viết phản hồi..." data-reply><?= e($r['admin_reply'] ?? '') ?></textarea>
                    <div class="adm-reply__foot">
                        <span class="d-flex gap-2">
                            <button class="bh-btn bh-btn--outline bh-btn--sm" type="button" data-cancel>Hủy</button>
                            <button class="bh-btn bh-btn--primary bh-btn--sm" type="submit">Gửi</button>
                        </span>
                    </div>
                </form>
            </details>
        </div>
    </article>
<?php endforeach; ?>
<?php if ($rows === []) : ?><div class="adm-card adm-empty">Không có đánh giá phù hợp.</div><?php endif; ?>
</div>
<div class="adm-pager"><?= $paginator->render() ?></div>

<script>
document.querySelectorAll('.adm-reply').forEach(function (d) {
  var ta = d.querySelector('[data-reply]');
  d.addEventListener('toggle', function () { if (d.open) { ta.focus(); ta.setSelectionRange(ta.value.length, ta.value.length); } });
  d.querySelector('[data-cancel]').addEventListener('click', function () { d.open = false; });
});
</script>
