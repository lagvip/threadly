<div class="ai-chat-widget">
    <button type="button" class="ai-chat-toggle" id="aiChatToggle">
        💬
    </button>

    <div class="ai-chat-panel" id="aiChatPanel">
        <div class="ai-chat-header">
            <strong>Trợ lý AI</strong>
            <button type="button" id="aiChatClose">✕</button>
        </div>

        <div class="ai-chat-body" id="aiChatBody">
            <div class="ai-message bot">Xin chào 👋 Tôi có thể giúp bạn tìm sản phẩm và kiểm tra đơn hàng.</div>
        </div>

        @auth
            <form id="aiChatForm" class="ai-chat-form">
                <input type="text" id="aiChatInput" placeholder="Nhập câu hỏi...">
                <button type="submit" id="aiChatSend">Gửi</button>
            </form>
        @else
            <div class="ai-chat-login-box">
                <a href="{{ route('login') }}">Đăng nhập để dùng AI</a>
            </div>
        @endauth
    </div>
</div>

<style>
    .ai-chat-widget {
        position: fixed;
        right: 20px;
        bottom: 20px;
        z-index: 99999;
    }

    .ai-chat-toggle {
        width: 58px;
        height: 58px;
        border: none;
        border-radius: 50%;
        background: #0da487;
        color: #fff;
        font-size: 22px;
        cursor: pointer;
        box-shadow: 0 12px 30px rgba(0,0,0,0.2);
    }

    .ai-chat-panel {
        position: absolute;
        right: 0;
        bottom: 72px;
        width: 360px;
        height: 500px;
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 20px 50px rgba(0,0,0,0.18);
        overflow: hidden;
        display: none;
        flex-direction: column;
    }

    .ai-chat-panel.active {
        display: flex;
    }

    .ai-chat-header {
        background: #0da487;
        color: #fff;
        padding: 14px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .ai-chat-header button {
        border: none;
        background: transparent;
        color: #fff;
        cursor: pointer;
        font-size: 16px;
    }

    .ai-chat-body {
        flex: 1;
        padding: 12px;
        overflow-y: auto;
        background: #f8fafc;
    }

    .ai-message {
        padding: 10px 12px;
        border-radius: 12px;
        margin-bottom: 10px;
        font-size: 14px;
        line-height: 1.5;
    }

    .ai-message.bot {
        background: #fff;
        border: 1px solid #e5e7eb;
    }

    .ai-message.user {
        background: #0da487;
        color: #fff;
        margin-left: auto;
    }

    .ai-chat-form {
        display: flex;
        gap: 8px;
        padding: 12px;
        border-top: 1px solid #e5e7eb;
        background: #fff;
    }

    .ai-chat-form input {
        flex: 1;
        height: 42px;
        border: 1px solid #d1d5db;
        border-radius: 10px;
        padding: 0 12px;
    }

    .ai-chat-form button {
        border: none;
        background: #0da487;
        color: #fff;
        border-radius: 10px;
        padding: 0 14px;
        font-weight: 700;
        cursor: pointer;
    }

    .ai-chat-login-box {
        padding: 12px;
        border-top: 1px solid #e5e7eb;
        background: #fff;
    }

    .ai-chat-login-box a {
        color: #0da487;
        font-weight: 700;
        text-decoration: none;
    }
</style>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const panel = document.getElementById('aiChatPanel');
    const toggle = document.getElementById('aiChatToggle');
    const closeBtn = document.getElementById('aiChatClose');
    const form = document.getElementById('aiChatForm');
    const input = document.getElementById('aiChatInput');
    const body = document.getElementById('aiChatBody');

    if (toggle) {
        toggle.addEventListener('click', function () {
            panel.classList.toggle('active');
        });
    }

    if (closeBtn) {
        closeBtn.addEventListener('click', function () {
            panel.classList.remove('active');
        });
    }

    function appendMessage(text, type) {
        const div = document.createElement('div');
        div.className = 'ai-message ' + type;
        div.textContent = text;
        body.appendChild(div);
        body.scrollTop = body.scrollHeight;
    }

    if (form) {
        form.addEventListener('submit', async function (e) {
            e.preventDefault();

            const message = input.value.trim();
            if (!message) return;

            appendMessage(message, 'user');
            input.value = '';

            try {
                const response = await fetch("{{ route('client.ai.ask') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': "{{ csrf_token() }}"
                    },
                    body: JSON.stringify({ message })
                });

                const data = await response.json();
                appendMessage(data.reply || 'AI chưa có phản hồi.', 'bot');
            } catch (error) {
                appendMessage('Không thể kết nối AI.', 'bot');
            }
        });
    }
});
</script>
@endpush
