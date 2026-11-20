# Ý tưởng Chatbot BakeHouse

## 1. Mục tiêu
Chatbot đóng vai **trợ lý tư vấn làm bánh**: người dùng nói mình muốn làm món gì / ngân sách bao nhiêu, chatbot gợi ý nguyên vật liệu và dụng cụ **có thật trong shop**, rồi đưa thẳng vào giỏ hàng. Mục đích là giúp người mới (không biết cần mua gì) mua nhanh hơn và mua đủ.

## 2. Chatbot hỗ trợ người dùng những gì

| # | Chức năng | Ví dụ người dùng hỏi | Chatbot trả lời / làm |
|---|---|---|---|
| 1 | **Gợi ý nguyên liệu theo món** | "Tôi muốn làm bánh bông lan" | Liệt kê bột, trứng, đường, bơ... kèm sản phẩm trong shop |
| 2 | **Gợi ý theo ngân sách** | "Làm cookie dưới 300k" | Chọn bộ sản phẩm có tổng giá không vượt ngân sách |
| 3 | **Gợi ý dụng cụ còn thiếu** | "Làm bánh cần dụng cụ gì?" | Khuôn, phới, cân, giấy nến... |
| 4 | **Tìm / so sánh sản phẩm** | "Bơ nào hợp làm croissant?" | Đề xuất 2–3 sản phẩm, nêu khác biệt (thương hiệu, giá, đánh giá) |
| 5 | **Thêm vào giỏ hàng** | "Thêm cả bộ này vào giỏ" | Nút "Thêm vào giỏ" ngay trong khung chat |
| 6 | **Tra cứu đơn hàng** (cần đăng nhập) | "Đơn của tôi tới đâu rồi?" | Trả trạng thái đơn gần nhất |
| 7 | **Giải đáp FAQ cửa hàng** | "Phí ship? Thanh toán VietQR thế nào?" | Trả lời theo trang thông tin của shop |
| 8 | **Chuyển liên hệ** | "Tôi muốn khiếu nại" | Hướng dẫn tới form Liên hệ |

Phạm vi nên làm trước (khớp với code hiện có): **1, 2, 3, 5**. Các mục còn lại là mở rộng.

## 3. Luồng hoạt động chung
```text
Người dùng nhập câu hỏi (lịch sử ≤10 lượt do trình duyệt giữ)
      │
      ▼
POST /api/chatbot/chat ──► ChatbotService (vòng lặp tối đa 8 lượt)
      │                         │
      │                  Azure OpenAI gpt-5-mini
      │                         │ tool-calling
      │             ┌───────────┼──────────────┐
      │      search_products  get_my_order_status  get_shop_info
      │      (embedding +     (user từ session)    (phí ship, thanh toán)
      │       cosine, từ DB)
      ▼
Trả về: lời nhắn + thẻ sản phẩm thật (id đã xác minh) + tổng tiền
      │
      ▼
Khung chat → "Thêm vào giỏ"
```

Cơ chế kiểm soát phía server:
- **Ngân sách:** server tự đọc ngân sách từ câu của khách ("dưới 300k", "1,5 triệu"…), nếu không có thì dùng dòng `BUDGET: <số>` AI ghi cạnh `IDS:`. Server cộng giá thật của các sản phẩm được chọn; nếu vượt thì yêu cầu AI chọn lại 1 lần, vẫn vượt thì thay lời nhắn bằng thông báo chuẩn của server. AI không được tự ghi tổng tiền, tổng hiển thị do server tính.
- **Tìm kiếm:** vector được nạp và chuẩn hoá 1 lần mỗi request; chỉ nhận kết quả có cosine ≥ `CHATBOT_MIN_SCORE`, không có thì AI được báo "shop chưa có".
- **Embedding luôn mới:** admin lưu sản phẩm → tự embed sản phẩm đó; sản phẩm chưa có vector được embed bù (tối đa 50/request) khi chatbot tìm kiếm. `php bin/embed-products.php` dùng để dựng lại hàng loạt.
- **Chống lạm dụng:** giới hạn theo session (20/10 phút), theo IP (40/10 phút) và toàn site (`CHATBOT_GLOBAL_LIMIT`/giờ), lưu ở `storage/cache`.
- **Lịch sử:** lượt `assistant` do client gửi lên chỉ được nhận nếu chính server đã trả ra trong session đó.
- **Thẻ sản phẩm:** ưu tiên dòng `IDS:`; nếu AI quên thì đối chiếu tên sản phẩm xuất hiện trong câu trả lời (vẫn chỉ trong tập tool đã trả về).
- **FAQ:** `src/Data/faq.php` là nguồn chung của trang /faq và tool `get_shop_info` (topic `faq`).

Triển khai bằng PHP thuần: `AzureOpenAiClient` (cURL), `ChatbotTools`, `ProductEmbeddingIndexer`, bảng `product_embeddings` (`database/init/003_chatbot.sql`). Sau khi import seed hoặc đổi sản phẩm, chạy `php bin/embed-products.php` để cập nhật vector. Cấu hình `AZURE_OPENAI_*` trong `.env`.

## 3b. Streaming và giao diện
- **Streaming:** `POST /api/chatbot/stream` trả Server-Sent Events qua `fetch` (cần CSRF nên không dùng `EventSource`). Sự kiện: `status` (đang tìm…/tra cứu…), `delta` (từng token), `reset` (bỏ phần đã hiện, vd khi phải chọn lại vì vượt ngân sách), `final` (câu trả lời đã làm sạch + thẻ sản phẩm + tổng + chữ ký), `error`, `done`. Endpoint cũ `POST /api/chatbot/chat` (JSON) là fallback cho trình duyệt không đọc được stream.
- **Server:** `AzureOpenAiClient::chat(..., $onDelta)` đọc stream của Azure và ghép tool call; `StreamTextFilter` không cho dòng `IDS:`/`BUDGET:` lọt ra màn hình; `Response::stream()` tắt output buffering và flush từng sự kiện; controller gọi `session_write_close()` trước khi stream để không khoá session; các truy vấn tìm sản phẩm trong cùng một lượt được embed chung 1 request (`ChatbotTools::warmUp`).
- **Xác thực lịch sử:** mỗi câu trả lời kèm `sig` = HMAC theo khoá riêng của session; lượt `assistant` do client gửi lên không có sig hợp lệ sẽ bị bỏ.
- **Client:** `public/assets/js/chatbot.js` + `public/assets/css/chatbot.css`. Hiệu ứng gõ mượt (xả token theo `requestAnimationFrame`), markdown nhẹ có escape HTML, nút Dừng/Thử lại/Chép, thẻ sản phẩm kèm "Thêm tất cả", lưu hội thoại trong `sessionStorage`, mobile là sheet toàn màn hình (xử lý bàn phím qua `visualViewport`).

## 4. Dữ liệu chatbot dùng
- `products`, `categories`, `brands`, `tags` + `product_embeddings`: tìm sản phẩm theo ngữ nghĩa.
- `reviews`: ưu tiên sản phẩm được đánh giá tốt.
- `orders`, `order_status_histories`: tra cứu đơn (chỉ của chính người dùng đăng nhập).
- Trang thông tin shop: nguồn cho FAQ.

## 5. Nguyên tắc
- **Chỉ gợi ý sản phẩm có thật và còn hàng**, không bịa tên hay giá.
- Giá và tồn kho luôn lấy từ DB, không để AI tự sinh.
- Chỉ trả dữ liệu đơn hàng của đúng người đang đăng nhập.
- Ngoài phạm vi (không liên quan làm bánh/shop) thì từ chối lịch sự và gợi ý quay lại chủ đề.

## 6. Lộ trình gợi ý
1. **Cơ bản:** AI nhận món + ngân sách → gợi ý sản phẩm thật trong shop.
2. **Tiếp theo:** nút thêm giỏ trong chat, gợi ý dụng cụ đi kèm.
3. **Mở rộng:** FAQ và tra cứu đơn hàng.
4. **Nâng cao:** nhớ ngữ cảnh hội thoại, so sánh sản phẩm.
