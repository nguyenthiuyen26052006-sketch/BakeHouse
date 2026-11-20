<?php /** @var \Throwable|null $exception */ ?>
<section class="container bh-error">
    <div class="bh-error__code">403</div>
    <h1 class="h3">Không có quyền truy cập</h1>
    <p class="text-muted">Bạn không được phép xem trang này.</p>
    <?php if (!empty($exception)) : ?>
        <pre class="text-start small bg-white border rounded p-3"><?= e($exception->getMessage() . ' @ ' . $exception->getFile() . ':' . $exception->getLine()) ?></pre>
    <?php endif; ?>
    <a class="btn btn-lg" style="background:var(--bh-color-caramel);color:#fff" href="<?= e(\App\Core\Auth::homeUrl()) ?>"><?= \App\Core\Auth::isAdmin() ? 'Về trang quản trị' : 'Về trang chủ' ?></a>
</section>
