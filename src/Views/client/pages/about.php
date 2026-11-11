<?php partial('hero', ['heroTitle' => 'Giới thiệu', 'crumbs' => [['Trang chủ', '/'], ['Giới thiệu', null]]]); ?>
<div class="container py-5">
    <div class="row g-5 align-items-center">
        <div class="col-lg-6"><img class="img-fluid rounded-4" src="<?= e(img_url(null)) ?>" alt="BakeHouse" loading="lazy"></div>
        <div class="col-lg-6">
            <h2 class="mb-3">Câu chuyện của BakeHouse</h2>
            <p>BakeHouse ra đời từ niềm đam mê bếp bánh. Chúng tôi chọn lọc từng loại bột, đường, bơ, hương liệu và dụng cụ để bạn luôn có nguyên liệu tốt nhất cho mỗi mẻ bánh.</p>
            <p>Mỗi sản phẩm đều có nguồn gốc rõ ràng, được bảo quản đúng cách và giao nhanh trong nội thành. Đội ngũ của chúng tôi luôn sẵn sàng tư vấn cho cả người mới bắt đầu lẫn thợ bánh chuyên nghiệp.</p>
            <ul class="list-unstyled">
                <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Nguyên liệu chính hãng, rõ nguồn gốc</li>
                <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Giá hợp lý, giao hàng nhanh</li>
                <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Tư vấn công thức và nguyên liệu miễn phí</li>
            </ul>
        </div>
    </div>
</div>
