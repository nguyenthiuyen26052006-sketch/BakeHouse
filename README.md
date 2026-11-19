# BakeHouse

Website bán **nguyên vật liệu & dụng cụ làm bánh** — bài tập lớn môn PHP.
PHP 8.3 OOP thuần (MVC tự xây, PSR-12/PSR-4), PDO, MySQL 8.4, Bootstrap 5 + Vanilla JS, chạy bằng XAMPP (Apache + MySQL).

## Chạy dự án
1. Copy `BakeHouse` vào `C:\xampp\htdocs\`.
2. Tạo file môi trường: `cp .env.example .env` (chỉnh `DB_*` theo MySQL của XAMPP nếu cần, mặc định user `root`, không mật khẩu).
3. Mở phpMyAdmin (`http://localhost/phpmyadmin`), tạo database `bakehouse`, import lần lượt `database/init/001_schema.sql` và `database/init/002_seed.sql`.
4. Bật Apache + MySQL trong XAMPP Control Panel.

| Dịch vụ | URL |
|---|---|
| Client | http://localhost/BakeHouse/public |
| Admin | http://localhost/BakeHouse/public/admin/login |
| phpMyAdmin | http://localhost/phpmyadmin |

- Reset DB: xoá & tạo lại database `bakehouse` trong phpMyAdmin, import lại 2 file SQL ở `database/init`.
- Xem lỗi: `storage/logs/app.log`.

## Tài khoản demo
| Vai trò | Tài khoản | Mật khẩu |
|---|---|---|
| Admin | admin@bakehouse.vn | Admin@123 |
| User | user@bakehouse.vn | User@123 |

## Cấu trúc
```text
src/Core          Router, Request, Response, View, Session, Csrf, Auth, Database, Validator, Paginator, FileUploader
src/Controllers   Client / Admin / Api (mỏng)
src/Services      AuthService, CartService, OrderService, ReviewService, VietQrService, ChatbotService (+ Chatbot/*)
src/Repositories  toàn bộ SQL (prepared statements)
src/Enums         OrderStatus, PaymentMethod, UserRole
src/Policies      ProductPolicy
src/Views         layouts, partials, client, admin, errors
routes            web.php, admin.php, api.php
database/init     001_schema.sql, 002_seed.sql
docs              erd.md, usecase.md (Mermaid)
```

## Chức năng chính
- **Client:** đăng ký/đăng nhập/quên mật khẩu, hồ sơ + ảnh đại diện, duyệt & tìm (tên, thương hiệu), lọc danh mục/giá, sắp xếp, phân trang giữ query string, giỏ hàng AJAX, đặt hàng/Mua ngay (trừ kho trong transaction), COD & VietQR, stepper theo dõi đơn, đánh giá (chỉ khi đã nhận hàng, 1 lần/sản phẩm), trang thông tin, liên hệ, chatbot gợi ý nguyên liệu (mock).
- **Admin:** quản lý toàn bộ shop (một admin duy nhất, hoặc nhiều tài khoản admin dùng chung quyền), CRUD sản phẩm (ảnh chính + gallery có preview), danh mục/thương hiệu/tag/banner, người dùng, đơn hàng (+ xác nhận VietQR), đánh giá, liên hệ, dashboard Chart.js.

## Chatbot
`POST /api/chatbot/suggest` → `ChatbotService` → `ChatbotProviderInterface`. Mặc định `CHATBOT_PROVIDER=mock` (chọn sản phẩm thật trong DB theo từ khóa món + ngân sách). `AzureOpenAiChatbotProvider` là khung chờ gắn Azure OpenAI (`AZURE_OPENAI_*`); khi lỗi sẽ tự quay về mock.

## Ảnh
Sản phẩm seed chưa có ảnh nên hiển thị ảnh placeholder; upload ảnh trong Admin (lưu ở `public/uploads/`). Có thể đặt ảnh nền hero tại `public/assets/img/hero-bread.jpg`. Cột `image_path` cũng chấp nhận URL `https://...` nếu muốn dùng ảnh ngoài.

## Git (Gitflow)
Nhánh `main`, `develop`, `feature/<mô-tả>`; merge bằng Pull Request; commit theo Conventional Commits (`feat(cart): ...`, `fix(auth): ...`).
