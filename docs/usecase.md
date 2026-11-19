# Use case — BakeHouse

```mermaid
flowchart LR
    Guest([Khách]) --> UC1[Xem & tìm sản phẩm]
    Guest --> UC2[Đăng ký / Đăng nhập / Quên mật khẩu]
    Guest --> UC3[Gửi liên hệ]
    Guest --> UC4[Dùng chatbot gợi ý nguyên liệu]

    User([Người dùng]) --> UC1
    User --> UC5[Quản lý hồ sơ & ảnh đại diện]
    User --> UC6[Giỏ hàng AJAX]
    User --> UC7[Đặt hàng / Mua ngay]
    UC7 -.-> UC8[Thanh toán COD hoặc VietQR]
    User --> UC9[Theo dõi đơn bằng stepper]
    User --> UC10[Đánh giá sản phẩm đã nhận]

    Admin([Admin]) --> A1[Đăng nhập quản trị]
    Admin --> A2[CRUD toàn bộ nguyên vật liệu + upload ảnh]
    Admin --> A3[CRUD danh mục / thương hiệu / tag / banner]
    Admin --> A4[Xử lý đơn: chuyển trạng thái đơn]
    Admin --> A5[Xác nhận đã nhận tiền VietQR]
    Admin --> A6[Quản lý người dùng: đổi vai trò, khóa]
    Admin --> A7[Duyệt đánh giá: ẩn / xóa]
    Admin --> A8[Xử lý liên hệ]
    Admin --> A9[Dashboard & thống kê]
```

## Luồng đặt hàng
```mermaid
sequenceDiagram
    actor U as Người dùng
    participant W as Website
    participant DB as MySQL
    actor A as Admin
    U->>W: Thêm vào giỏ (AJAX)
    U->>W: Checkout (COD / VietQR)
    W->>DB: BEGIN; khóa sản phẩm; kiểm tồn; trừ kho; tạo order + items + history; COMMIT
    W-->>U: Trang đơn (VietQR: ảnh QR đúng số tiền, nội dung = mã đơn)
    A->>W: Chuyển trạng thái đơn (chỉ tiến)
    W->>DB: Cập nhật trạng thái đơn + các item, ghi lịch sử
    A->>W: Xác nhận đã nhận tiền (VietQR)
    U->>W: Sau khi Delivered → đánh giá (1 lần / sản phẩm)
```
