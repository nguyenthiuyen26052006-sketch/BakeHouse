# ERD — BakeHouse

```mermaid
erDiagram
    users ||--o| user_profiles : "1-1 có hồ sơ"
    users ||--o| carts : "1-1 có giỏ hàng"
    users ||--o{ orders : "1-N đặt"
    users ||--o{ reviews : "1-N viết"
    users ||--o{ password_resets : "1-N"
    categories ||--o{ products : "1-N"
    brands ||--o{ products : "1-N"
    products ||--o{ product_images : "1-N"
    products ||--o{ reviews : "1-N"
    products }o--o{ tags : "N-N qua product_tags"
    products }o--o{ carts : "N-N qua cart_items"
    products }o--o{ orders : "N-N qua order_items"
    orders ||--o{ order_items : "1-N"
    orders ||--o{ order_status_histories : "1-N"

    users { bigint id PK
        varchar email UK
        varchar phone UK
        varchar password_hash
        enum role "user|admin"
        tinyint is_active }
    user_profiles { bigint id PK
        bigint user_id UK
        varchar full_name
        varchar shipping_phone
        varchar shipping_address
        varchar avatar_path }
    categories { bigint id PK
        varchar name
        varchar slug UK
        text description
        varchar image_path
        tinyint is_active }
    brands { bigint id PK
        varchar name
        varchar slug UK }
    tags { bigint id PK
        varchar name
        varchar slug UK }
    products { bigint id PK
        bigint category_id FK
        bigint brand_id FK
        varchar name
        varchar slug UK
        int price
        int stock_quantity
        tinyint is_featured
        tinyint is_active
        decimal rating_avg
        int rating_count }
    product_images { bigint id PK
        bigint product_id FK
        varchar image_path
        tinyint is_primary }
    product_tags { bigint product_id PK
        bigint tag_id PK }
    carts { bigint id PK
        bigint user_id UK }
    cart_items { bigint id PK
        bigint cart_id FK
        bigint product_id FK
        int quantity }
    orders { bigint id PK
        varchar order_code UK
        bigint user_id FK
        enum payment_method "cod|vietqr"
        enum payment_status "unpaid|paid"
        enum status "pending..delivered"
        int shipping_fee
        int total_amount }
    order_items { bigint id PK
        bigint order_id FK
        bigint product_id FK
        varchar product_name
        int unit_price
        int quantity
        enum status }
    order_status_histories { bigint id PK
        bigint order_id FK
        enum from_status
        enum to_status
        bigint changed_by FK }
    reviews { bigint id PK
        bigint user_id FK
        bigint product_id FK
        tinyint rating
        text comment
        tinyint is_hidden }
    banners { bigint id PK
        varchar title
        varchar image_path
        varchar link_url
        tinyint is_active }
    contacts { bigint id PK
        varchar full_name
        text message
        enum status "new|read|replied" }
    password_resets { bigint id PK
        bigint user_id FK
        char token_hash
        datetime expires_at }
```

## Quan hệ
| Loại | Bảng |
|---|---|
| 1-1 | `users`–`user_profiles`, `users`–`carts` |
| 1-N | `categories`/`brands` → `products`; `users` → `orders`; `orders` → `order_items`; `products` → `reviews` |
| N-N | `products`–`tags` (`product_tags`), `products`–`carts` (`cart_items`), `products`–`orders` (`order_items`) |

## Quy tắc nghiệp vụ gắn với dữ liệu
- Một admin duy nhất (hoặc nhiều tài khoản role `admin` dùng chung) quản lý toàn bộ sản phẩm và đơn hàng của shop.
- `products.rating_avg/rating_count` được tính lại từ các `reviews` không bị ẩn.
