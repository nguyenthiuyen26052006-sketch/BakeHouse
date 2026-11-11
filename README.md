# BakeHouse

Website bán **nguyên vật liệu & dụng cụ làm bánh** — bài tập lớn môn PHP.
PHP 8.3 OOP thuần (MVC tự xây, PSR-12/PSR-4), PDO, MySQL 8.4, Bootstrap 5 + Vanilla JS, chạy bằng Docker Compose.

## Chạy dự án
```bash
cp .env.example .env
chmod -R 777 public/uploads storage/logs   # để container PHP ghi được ảnh & log
docker compose up -d --build
```
| Dịch vụ | URL |
|---|---|
| Client | http://localhost:8080 |
| Admin | http://localhost:8080/admin/login |
| phpMyAdmin | http://localhost:8081 (root / `DB_ROOT_PASSWORD`) |
| MySQL (từ máy host) | `127.0.0.1:3307` |

- Reset DB và nạp lại seed: `docker compose down -v && docker compose up -d --build`
- Xem lỗi: `docker compose logs -f app nginx` và `storage/logs/app.log`
- Email quên mật khẩu (dev) được ghi vào `storage/logs/mail.log`

> Chạy trong WSL2: để dự án ở filesystem Linux (không phải `/mnt/c`) và bật Docker Desktop → Settings → Resources → WSL integration.

## Tài khoản demo
| Vai trò | Tài khoản | Mật khẩu |
|---|---|---|
| Admin 1 | admin1@bakehouse.vn | Admin@123 |
| Admin 2 | admin2@bakehouse.vn | Admin@123 |
| User | user@bakehouse.vn | User@123 |

## Cấu trúc
```text
src/Core          Router, Request, Response, View, Session, Csrf, Auth, Database, Validator, Paginator, FileUploader
src/Controllers   Client / Admin / Api (mỏng)
src/Services      AuthService, CartService, OrderService, ReviewService, VietQrService, ChatbotService (+ Chatbot/*)
src/Repositories  toàn bộ SQL (prepared statements)
src/Enums         OrderStatus, PaymentMethod, UserRole
src/Policies      ProductPolicy (multi-vendor)
src/Views         layouts, partials, client, admin, errors
routes            web.php, admin.php, api.php
database/init     001_schema.sql, 002_seed.sql
docs              erd.md, usecase.md (Mermaid)
```

## Chức năng chính
- **Client:** đăng ký/đăng nhập/quên mật khẩu, hồ sơ + ảnh đại diện, duyệt & tìm (tên, thương hiệu), lọc danh mục/giá, sắp xếp, phân trang giữ query string, giỏ hàng AJAX, đặt hàng/Mua ngay (trừ kho trong transaction), COD & VietQR, stepper theo dõi đơn, đánh giá (chỉ khi đã nhận hàng, 1 lần/sản phẩm), trang thông tin, liên hệ, chatbot gợi ý nguyên liệu (mock).
- **Admin:** multi-vendor (chỉ sửa sản phẩm do mình tạo, xử lý item của mình), CRUD sản phẩm (ảnh chính + gallery có preview), danh mục/thương hiệu/tag/banner, người dùng, đơn hàng (+ xác nhận VietQR), đánh giá, liên hệ, dashboard Chart.js.

## Chatbot
`POST /api/chatbot/suggest` → `ChatbotService` → `ChatbotProviderInterface`. Mặc định `CHATBOT_PROVIDER=mock` (chọn sản phẩm thật trong DB theo từ khóa món + ngân sách). `AzureOpenAiChatbotProvider` là khung chờ gắn Azure OpenAI (`AZURE_OPENAI_*`); khi lỗi sẽ tự quay về mock.

## Ảnh
Sản phẩm seed chưa có ảnh nên hiển thị ảnh placeholder; upload ảnh trong Admin (lưu ở `public/uploads/`). Có thể đặt ảnh nền hero tại `public/assets/img/hero-bread.jpg`. Cột `image_path` cũng chấp nhận URL `https://...` nếu muốn dùng ảnh ngoài.

## Git (Gitflow)
Nhánh `main`, `develop`, `feature/<mô-tả>`; merge bằng Pull Request; commit theo Conventional Commits (`feat(cart): ...`, `fix(auth): ...`).
