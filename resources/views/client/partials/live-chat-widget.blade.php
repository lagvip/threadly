<style>
    .live-chat-widget {
        position: fixed;
        right: 24px;
        bottom: 96px;
        z-index: 10002;
        font-family: inherit;
    }

    .live-chat-toggle {
        width: 58px;
        height: 58px;
        border: none;
        border-radius: 50%;
        background: linear-gradient(135deg, #0084ff, #36a3ff);
        color: #fff;
        font-size: 24px;
        box-shadow: 0 10px 25px rgba(0, 132, 255, 0.25);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all .2s ease;
        text-decoration: none;
    }

    .live-chat-toggle:hover {
        transform: translateY(-2px);
        color: #fff;
    }

    .live-chat-toggle-badge {
        position: absolute;
        top: -4px;
        right: -2px;
        min-width: 22px;
        height: 22px;
        padding: 0 6px;
        border-radius: 999px;
        background: #ff3b30;
        color: #fff;
        font-size: 12px;
        font-weight: 700;
        display: none;
        align-items: center;
        justify-content: center;
        border: 2px solid #fff;
    }

    .live-chat-box {
        position: absolute;
        right: 0;
        bottom: 74px;
        width: 360px;
        max-width: calc(100vw - 24px);
        height: 560px;
        background: #fff;
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.18);
        border: 1px solid #e9ecef;
        display: none;
        flex-direction: column;
    }

    .live-chat-box.show {
        display: flex;
    }

    .live-chat-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 14px 16px;
        border-bottom: 1px solid #edf0f2;
        background: #fff;
    }

    .live-chat-header-left {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .live-chat-avatar {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        background: linear-gradient(135deg, #0084ff, #36a3ff);
        color: #fff;
        font-weight: 700;
        font-size: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        flex-shrink: 0;
    }

    .live-chat-avatar::after {
        content: '';
        position: absolute;
        right: 1px;
        bottom: 1px;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: #22c55e;
        border: 2px solid #fff;
    }

    .live-chat-title {
        font-size: 15px;
        font-weight: 700;
        line-height: 1.2;
        color: #111827;
    }

    .live-chat-subtitle {
        font-size: 12px;
        color: #6b7280;
        margin-top: 2px;
    }

    .live-chat-close {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        border: none;
        background: #f3f4f6;
        color: #374151;
        font-size: 18px;
        cursor: pointer;
    }

    .live-chat-body {
        flex: 1;
        padding: 14px 12px;
        overflow-y: auto;
        background: #f5f7fb;
    }

    .live-chat-empty {
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #9ca3af;
        font-size: 14px;
        text-align: center;
        padding: 20px;
    }

    .live-chat-row {
        display: flex;
        margin-bottom: 10px;
    }

    .live-chat-row.me {
        justify-content: flex-end;
    }

    .live-chat-row.other {
        justify-content: flex-start;
    }

    .live-chat-bubble {
        max-width: 78%;
        padding: 10px 12px;
        border-radius: 18px;
        font-size: 14px;
        line-height: 1.45;
        word-break: break-word;
        box-shadow: 0 1px 2px rgba(0,0,0,.04);
    }

    .live-chat-row.me .live-chat-bubble {
        background: #0084ff;
        color: #fff;
        border-bottom-right-radius: 6px;
    }

    .live-chat-row.other .live-chat-bubble {
        background: #fff;
        color: #111827;
        border: 1px solid #e5e7eb;
        border-bottom-left-radius: 6px;
    }

    .live-chat-time {
        font-size: 11px;
        margin-top: 4px;
        opacity: .8;
    }

    .live-chat-row.me .live-chat-time {
        color: rgba(255,255,255,.85);
    }

    .live-chat-row.other .live-chat-time {
        color: #6b7280;
    }

    .live-chat-footer {
        padding: 12px;
        border-top: 1px solid #edf0f2;
        background: #fff;
    }

    .live-chat-form {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .live-chat-input {
        flex: 1;
        min-width: 0;
        height: 42px;
        border-radius: 999px;
        border: 1px solid #d1d5db;
        padding: 0 15px;
        outline: none;
        font-size: 14px;
        background: #f9fafb;
    }

    .live-chat-input:focus {
        border-color: #0084ff;
        background: #fff;
    }

    .live-chat-send {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        border: none;
        background: #0084ff;
        color: #fff;
        font-size: 18px;
        cursor: pointer;
        flex-shrink: 0;
    }

    .live-chat-debug {
        margin-top: 8px;
        font-size: 11px;
        color: #9ca3af;
        min-height: 16px;
    }

    @media (max-width: 575px) {
        .live-chat-widget {
            right: 12px;
            bottom: 86px;
        }

        .live-chat-box {
            width: calc(100vw - 24px);
            height: 72vh;
            right: 0;
            bottom: 68px;
        }
    }
</style>

<div class="live-chat-widget" id="liveChatWidget">
    @auth
        <button type="button" class="live-chat-toggle" id="liveChatToggle" aria-label="Mở chat">
            💬
            <span class="live-chat-toggle-badge" id="liveChatBadge">0</span>
        </button>

        <div class="live-chat-box" id="liveChatBox">
            <div class="live-chat-header">
                <div class="live-chat-header-left">
                    <div class="live-chat-avatar">T</div>
                    <div>
                        <div class="live-chat-title">Threadly Support</div>
                        <div class="live-chat-subtitle">Đang hoạt động</div>
                    </div>
                </div>

                <button type="button" class="live-chat-close" id="liveChatClose">×</button>
            </div>

            <div class="live-chat-body" id="liveChatMessages">
                <div class="live-chat-empty" id="liveChatEmpty">
                    Xin chào 👋<br>
                    Bạn cần shop hỗ trợ gì?
                </div>
            </div>

            <div class="live-chat-footer">
                <form id="liveChatForm" class="live-chat-form" action="{{ route('client.chat.send') }}" method="POST">
                    @csrf
                    <input
                        type="text"
                        id="liveChatInput"
                        class="live-chat-input"
                        placeholder="Nhập tin nhắn..."
                        autocomplete="off"
                        name="body"
                    >
                    <button type="submit" class="live-chat-send">➤</button>
                </form>

                <div class="live-chat-debug" id="liveChatDebug"></div>
            </div>
        </div>
    @else
        <a href="{{ route('login') }}" class="live-chat-toggle" aria-label="Đăng nhập để chat" style="text-decoration:none;">
            💬
        </a>
    @endauth
</div>

@auth
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const widget = document.getElementById('liveChatWidget');
    const toggleBtn = document.getElementById('liveChatToggle');
    const closeBtn = document.getElementById('liveChatClose');
    const chatBox = document.getElementById('liveChatBox');
    const messagesBox = document.getElementById('liveChatMessages');
    const emptyBox = document.getElementById('liveChatEmpty');
    const form = document.getElementById('liveChatForm');
    const input = document.getElementById('liveChatInput');
    const badge = document.getElementById('liveChatBadge');
    const debugBox = document.getElementById('liveChatDebug');
    const authId = @json(auth()->id());

    let conversationId = null;
    let isLoaded = false;
    let isSubscribed = false;
    let isOpen = false;
    let unreadCount = 0;

    function setDebug(text) {
        if (debugBox) debugBox.innerText = text || '';
        console.log('[LIVE CHAT]', text);
    }

    function getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.content
            || form.querySelector('input[name="_token"]')?.value
            || '';
    }

    function updateBadge() {
        if (!badge) return;

        if (unreadCount > 0) {
            badge.style.display = 'flex';
            badge.textContent = unreadCount > 9 ? '9+' : unreadCount;
        } else {
            badge.style.display = 'none';
        }
    }

    function openChat() {
        chatBox.classList.add('show');
        isOpen = true;
        unreadCount = 0;
        updateBadge();
        setTimeout(() => {
            scrollBottom();
            input?.focus();
        }, 100);
    }

    function closeChat() {
        chatBox.classList.remove('show');
        isOpen = false;
    }

    function scrollBottom() {
        if (messagesBox) {
            messagesBox.scrollTop = messagesBox.scrollHeight;
        }
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.innerText = text ?? '';
        return div.innerHTML;
    }

    function removeEmptyState() {
        if (emptyBox) {
            emptyBox.style.display = 'none';
        }
    }

    function renderMessage(message) {
        if (!message || !message.id) return;

        if (messagesBox.querySelector('[data-message-id="' + message.id + '"]')) {
            return;
        }

        removeEmptyState();

        const isMine = Number(message.sender_id) === Number(authId);
        const row = document.createElement('div');
        row.className = 'live-chat-row ' + (isMine ? 'me' : 'other');
        row.setAttribute('data-message-id', message.id);

        row.innerHTML = `
            <div class="live-chat-bubble">
                <div>${escapeHtml(message.body)}</div>
                <div class="live-chat-time">${escapeHtml(message.created_at || '')}</div>
            </div>
        `;

        messagesBox.appendChild(row);
        scrollBottom();
    }

    async function loadWidgetData() {
        if (isLoaded) return;

        try {
            setDebug('Đang tải hội thoại...');

            const response = await fetch(@json(route('client.chat.widgetData')), {
                headers: {
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (!response.ok || !data.ok) {
                setDebug('Không tải được dữ liệu chat.');
                return;
            }

            conversationId = data.conversation_id;

            if (Array.isArray(data.messages) && data.messages.length > 0) {
                data.messages.forEach(renderMessage);
            }

            subscribeChannel();
            isLoaded = true;
            setDebug('Chat sẵn sàng.');
        } catch (error) {
            console.error(error);
            setDebug('Lỗi tải dữ liệu chat.');
        }
    }

    function subscribeChannel() {
        if (!window.Echo || !conversationId || isSubscribed) return;

        window.Echo.private('chat.' + conversationId)
            .listen('.message.sent', function (event) {
                renderMessage(event);

                if (!isOpen && Number(event.sender_id) !== Number(authId)) {
                    unreadCount++;
                    updateBadge();
                }
            });

        isSubscribed = true;
    }

    toggleBtn?.addEventListener('click', async function () {
        if (!isOpen) {
            openChat();
            await loadWidgetData();
        } else {
            closeChat();
        }
    });

    closeBtn?.addEventListener('click', function () {
        closeChat();
    });

    document.addEventListener('click', function (e) {
        if (!widget.contains(e.target) && isOpen) {
            closeChat();
        }
    });

    form?.addEventListener('submit', async function (event) {
        event.preventDefault();

        const body = input.value.trim();
        const csrfToken = getCsrfToken();

        if (!body) return;

        if (!csrfToken) {
            setDebug('Thiếu CSRF token.');
            return;
        }

        if (!isLoaded) {
            await loadWidgetData();
        }

        const tempValue = body;
        input.value = '';

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ body: tempValue }),
            });

            const data = await response.json();

            if (response.ok && data.ok && data.message) {
                renderMessage(data.message);
                setDebug('');
            } else {
                input.value = tempValue;
                setDebug(data.message || 'Gửi tin nhắn thất bại.');
            }
        } catch (error) {
            console.error(error);
            input.value = tempValue;
            setDebug('Không gửi được tin nhắn.');
        }
    });
});
</script>
@endpush
@endauth
