@extends('admin.layouts.layout')

@section('content')
<div class="container py-4">
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header bg-white fw-bold rounded-top-4 d-flex justify-content-between align-items-center">
            <span>Chat với {{ $conversation->user->name ?? 'Khách hàng' }}</span>
            <small class="text-muted">Phòng #{{ $conversation->id }}</small>
        </div>

        <div id="chatMessages"
             class="card-body"
             style="height: 520px; overflow-y: auto; background:#f8fafc;">
            @foreach($messages as $message)
                <div class="mb-3 {{ (int) $message->sender_id === (int) auth()->id() ? 'text-end' : 'text-start' }}"
                     data-message-id="{{ $message->id }}">
                    <div class="d-inline-block px-3 py-2 rounded-4 {{ (int) $message->sender_id === (int) auth()->id() ? 'bg-primary text-white' : 'bg-white border' }}"
                         style="max-width: 75%;">
                        <div>{{ $message->body }}</div>
                        <small class="{{ (int) $message->sender_id === (int) auth()->id() ? 'text-white-50' : 'text-muted' }}">
                            {{ $message->sender->name ?? 'Người dùng' }} · {{ $message->created_at->format('H:i d/m/Y') }}
                        </small>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="card-footer bg-white rounded-bottom-4">
            <form id="chatForm"
                  class="d-flex gap-2"
                  method="POST"
                  action="{{ route('admin.chats.send', $conversation) }}">
                @csrf

                <input type="text"
                       id="chatInput"
                       name="body"
                       class="form-control"
                       placeholder="Nhập phản hồi..."
                       autocomplete="off">

                <button class="btn btn-primary px-4" type="submit">
                    Gửi
                </button>
            </form>

            <div id="chatDebug" class="small text-muted mt-2"></div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const conversationId = @json($conversation->id);
    const authId = @json(auth()->id());
    const messagesBox = document.getElementById('chatMessages');
    const form = document.getElementById('chatForm');
    const input = document.getElementById('chatInput');
    const debug = document.getElementById('chatDebug');

    if (!messagesBox || !form || !input) {
        console.log('[ADMIN CHAT] Thiếu phần tử chat');
        return;
    }

    function setDebug(text) {
        if (debug) {
            debug.innerText = text;
        }
        console.log('[ADMIN CHAT]', text);
    }

    function getCsrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        const hiddenInput = form.querySelector('input[name="_token"]');

        return meta?.content || hiddenInput?.value || '';
    }

    function scrollBottom() {
        messagesBox.scrollTop = messagesBox.scrollHeight;
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.innerText = text ?? '';
        return div.innerHTML;
    }

    function appendMessage(message) {
        if (!message || !message.id) {
            return;
        }

        if (document.querySelector('[data-message-id="' + message.id + '"]')) {
            return;
        }

        const isMine = Number(message.sender_id) === Number(authId);

        const wrapper = document.createElement('div');
        wrapper.className = 'mb-3 ' + (isMine ? 'text-end' : 'text-start');
        wrapper.setAttribute('data-message-id', message.id);

        wrapper.innerHTML = `
            <div class="d-inline-block px-3 py-2 rounded-4 ${isMine ? 'bg-primary text-white' : 'bg-white border'}" style="max-width:75%;">
                <div>${escapeHtml(message.body)}</div>
                <small class="${isMine ? 'text-white-50' : 'text-muted'}">${escapeHtml(message.sender_name)} · ${escapeHtml(message.created_at)}</small>
            </div>
        `;

        messagesBox.appendChild(wrapper);
        scrollBottom();
    }

    scrollBottom();

    if (window.Echo) {
        setDebug('Echo đã load. Đang nghe phòng chat #' + conversationId);

        window.Echo.private('chat.' + conversationId)
            .listen('.message.sent', function (event) {
                setDebug('Nhận realtime message #' + event.id);
                appendMessage(event);
            });
    } else {
        setDebug('Echo chưa load. Kiểm tra admin layout đã có Vite chưa.');
    }

    form.addEventListener('submit', async function (event) {
        event.preventDefault();

        const body = input.value.trim();
        const csrfToken = getCsrfToken();

        if (!body) {
            return;
        }

        if (!csrfToken) {
            setDebug('Thiếu CSRF token.');
            alert('Thiếu CSRF token. Kiểm tra admin layout.');
            return;
        }

        input.value = '';
        setDebug('Đang gửi phản hồi...');

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ body: body }),
            });

            const raw = await response.text();

            let data;

            try {
                data = JSON.parse(raw);
            } catch (error) {
                console.error(raw);
                setDebug('Server không trả JSON. Có thể lỗi auth, CSRF hoặc 500.');
                alert('Server không trả JSON. Mở Network xem request gửi tin.');
                return;
            }

            if (!response.ok) {
                console.error(data);
                setDebug('Gửi thất bại HTTP ' + response.status);
                alert(data.message || 'Gửi phản hồi thất bại.');
                return;
            }

            if (data.ok && data.message) {
                setDebug('Gửi thành công message #' + data.message.id);
                appendMessage(data.message);
            } else {
                console.error(data);
                setDebug('Response thiếu data.message.');
            }
        } catch (error) {
            console.error(error);
            setDebug('Lỗi JS khi gửi phản hồi.');
            alert('Không gửi được phản hồi.');
        }
    });
});
</script>
@endpush
