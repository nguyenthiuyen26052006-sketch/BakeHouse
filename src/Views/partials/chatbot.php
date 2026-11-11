<button class="bh-chat-toggle" id="bhChatToggle" type="button" aria-label="Trợ lý làm bánh"><i class="bi bi-chat-dots-fill"></i></button>
<div class="bh-chat" id="bhChat" role="dialog" aria-label="Trợ lý làm bánh">
    <div class="bh-chat__head">
        <strong><i class="bi bi-stars me-1"></i> Trợ lý BakeHouse</strong>
        <button type="button" class="btn-close btn-close-white" data-chat-close aria-label="Đóng"></button>
    </div>
    <div class="bh-chat__body">
        <div class="bh-chat__msg bh-chat__msg--bot">Xin chào! Hãy cho mình biết món bạn muốn làm và ngân sách, mình sẽ gợi ý nguyên vật liệu nhé 🍞</div>
    </div>
    <form class="bh-chat__form" autocomplete="off">
        <input class="form-control" name="dish" placeholder="Bạn muốn làm món gì? (vd: bánh mì, cookie)" maxlength="120" required>
        <div class="d-flex gap-2">
            <input class="form-control" name="budget" inputmode="numeric" placeholder="Ngân sách (₫)">
            <button class="bh-btn bh-btn--primary" type="submit">Gợi ý</button>
        </div>
    </form>
</div>
