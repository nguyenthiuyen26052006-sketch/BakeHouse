<?php /** @var \Throwable|null $exception */ ?>
<section class="container bh-error">
    <div class="bh-error__code">500</div>
    <h1 class="h3">Đã xảy ra lỗi</h1>
    <p class="text-muted">Hệ thống gặp sự cố, vui lòng thử lại sau.</p>
    <?php if (!empty($exception)) : ?>
        <pre class="text-start small bg-white border rounded p-3"><?= e($exception->getMessage() . ' @ ' . $exception->getFile() . ':' . $exception->getLine()) ?></pre>
    <?php endif; ?>
    <a class="btn btn-lg" style="background:var(--bh-color-caramel);color:#fff" href="<?= e(\App\Core\Auth::homeUrl()) ?>"><?= \App\Core\Auth::isAdmin() ? 'Về trang quản trị' : 'Về trang chủ' ?></a>
</section>
