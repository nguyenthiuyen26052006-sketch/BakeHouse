<?php /** @var \Throwable|null $exception */ ?>
<section class="container bh-error">
    <div class="bh-error__code">404</div>
    <h1 class="h3">Không tìm thấy trang</h1>
    <p class="text-muted">Trang bạn tìm không tồn tại.</p>
    <?php if (!empty($exception)) : ?>
        <pre class="text-start small bg-white border rounded p-3"><?= e($exception->getMessage() . ' @ ' . $exception->getFile() . ':' . $exception->getLine()) ?></pre>
    <?php endif; ?>
    <a class="btn btn-lg" style="background:var(--bh-color-caramel);color:#fff" href="/">Về trang chủ</a>
</section>
