# Dựng lại CSDL trên máy mới

`.env` đã cấu hình sẵn. Không import SQL bằng tay.

1. Bật Apache và MySQL trong XAMPP.
2. Tạo CSDL rỗng (tên theo `DB_DATABASE` trong `.env`, mặc định `bakehouse`):
   ```
   mysql -u root -e "CREATE DATABASE IF NOT EXISTS bakehouse CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
   ```
3. Seed (xóa mọi bảng rồi nạp lại schema, dữ liệu, ảnh, embedding chatbot):
   ```
   php bin/seed.php
   ```
4. Kiểm tra: mở `/`, đăng nhập `/login` bằng `admin@bakehouse.vn` / `Admin@123` → vào `/admin`.

Lỗi `Unknown database`: chưa làm bước 2.
