# BakeHouse

Website bán **nguyên vật liệu & dụng cụ làm bánh** — bài tập lớn môn PHP.
PHP 8.3 OOP thuần (MVC tự xây), PDO, MySQL, Bootstrap 5, chạy bằng XAMPP.

## Chức năng
- **Client:** đăng ký/đăng nhập, duyệt & tìm sản phẩm, giỏ hàng, đặt hàng (COD / VietQR), theo dõi đơn, đánh giá, liên hệ, chatbot gợi ý nguyên liệu.
- **Admin (tập trung bán hàng):** Tổng quan doanh số (lọc theo kỳ, in báo cáo), Sản phẩm (giá/tồn kho sửa nhanh), Đơn hàng (duyệt và giao hàng theo từng bước), Đánh giá (phản hồi khách), cùng Danh mục và Thương hiệu. Quản lý người dùng dành cho super admin sẽ làm sau; banner, tag và đường dẫn (URL) do dev quản lý. Form liên hệ của khách vẫn lưu tin nhắn nhưng admin chưa đọc được.

## Tài khoản demo
| Vai trò | Tài khoản | Mật khẩu |
|---|---|---|
| Admin | admin@bakehouse.vn | Admin@123 |
| User | user@bakehouse.vn | User@123 |

## Phân quyền
Một trình duyệt chỉ đăng nhập **một tài khoản, một vai trò**; muốn đổi vai trò phải đăng xuất.

| Khu vực | Khách (chưa đăng nhập) | `user` | `admin` |
|---|---|---|---|
| Website công khai (trang chủ, sản phẩm, blog, FAQ, liên hệ) | ✔ | ✔ | ✘ chuyển `/admin` |
| Giỏ hàng, thanh toán, đơn hàng, tài khoản, đánh giá, chatbot | ✘ chuyển `/login` | ✔ | ✘ chuyển `/admin` |
| `/login`, `/register`, `/forgot-password` | ✔ | ✘ chuyển `/` | ✘ chuyển `/admin` |
| `/admin/login` | ✔ | ✘ chuyển `/` (cần đăng xuất trước) | ✘ chuyển `/admin` |
| Khu quản trị `/admin/*` | ✘ chuyển `/admin/login` | ✘ 403 | ✔ |

Admin đăng nhập ở `/login` cũng được chuyển tới `/admin`. Quy tắc nằm ở `NotAdminMiddleware` (chặn admin khỏi website khách), `AuthMiddleware`/`GuestMiddleware` và `AdminMiddleware`, cùng `Auth::isCustomer()` / `Auth::homeUrl()`.

Ngoài ra có 10 tài khoản khách khác (vd `lan.pham@gmail.com`, `minh.tran@gmail.com`…) cùng mật khẩu `User@123`; một tài khoản bị khoá để demo quản lý người dùng.

## Dữ liệu demo (seed)
Bộ dữ liệu mô phỏng shop đang vận hành: 66 sản phẩm / 8 danh mục / 13 thương hiệu, 12 người dùng, ~47 đơn hàng trải 12 tháng (đủ 4 trạng thái), ~75 đánh giá, giỏ hàng và liên hệ mẫu.

```bash
cp .env.example .env        # cấu hình DB_*, (tuỳ chọn) AZURE_STORAGE_*, AZURE_OPENAI_*
php bin/seed.php            # xoá toàn bộ bảng, nạp lại schema + dữ liệu, tải ảnh, tạo embedding chatbot
php bin/seed.php --no-images --no-embed   # chỉ dựng lại CSDL
```

- **Cảnh báo:** `bin/seed.php` xoá mọi bảng trong CSDL đã cấu hình trước khi nạp lại.
- Ảnh sản phẩm nằm ở `database/seed-images/` (nguồn và giấy phép trong `CREDITS.md`). Có cấu hình Azure Storage thì ảnh được tải lên Blob, nếu không sẽ chép vào `public/uploads/products/seed/`. Một số sản phẩm chủ động để trống ảnh (hiện ảnh placeholder).
- `database/init/002_seed.sql` được **sinh tự động** từ `database/seed-src/` (`catalog.py` chứa danh mục/sản phẩm, `generate.py` sinh người dùng, đơn hàng, đánh giá theo seed cố định). Muốn đổi dữ liệu: sửa các file đó rồi chạy `python3 database/seed-src/generate.py`.

