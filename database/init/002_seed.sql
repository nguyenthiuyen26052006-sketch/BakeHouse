SET NAMES utf8mb4;

INSERT INTO users (id, email, phone, password_hash, role) VALUES
(1, 'admin1@bakehouse.vn', NULL, '$2y$10$cZ6PIYrutJLVS3qZMXx2SuH3izGxt71.fHYa0UZbJHbZRzBDC1zky', 'admin'),
(2, 'admin2@bakehouse.vn', NULL, '$2y$10$cZ6PIYrutJLVS3qZMXx2SuH3izGxt71.fHYa0UZbJHbZRzBDC1zky', 'admin'),
(3, 'user@bakehouse.vn', '0901234567', '$2y$10$ffNvtCJk5E9am/8wVcEYkegU33Bl0EweSesmfTMTYVhNlCK2zI1Ke', 'user');

INSERT INTO user_profiles (user_id, full_name, shipping_phone, shipping_address) VALUES
(1, 'Quản trị viên 1', '0900000001', '1 Nguyễn Huệ, Quận 1, TP.HCM'),
(2, 'Quản trị viên 2', '0900000002', '2 Lê Lợi, Quận 1, TP.HCM'),
(3, 'Nguyễn Văn A', '0901234567', '12 Trần Hưng Đạo, Quận 5, TP.HCM');

INSERT INTO carts (user_id) VALUES (1), (2), (3);

INSERT INTO categories (id, name, slug, description) VALUES
(1, 'Bột & Ngũ cốc', 'bot-ngu-coc', 'Các loại bột mì, bột bắp, yến mạch và ngũ cốc làm bánh.'),
(2, 'Chất tạo ngọt', 'chat-tao-ngot', 'Đường, mật ong, siro và các chất tạo ngọt.'),
(3, 'Chất béo', 'chat-beo', 'Bơ, dầu, shortening và kem béo.'),
(4, 'Dụng cụ làm bánh', 'dung-cu-lam-banh', 'Khuôn, cân, phới, cọ và dụng cụ cơ bản.'),
(5, 'Hương liệu & Màu thực phẩm', 'huong-lieu-mau-thuc-pham', 'Vani, cacao, bột trà xanh và màu thực phẩm.');

INSERT INTO brands (id, name, slug) VALUES
(1, 'Meizan', 'meizan'),
(2, 'Anchor', 'anchor'),
(3, 'Tường An', 'tuong-an'),
(4, 'Biên Hòa', 'bien-hoa'),
(5, 'Wilton', 'wilton'),
(6, 'Cái Lân', 'cai-lan'),
(7, 'Nielsen-Massey', 'nielsen-massey');

INSERT INTO tags (id, name, slug) VALUES
(1, 'Làm bánh mì', 'lam-banh-mi'),
(2, 'Làm bánh ngọt', 'lam-banh-ngot'),
(3, 'Không gluten', 'khong-gluten'),
(4, 'Cho người mới', 'cho-nguoi-moi'),
(5, 'Nhập khẩu', 'nhap-khau');

INSERT INTO products (id, category_id, brand_id, created_by, name, slug, description, unit, price, stock_quantity, is_featured, rating_avg, rating_count, created_at) VALUES
(1, 1, 1, 1, 'Bột Mì Đa Dụng (All-purpose Flour)', 'bot-mi-da-dung', 'Bột mì đa dụng phù hợp làm bánh quy, bánh bông lan và bánh muffin.', 'gói 1kg', 28000, 120, 1, 4.50, 2, '2026-10-01 09:00:00'),
(2, 1, 1, 1, 'Bột Mì Số 11 (Bread Flour)', 'bot-mi-so-11', 'Bột mì hàm lượng protein cao, chuyên làm bánh mì và pizza.', 'gói 1kg', 32000, 90, 1, 5.00, 1, '2026-10-02 09:00:00'),
(3, 2, 6, 1, 'Đường Cát Trắng', 'duong-cat-trang', 'Đường cát trắng tinh luyện, hạt mịn dễ tan.', 'gói 1kg', 24000, 200, 0, 4.00, 1, '2026-10-03 09:00:00'),
(4, 2, NULL, 1, 'Mật Ong Hoa Nhãn', 'mat-ong-hoa-nhan', 'Mật ong nguyên chất, dùng làm bánh và pha đồ uống.', 'chai 500ml', 120000, 40, 0, 0.00, 0, '2026-10-04 09:00:00'),
(5, 3, 2, 1, 'Bơ Lạt Anchor', 'bo-lat-anchor', 'Bơ lạt nhập khẩu New Zealand, béo thơm tự nhiên.', 'cục 227g', 65000, 60, 1, 5.00, 1, '2026-10-05 09:00:00'),
(6, 3, 3, 1, 'Dầu Ăn Thực Vật Tường An', 'dau-an-tuong-an', 'Dầu ăn tinh luyện dùng cho bánh bông lan.', 'chai 1L', 52000, 80, 0, 0.00, 0, '2026-10-06 09:00:00'),
(7, 4, 5, 1, 'Khuôn Bánh Tròn Wilton 20cm', 'khuon-banh-tron-wilton', 'Khuôn nhôm chống dính, đường kính 20cm.', 'cái', 145000, 25, 0, 0.00, 0, '2026-10-07 09:00:00'),
(8, 4, NULL, 1, 'Cân Điện Tử Nhà Bếp 5kg', 'can-dien-tu-5kg', 'Cân điện tử độ chính xác 1g, tối đa 5kg.', 'cái', 175000, 30, 1, 0.00, 0, '2026-10-08 09:00:00'),
(9, 1, 4, 2, 'Bột Bắp Biên Hòa', 'bot-bap-bien-hoa', 'Bột bắp dùng làm đặc sốt và làm mềm bánh.', 'hộp 400g', 18000, 150, 0, 0.00, 0, '2026-10-09 09:00:00'),
(10, 1, NULL, 2, 'Yến Mạch Cán Dẹt', 'yen-mach-can-det', 'Yến mạch nguyên hạt cán dẹt, dùng làm bánh cookie.', 'gói 500g', 45000, 70, 0, 0.00, 0, '2026-10-10 09:00:00'),
(11, 2, 6, 2, 'Đường Nâu Muscovado', 'duong-nau-muscovado', 'Đường nâu giàu mật mía, hương caramel đậm.', 'gói 500g', 38000, 100, 0, 5.00, 1, '2026-10-11 09:00:00'),
(12, 3, 2, 2, 'Kem Tươi Whipping Cream', 'kem-tuoi-whipping-cream', 'Whipping cream 35% béo, đánh bông ổn định.', 'hộp 1L', 135000, 35, 1, 0.00, 0, '2026-10-12 09:00:00'),
(13, 4, 5, 2, 'Phới Đánh Trứng Inox', 'phoi-danh-trung-inox', 'Phới lồng inox 304 cán dài, bền chắc.', 'cái', 55000, 60, 0, 0.00, 0, '2026-10-13 09:00:00'),
(14, 5, 7, 2, 'Tinh Chất Vani Nguyên Chất', 'tinh-chat-vani', 'Vani nguyên chất từ Madagascar, hương thơm sâu.', 'chai 60ml', 220000, 20, 1, 0.00, 0, '2026-10-14 09:00:00'),
(15, 5, NULL, 2, 'Bột Cacao Nguyên Chất', 'bot-cacao-nguyen-chat', 'Bột cacao không đường, vị đắng đậm đà.', 'gói 250g', 75000, 55, 0, 0.00, 0, '2026-10-15 09:00:00');


INSERT INTO product_tags (product_id, tag_id) VALUES
(1, 2), (1, 4), (2, 1), (3, 4), (5, 2), (5, 5), (7, 4), (8, 4),
(10, 2), (11, 2), (12, 2), (12, 5), (14, 5), (15, 2), (9, 3);

INSERT INTO banners (title, image_path, link_url, is_active) VALUES
('Nguyên liệu làm bánh chất lượng', 'assets/img/banner-1.svg', '/products', 1),
('Ưu đãi dụng cụ làm bánh', 'assets/img/banner-2.svg', '/products?category=dung-cu-lam-banh', 1),
('Bơ & kem tươi nhập khẩu', 'assets/img/banner-3.svg', '/products?category=chat-beo', 1);

INSERT INTO orders (id, order_code, user_id, recipient_name, shipping_phone, shipping_address, payment_method, payment_status, status, shipping_fee, total_amount, created_at) VALUES
(1, 'BH20261001001', 3, 'Nguyễn Văn A', '0901234567', '12 Trần Hưng Đạo, Quận 5, TP.HCM', 'cod', 'paid', 'delivered', 30000, 146000, '2026-10-20 10:00:00'),
(2, 'BH20261005002', 3, 'Nguyễn Văn A', '0901234567', '12 Trần Hưng Đạo, Quận 5, TP.HCM', 'vietqr', 'paid', 'shipped', 30000, 143000, '2026-10-25 14:30:00'),
(3, 'BH20261101003', 3, 'Nguyễn Văn A', '0901234567', '12 Trần Hưng Đạo, Quận 5, TP.HCM', 'cod', 'unpaid', 'processing', 30000, 188000, '2026-11-03 09:15:00'),
(4, 'BH20261108004', 3, 'Nguyễn Văn A', '0901234567', '12 Trần Hưng Đạo, Quận 5, TP.HCM', 'vietqr', 'unpaid', 'pending', 30000, 135000, '2026-11-08 16:45:00');


INSERT INTO order_items (order_id, product_id, vendor_id, product_name, unit_price, quantity, status) VALUES
(1, 1, 1, 'Bột Mì Đa Dụng (All-purpose Flour)', 28000, 3, 'delivered'),
(1, 2, 1, 'Bột Mì Số 11 (Bread Flour)', 32000, 1, 'delivered'),
(2, 5, 1, 'Bơ Lạt Anchor', 65000, 1, 'shipped'),
(2, 11, 2, 'Đường Nâu Muscovado', 38000, 2, 'shipped'),
(3, 3, 1, 'Đường Cát Trắng', 24000, 2, 'processing'),
(3, 13, 2, 'Phới Đánh Trứng Inox', 55000, 2, 'pending'),
(4, 12, 2, 'Kem Tươi Whipping Cream', 135000, 1, 'pending');

UPDATE orders SET total_amount = 30000 + 65000 + 76000 WHERE id = 2;
UPDATE orders SET total_amount = 30000 + 48000 + 110000 WHERE id = 3;
UPDATE orders SET total_amount = 30000 + 135000 WHERE id = 4;
UPDATE orders SET total_amount = 30000 + 84000 + 32000 WHERE id = 1;

INSERT INTO order_status_histories (order_id, from_status, to_status, changed_by, created_at) VALUES
(1, NULL, 'pending', 3, '2026-10-20 10:00:00'),
(1, 'pending', 'processing', 1, '2026-10-20 11:00:00'),
(1, 'processing', 'shipped', 1, '2026-10-21 08:00:00'),
(1, 'shipped', 'delivered', 1, '2026-10-23 15:00:00'),
(2, NULL, 'pending', 3, '2026-10-25 14:30:00'),
(2, 'pending', 'processing', 1, '2026-10-25 15:00:00'),
(2, 'processing', 'shipped', 1, '2026-10-26 09:00:00'),
(3, NULL, 'pending', 3, '2026-11-03 09:15:00'),
(3, 'pending', 'processing', 1, '2026-11-04 08:00:00'),
(4, NULL, 'pending', 3, '2026-11-08 16:45:00');

-- Reviews only on products from a delivered order (order 1) plus seeded ratings for demo
INSERT INTO reviews (user_id, product_id, rating, comment) VALUES
(3, 1, 5, 'Bột mịn, làm bánh bông lan rất xốp.'),
(3, 2, 5, 'Nở rất tốt, làm bánh mì mềm.'),
(2, 1, 4, 'Chất lượng ổn, đóng gói cẩn thận.'),
(1, 3, 4, 'Đường tơi, dễ tan.'),
(1, 5, 5, 'Bơ thơm, béo ngậy.'),
(2, 11, 5, 'Vị caramel rất đậm, làm cookie tuyệt vời.');

-- Extra delivered orders (history for charts)
INSERT INTO orders (id, order_code, user_id, recipient_name, shipping_phone, shipping_address, payment_method, payment_status, status, shipping_fee, total_amount, created_at) VALUES
(5, 'BH20260615005', 3, 'Nguyễn Văn A', '0901234567', '12 Trần Hưng Đạo, Quận 5, TP.HCM', 'cod', 'paid', 'delivered', 30000, 170000, '2026-06-15 10:00:00'),
(6, 'BH20260720006', 3, 'Nguyễn Văn A', '0901234567', '12 Trần Hưng Đạo, Quận 5, TP.HCM', 'vietqr', 'paid', 'delivered', 30000, 300000, '2026-07-20 10:00:00'),
(7, 'BH20260818007', 3, 'Nguyễn Văn A', '0901234567', '12 Trần Hưng Đạo, Quận 5, TP.HCM', 'cod', 'paid', 'delivered', 30000, 205000, '2026-08-18 10:00:00'),
(8, 'BH20260910008', 3, 'Nguyễn Văn A', '0901234567', '12 Trần Hưng Đạo, Quận 5, TP.HCM', 'vietqr', 'paid', 'delivered', 30000, 250000, '2026-09-10 10:00:00'),
(9, 'BH20261005009', 3, 'Nguyễn Văn A', '0901234567', '12 Trần Hưng Đạo, Quận 5, TP.HCM', 'cod', 'paid', 'delivered', 30000, 225000, '2026-10-05 10:00:00');

INSERT INTO order_items (order_id, product_id, vendor_id, product_name, unit_price, quantity, status) VALUES
(5, 1, 1, 'Bột Mì Đa Dụng (All-purpose Flour)', 28000, 5, 'delivered'),
(6, 12, 2, 'Kem Tươi Whipping Cream', 135000, 2, 'delivered'),
(7, 8, 1, 'Cân Điện Tử Nhà Bếp 5kg', 175000, 1, 'delivered'),
(8, 14, 2, 'Tinh Chất Vani Nguyên Chất', 220000, 1, 'delivered'),
(9, 5, 1, 'Bơ Lạt Anchor', 65000, 3, 'delivered');

INSERT INTO order_status_histories (order_id, from_status, to_status, changed_by, created_at)
SELECT id, 'shipped', 'delivered', 1, created_at FROM orders WHERE id BETWEEN 5 AND 9;

-- Align timestamps: items follow their order; delivered 2 days after order
UPDATE orders SET updated_at = DATE_ADD(created_at, INTERVAL 2 DAY);
UPDATE order_items oi JOIN orders o ON o.id = oi.order_id
SET oi.created_at = o.created_at, oi.updated_at = o.updated_at;
