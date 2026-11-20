<button class="bh-chat-fab" id="bhChatToggle" type="button" aria-label="Mở trợ lý BakeHouse" aria-expanded="false" aria-controls="bhChat">
    <span class="bh-chat-fab__icon bh-chat-fab__icon--open" aria-hidden="true"><i class="bi bi-chat-dots-fill"></i></span>
    <span class="bh-chat-fab__icon bh-chat-fab__icon--close" aria-hidden="true"><i class="bi bi-chevron-down"></i></span>
    <span class="bh-chat-fab__dot" aria-hidden="true"></span>
</button>

<section class="bh-chat" id="bhChat" role="dialog" aria-modal="false" aria-labelledby="bhChatTitle" inert>
    <header class="bh-chat__head">
        <div class="bh-chat__avatar" aria-hidden="true"><i class="bi bi-stars"></i></div>
        <div class="bh-chat__who">
            <h2 class="bh-chat__title" id="bhChatTitle">Trợ lý BakeHouse</h2>
            <p class="bh-chat__status" id="bhChatStatus"><span class="bh-chat__pulse" aria-hidden="true"></span><span data-chat-status>Trực tuyến</span></p>
        </div>
        <div class="bh-chat__tools">
            <button type="button" class="bh-chat__tool" data-chat-new title="Cuộc trò chuyện mới" aria-label="Bắt đầu cuộc trò chuyện mới"><i class="bi bi-pencil-square"></i></button>
            <button type="button" class="bh-chat__tool bh-chat__tool--expand" data-chat-expand title="Mở rộng" aria-label="Mở rộng cửa sổ chat" aria-pressed="false"><i class="bi bi-arrows-angle-expand"></i></button>
            <button type="button" class="bh-chat__tool" data-chat-close title="Thu nhỏ" aria-label="Đóng trợ lý"><i class="bi bi-x-lg"></i></button>
        </div>
    </header>

    <div class="bh-chat__scroller" id="bhChatScroll">
        <div class="bh-chat__list" id="bhChatList" role="log" aria-live="polite" aria-relevant="additions"></div>
        <button type="button" class="bh-chat__jump" id="bhChatJump" hidden><i class="bi bi-arrow-down"></i> Tin mới</button>
    </div>

    <form class="bh-chat__composer" id="bhChatForm" autocomplete="off">
        <div class="bh-chat__box">
            <textarea class="bh-chat__input" id="bhChatInput" name="message" rows="1" maxlength="500" placeholder="Nhập câu hỏi của bạn…" aria-label="Nhập câu hỏi cho trợ lý"></textarea>
            <button type="submit" class="bh-chat__send" id="bhChatSend" aria-label="Gửi câu hỏi" disabled>
                <i class="bi bi-arrow-up" data-icon="send"></i><i class="bi bi-stop-fill" data-icon="stop"></i>
            </button>
        </div>
        <p class="bh-chat__hint" hidden></p>
    </form>
</section>
