<?php
partial('hero', ['heroTitle' => 'Câu hỏi thường gặp', 'crumbs' => [['Trang chủ', '/'], ['FAQ', null]]]);
$faqs = [
    ['Tôi có thể thanh toán bằng cách nào?', 'BakeHouse hỗ trợ thanh toán khi nhận hàng (COD) và chuyển khoản qua mã VietQR. Với VietQR, mã QR đúng số tiền và nội dung chuyển khoản hiển thị ngay sau khi đặt hàng.'],
    ['Phí vận chuyển là bao nhiêu?', 'Phí vận chuyển cố định cho mỗi đơn hàng và được hiển thị rõ ở bước thanh toán.'],
    ['Làm sao theo dõi đơn hàng?', 'Vào mục "Đơn hàng của tôi" để xem trạng thái: Chờ xử lý → Đã xác nhận → Đang giao → Hoàn tất.'],
    ['Khi nào tôi có thể đánh giá sản phẩm?', 'Bạn chỉ có thể đánh giá sau khi đã nhận hàng (đơn ở trạng thái Hoàn tất), mỗi sản phẩm một lần.'],
    ['Tôi quên mật khẩu thì làm thế nào?', 'Chọn "Quên mật khẩu" ở trang đăng nhập và làm theo hướng dẫn trong liên kết được gửi.'],
];
?>
<div class="container py-5" style="max-width:820px">
    <div class="accordion" id="bhFaq">
        <?php foreach ($faqs as $i => [$q, $a]) : ?>
            <div class="accordion-item">
                <h2 class="accordion-header"><button class="accordion-button<?= $i ? ' collapsed' : '' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#faq<?= $i ?>"><?= e($q) ?></button></h2>
                <div id="faq<?= $i ?>" class="accordion-collapse collapse<?= $i ? '' : ' show' ?>" data-bs-parent="#bhFaq"><div class="accordion-body"><?= e($a) ?></div></div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
