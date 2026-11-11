<?php partial('hero', ['heroTitle' => 'Liên hệ', 'crumbs' => [['Trang chủ', '/'], ['Liên hệ', null]]]); ?>
<div class="container py-5">
    <div class="row g-4">
        <div class="col-lg-5">
            <div class="bh-panel h-100">
                <h4>Thông tin liên hệ</h4>
                <p class="mt-3"><i class="bi bi-geo-alt me-2 text-warning"></i>12 Nguyễn Huệ, Quận 1, TP.HCM</p>
                <p><i class="bi bi-telephone me-2 text-warning"></i>0900 000 001</p>
                <p><i class="bi bi-envelope me-2 text-warning"></i>hello@bakehouse.vn</p>
                <p class="mb-0"><i class="bi bi-clock me-2 text-warning"></i>8:00 – 20:00 mỗi ngày</p>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="bh-panel">
                <form method="post" action="/contact" novalidate>
                    <?= csrf_field() ?>
                    <div class="mb-3"><label class="form-label" for="full_name">Họ tên</label><input class="form-control" id="full_name" name="full_name" value="<?= old('full_name') ?>" required><?= form_error('full_name') ?></div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6"><label class="form-label" for="email">Email</label><input class="form-control" id="email" name="email" value="<?= old('email') ?>"><?= form_error('email') ?></div>
                        <div class="col-md-6"><label class="form-label" for="phone">Số điện thoại</label><input class="form-control" id="phone" name="phone" value="<?= old('phone') ?>"><?= form_error('phone') ?></div>
                    </div>
                    <div class="mb-3"><label class="form-label" for="message">Nội dung</label><textarea class="form-control" id="message" name="message" rows="5" required><?= old('message') ?></textarea><?= form_error('message') ?></div>
                    <button class="bh-btn bh-btn--primary" type="submit">Gửi liên hệ</button>
                </form>
            </div>
        </div>
    </div>
</div>
