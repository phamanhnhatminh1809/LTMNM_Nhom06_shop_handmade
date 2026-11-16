<div id="aiChatWidget"
     class="ai-chat-widget"
     data-ask-url="{{ route('ai.chat.ask') }}">

    <!-- Cửa sổ Chatbox AI -->
    <section id="aiChatPanel"
             class="ai-chat-panel"
             aria-label="Trợ lý AI gợi ý sản phẩm"
             aria-hidden="true">
        <header class="ai-chat-panel__header">
            <div class="ai-chat-panel__brand">
                <span class="ai-chat-panel__avatar" aria-hidden="true">
                    <i class="bi bi-stars"></i>
                </span>
                <div>
                    <h3 class="ai-chat-panel__title">Trợ lý Shop Handmade AI</h3>
                    <p class="ai-chat-panel__subtitle">Tư vấn &amp; gợi ý sản phẩm trong cửa hàng</p>
                </div>
            </div>
            <div class="ai-chat-panel__actions">
                <button type="button"
                        id="aiChatClearBtn"
                        class="ai-chat-panel__icon-btn"
                        title="Xóa cuộc trò chuyện"
                        aria-label="Xóa cuộc trò chuyện">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </button>
                <button type="button"
                        id="aiChatCloseBtn"
                        class="ai-chat-panel__icon-btn"
                        title="Thu gọn"
                        aria-label="Đóng khung chat">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        </header>

        <!-- Gợi ý câu hỏi nhanh -->
        <div class="ai-chat-panel__suggestions" aria-label="Gợi ý câu hỏi nhanh">
            <button type="button" class="ai-chat-chip" data-ai-prompt="Gợi ý quà tặng handmade dưới 200k">
                Quà tặng dưới 200k
            </button>
            <button type="button" class="ai-chat-chip" data-ai-prompt="Những sản phẩm nào đang bán chạy nhất?">
                Sản phẩm bán chạy
            </button>
            <button type="button" class="ai-chat-chip" data-ai-prompt="Shop có túi xách hoặc móc khóa len nào còn hàng không?">
                Túi &amp; móc khóa len
            </button>
            <button type="button" class="ai-chat-chip" data-ai-prompt="Chính sách vận chuyển và phí ship của shop như thế nào?">
                Phí ship &amp; đổi trả
            </button>
        </div>

        <!-- Danh sách tin nhắn -->
        <div id="aiChatMessages" class="ai-chat-panel__messages" role="log" aria-live="polite">
            <div class="ai-chat-msg ai-chat-msg--bot">
                <div class="ai-chat-msg__bubble">
                    Xin chào! Mình là <strong>Trợ lý AI của Shop Handmade</strong>. Bạn đang tìm món đồ thủ công nào (túi len, móc khóa, gấu bông, nến thơm, quà tặng...) hoặc cần tư vấn mức giá, tồn kho? Hãy nhắn cho mình nhé!
                </div>
            </div>
        </div>

        <!-- Form nhập câu hỏi -->
        <form id="aiChatForm" class="ai-chat-panel__form">
            <input type="text"
                   id="aiChatInput"
                   class="ai-chat-panel__input"
                   placeholder="Hỏi gợi ý sản phẩm handmade..."
                   maxlength="500"
                   autocomplete="off"
                   required>
            <button type="submit"
                    id="aiChatSendBtn"
                    class="ai-chat-panel__send"
                    aria-label="Gửi câu hỏi">
                <i class="bi bi-send-fill"></i>
            </button>
        </form>
    </section>

    <!-- Nút mở Chatbox nổi -->
    <button type="button"
            id="aiChatToggleBtn"
            class="ai-chat-toggle"
            aria-label="Mở trợ lý AI gợi ý sản phẩm"
            aria-expanded="false"
            aria-controls="aiChatPanel">
        <i class="bi bi-stars ai-chat-toggle__icon"></i>
        <span class="ai-chat-toggle__label">Tư vấn AI</span>
    </button>
</div>
