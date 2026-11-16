/**
 * Trợ lý AI Chatbox gợi ý sản phẩm (Google Gemini Flash)
 */
document.addEventListener('DOMContentLoaded', () => {
    const widget = document.getElementById('aiChatWidget');
    if (!widget) return;

    const askUrl = widget.getAttribute('data-ask-url') || '/ai-chat/ask';
    const panel = document.getElementById('aiChatPanel');
    const toggleBtn = document.getElementById('aiChatToggleBtn');
    const closeBtn = document.getElementById('aiChatCloseBtn');
    const clearBtn = document.getElementById('aiChatClearBtn');
    const messagesBox = document.getElementById('aiChatMessages');
    const form = document.getElementById('aiChatForm');
    const input = document.getElementById('aiChatInput');
    const sendBtn = document.getElementById('aiChatSendBtn');
    const suggestionChips = widget.querySelectorAll('.ai-chat-chip');

    const STORAGE_KEY = 'shop_handmade_ai_chat_v1';
    const MAX_STORED_MESSAGES = 20;
    const WELCOME_HTML = 'Xin chào! Mình là <strong>Trợ lý AI của Shop Handmade</strong>. Bạn đang tìm món đồ thủ công nào (túi len, móc khóa, gấu bông, nến thơm, quà tặng...) hoặc cần tư vấn mức giá, tồn kho? Hãy nhắn cho mình nhé!';

    let isLoading = false;
    let storedMessages = [];

    function getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = String(text ?? '');
        return div.innerHTML;
    }

    function formatReplyHtml(text) {
        return escapeHtml(text).replace(/\n/g, '<br>');
    }

    function scrollToBottom() {
        if (!messagesBox) return;
        messagesBox.scrollTop = messagesBox.scrollHeight;
    }

    function saveState(isOpen) {
        try {
            const payload = {
                isOpen: Boolean(isOpen),
                messages: storedMessages.slice(-MAX_STORED_MESSAGES),
            };
            sessionStorage.setItem(STORAGE_KEY, JSON.stringify(payload));
        } catch (_) {
            // Bỏ qua nếu sessionStorage bị vô hiệu hóa
        }
    }

    function setOpenState(open) {
        if (!panel || !toggleBtn) return;
        panel.classList.toggle('is-open', open);
        panel.setAttribute('aria-hidden', open ? 'false' : 'true');
        toggleBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
        saveState(open);

        if (open) {
            scrollToBottom();
            setTimeout(() => input?.focus(), 120);
        }
    }

    function buildProductCardElement(product) {
        const card = document.createElement('div');
        card.className = 'ai-chat-product-card';

        const img = document.createElement('img');
        img.className = 'ai-chat-product-card__thumb';
        img.src = product.image_url || '/assets/images/placeholder.svg';
        img.alt = product.name || 'Sản phẩm handmade';
        img.loading = 'lazy';
        img.onerror = function () {
            this.onerror = null;
            this.src = '/assets/images/placeholder.svg';
        };

        const info = document.createElement('div');
        info.className = 'ai-chat-product-card__info';

        const titleLink = document.createElement('a');
        titleLink.className = 'ai-chat-product-card__title';
        titleLink.href = product.url || '#';
        titleLink.textContent = product.name || '';

        const meta = document.createElement('div');
        meta.className = 'ai-chat-product-card__meta';

        const priceSpan = document.createElement('span');
        priceSpan.className = 'ai-chat-product-card__price';
        priceSpan.textContent = product.price_formatted || '';

        const stockSpan = document.createElement('span');
        stockSpan.className = `ai-chat-product-card__stock ${product.in_stock ? 'is-in' : 'is-out'}`;
        stockSpan.textContent = product.in_stock ? `Còn ${product.stock_quantity}` : 'Hết hàng';

        meta.appendChild(priceSpan);
        meta.appendChild(stockSpan);

        const actions = document.createElement('div');
        actions.className = 'ai-chat-product-card__actions';

        const detailBtn = document.createElement('a');
        detailBtn.className = 'ai-chat-product-card__btn ai-chat-product-card__btn--detail';
        detailBtn.href = product.url || '#';
        detailBtn.textContent = 'Xem chi tiết';

        const cartBtn = document.createElement('button');
        cartBtn.type = 'button';
        cartBtn.className = 'ai-chat-product-card__btn ai-chat-product-card__btn--cart';
        cartBtn.setAttribute('data-quick-add-cart', '');
        cartBtn.setAttribute('data-product-id', String(product.id || ''));
        cartBtn.setAttribute('data-product-name', String(product.name || ''));
        cartBtn.disabled = !product.in_stock;
        cartBtn.innerHTML = product.in_stock
            ? '<i class="bi bi-cart-plus"></i><span>Thêm giỏ</span>'
            : '<span>Hết hàng</span>';

        actions.appendChild(detailBtn);
        actions.appendChild(cartBtn);

        info.appendChild(titleLink);
        info.appendChild(meta);
        info.appendChild(actions);

        card.appendChild(img);
        card.appendChild(info);

        return card;
    }

    function appendMessageToDom({ role, text, products = [], isInScope = true }) {
        if (!messagesBox) return;

        const wrapper = document.createElement('div');
        wrapper.className = role === 'user'
            ? 'ai-chat-msg ai-chat-msg--user'
            : `ai-chat-msg ai-chat-msg--bot${isInScope ? '' : ' ai-chat-msg--refusal'}`;

        const bubble = document.createElement('div');
        bubble.className = 'ai-chat-msg__bubble';
        bubble.innerHTML = formatReplyHtml(text);
        wrapper.appendChild(bubble);

        if (role !== 'user' && Array.isArray(products) && products.length > 0) {
            const productsWrap = document.createElement('div');
            productsWrap.className = 'ai-chat-products';
            products.forEach((p) => {
                productsWrap.appendChild(buildProductCardElement(p));
            });
            wrapper.appendChild(productsWrap);
        }

        messagesBox.appendChild(wrapper);
        scrollToBottom();
    }

    function appendTypingIndicator() {
        if (!messagesBox) return null;
        const wrapper = document.createElement('div');
        wrapper.className = 'ai-chat-msg ai-chat-msg--bot';
        wrapper.innerHTML = `
            <div class="ai-chat-msg__bubble">
                <div class="ai-chat-typing" aria-label="AI đang suy nghĩ">
                    <span></span><span></span><span></span>
                </div>
            </div>
        `;
        messagesBox.appendChild(wrapper);
        scrollToBottom();
        return wrapper;
    }

    function renderWelcomeMessage() {
        if (!messagesBox) return;
        messagesBox.innerHTML = `
            <div class="ai-chat-msg ai-chat-msg--bot">
                <div class="ai-chat-msg__bubble">${WELCOME_HTML}</div>
            </div>
        `;
    }

    function restoreState() {
        try {
            const raw = sessionStorage.getItem(STORAGE_KEY);
            if (!raw) return;
            const parsed = JSON.parse(raw);

            if (Array.isArray(parsed.messages) && parsed.messages.length > 0) {
                storedMessages = parsed.messages.slice(-MAX_STORED_MESSAGES);
                renderWelcomeMessage();
                storedMessages.forEach((msg) => appendMessageToDom(msg));
            }

            if (parsed.isOpen) {
                setOpenState(true);
            }
        } catch (_) {
            // Bỏ qua dữ liệu lưu lỗi
        }
    }

    function getHistoryPayload() {
        return storedMessages.slice(-6).map((item) => ({
            role: item.role === 'model' ? 'model' : 'user',
            text: String(item.text || '').slice(0, 1000),
        }));
    }

    async function sendPrompt(messageText) {
        const cleanText = String(messageText || '').trim();
        if (!cleanText || isLoading) return;

        isLoading = true;
        if (sendBtn) sendBtn.disabled = true;
        if (input) input.value = '';

        const historyBeforeSend = getHistoryPayload();

        const userMsg = { role: 'user', text: cleanText, products: [], isInScope: true };
        storedMessages.push(userMsg);
        appendMessageToDom(userMsg);
        saveState(panel?.classList.contains('is-open'));

        const typingEl = appendTypingIndicator();

        try {
            const response = await fetch(askUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({
                    message: cleanText,
                    history: historyBeforeSend,
                }),
            });

            const data = await response.json().catch(() => ({}));
            if (typingEl) typingEl.remove();

            let replyText = data.reply || data.message || '';
            if (response.status === 429 && !replyText) {
                replyText = 'Bạn đang hỏi hơi nhanh một chút. Vui lòng đợi vài giây rồi thử lại nhé!';
            }
            if (!replyText) {
                replyText = 'Xin lỗi, mình chưa thể xử lý yêu cầu lúc này. Bạn vui lòng thử lại sau nhé!';
            }

            const botMsg = {
                role: 'model',
                text: replyText,
                products: Array.isArray(data.products) ? data.products : [],
                isInScope: response.ok ? Boolean(data.is_in_scope) : false,
            };

            storedMessages.push(botMsg);
            appendMessageToDom(botMsg);
            saveState(panel?.classList.contains('is-open'));
        } catch (_) {
            if (typingEl) typingEl.remove();
            const errMsg = {
                role: 'model',
                text: 'Không thể kết nối tới máy chủ trợ lý AI. Vui lòng kiểm tra kết nối mạng và thử lại.',
                products: [],
                isInScope: false,
            };
            storedMessages.push(errMsg);
            appendMessageToDom(errMsg);
            saveState(panel?.classList.contains('is-open'));
        } finally {
            isLoading = false;
            if (sendBtn) sendBtn.disabled = false;
            input?.focus();
        }
    }

    if (toggleBtn) {
        toggleBtn.addEventListener('click', () => {
            const isOpen = panel?.classList.contains('is-open');
            setOpenState(!isOpen);
        });
    }

    if (closeBtn) {
        closeBtn.addEventListener('click', () => setOpenState(false));
    }

    if (clearBtn) {
        clearBtn.addEventListener('click', () => {
            storedMessages = [];
            renderWelcomeMessage();
            saveState(panel?.classList.contains('is-open'));
        });
    }

    if (form) {
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            sendPrompt(input?.value || '');
        });
    }

    suggestionChips.forEach((chip) => {
        chip.addEventListener('click', () => {
            const prompt = chip.getAttribute('data-ai-prompt') || '';
            if (!prompt) return;
            if (!panel?.classList.contains('is-open')) {
                setOpenState(true);
            }
            sendPrompt(prompt);
        });
    });

    restoreState();
});
