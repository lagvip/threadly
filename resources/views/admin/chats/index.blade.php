@extends('admin.layouts.layout')

@section('content')
<style>
    .admin-chat-page {
        color: #334155;
    }

    .admin-chat-page .page-title {
        font-size: 22px;
        font-weight: 800;
        color: #1e293b;
        margin-bottom: 6px;
    }

    .admin-chat-page .page-subtitle {
        color: #64748b;
        font-size: 14px;
        margin-bottom: 0;
    }

    .chat-shell-card {
        border: 0;
        border-radius: 18px;
        background: #fff;
        box-shadow: 0 10px 28px rgba(15, 23, 42, 0.06);
        overflow: hidden;
    }

    .chat-shell-header {
        padding: 18px 22px;
        border-bottom: 1px solid #eef2f7;
        background: #fff;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
    }

    .chat-shell-header h5 {
        margin: 0;
        font-size: 17px;
        font-weight: 800;
        color: #1e293b;
    }

    .chat-search-input {
        width: 320px;
        max-width: 100%;
        height: 40px;
        border: 1px solid #dbe4ef;
        border-radius: 10px;
        padding: 0 14px;
        font-size: 14px;
        color: #475569;
        outline: none;
        background: #f8fafc;
    }

    .chat-search-input:focus {
        background: #fff;
        border-color: #ff6b35;
        box-shadow: 0 0 0 3px rgba(255, 107, 53, 0.1);
    }

    .chat-shell-body {
        padding: 14px;
        background: #fff;
    }

    .chat-item {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 16px;
        border: 1px solid #eef2f7;
        border-radius: 14px;
        background: #fff;
        text-decoration: none;
        color: inherit;
        transition: all 0.18s ease;
        margin-bottom: 12px;
    }

    .chat-item:last-child {
        margin-bottom: 0;
    }

    .chat-item:hover {
        background: #f8fafc;
        border-color: #dbe4ef;
        color: inherit;
        transform: translateY(-1px);
    }

    .chat-avatar {
        width: 48px;
        height: 48px;
        flex: 0 0 48px;
        border-radius: 50%;
        background: linear-gradient(135deg, #ff6b35, #ff9f68);
        color: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 18px;
        position: relative;
    }

    .chat-avatar::after {
        content: "";
        width: 11px;
        height: 11px;
        border-radius: 50%;
        background: #22c55e;
        border: 2px solid #fff;
        position: absolute;
        right: 1px;
        bottom: 2px;
    }

    .chat-content {
        min-width: 0;
        flex: 1;
    }

    .chat-top-line {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 3px;
        min-width: 0;
    }

    .chat-name {
        font-size: 15px;
        font-weight: 800;
        color: #1e293b;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .chat-email {
        font-size: 13px;
        color: #64748b;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        margin-bottom: 6px;
    }

    .chat-last-message {
        font-size: 14px;
        line-height: 1.45;
        color: #475569;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .chat-last-message.empty {
        color: #94a3b8;
        font-style: italic;
    }

    .chat-prefix {
        color: #1e293b;
        font-weight: 800;
    }

    .chat-meta {
        min-width: 150px;
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 9px;
        text-align: right;
    }

    .chat-time {
        color: #64748b;
        font-size: 13px;
        white-space: nowrap;
    }

    .chat-badges {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 6px;
        flex-wrap: wrap;
    }

    .chat-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        padding: 6px 12px;
        min-height: 28px;
        font-family: Arial, "Helvetica Neue", sans-serif;
        font-size: 12px;
        font-weight: 700;
        line-height: 1.2;
        white-space: nowrap;
        letter-spacing: 0;
        text-transform: none;
    }

    .chat-badge-open {
        background: #dcfce7;
        color: #15803d;
    }

    .chat-badge-closed {
        background: #e2e8f0;
        color: #475569;
    }

    .chat-badge-unread {
        background: #ef4444;
        color: #fff;
        min-width: 26px;
        height: 26px;
        padding: 0 8px;
        font-size: 12px;
    }

    .chat-actions {
        display: flex;
        align-items: center;
        gap: 6px;
        margin-top: 2px;
    }

    .chat-actions .btn {
        height: 30px;
        min-width: 34px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0 9px;
        font-size: 12px;
    }

    .chat-empty {
        padding: 48px 20px;
        text-align: center;
        color: #94a3b8;
    }

    .chat-empty-icon {
        width: 62px;
        height: 62px;
        border-radius: 50%;
        background: #f1f5f9;
        color: #64748b;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
        margin-bottom: 14px;
    }

    @media (max-width: 767.98px) {
        .chat-shell-header {
            padding: 16px;
            align-items: stretch;
        }

        .chat-search-input {
            width: 100%;
        }

        .chat-item {
            align-items: flex-start;
            padding: 14px;
        }

        .chat-avatar {
            width: 42px;
            height: 42px;
            flex-basis: 42px;
            font-size: 16px;
        }

        .chat-meta {
            min-width: 0;
            align-items: flex-start;
            text-align: left;
            width: 100%;
            margin-top: 8px;
        }

        .chat-item {
            flex-wrap: wrap;
        }

        .chat-content {
            flex: 1 1 calc(100% - 56px);
        }

        .chat-meta {
            flex: 1 1 100%;
            padding-left: 56px;
        }
    }
</style>

<div class="container-fluid py-4 admin-chat-page">
    <div class="mb-4">
        <h4 class="page-title">Tin nhắn khách hàng</h4>
        <p class="page-subtitle">
            Quản lý các cuộc trò chuyện realtime giữa khách hàng và shop.
        </p>
    </div>

    <div class="chat-shell-card">
        <div class="chat-shell-header">
            <h5>Danh sách cuộc trò chuyện</h5>

            <input type="text"
                   id="chatSearchInput"
                   class="chat-search-input"
                   placeholder="Tìm theo tên, email, tin nhắn...">
        </div>

        <div class="chat-shell-body" id="chatList">
            @forelse($conversations as $conversation)
                @php
                    $user = $conversation->user;
                    $latestMessage = $conversation->latestMessage;

                    $displayName = $user->name ?? 'Khách hàng';
                    $email = $user->email ?? '';
                    $avatarLetter = mb_strtoupper(mb_substr($displayName, 0, 1));

                    $lastBody = $latestMessage?->body;
                    $lastTime = $conversation->last_message_at
                        ? $conversation->last_message_at->format('H:i d/m/Y')
                        : $conversation->created_at->format('H:i d/m/Y');

                    $unreadCount = (int) ($conversation->unread_count ?? 0);
                    $status = $conversation->status ?? 'open';
                @endphp

                <a href="{{ route('admin.chats.show', $conversation) }}"
                   class="chat-item"
                   data-search="{{ mb_strtolower($displayName . ' ' . $email . ' ' . ($lastBody ?? '')) }}">
                    <div class="chat-avatar">
                        {{ $avatarLetter }}
                    </div>

                    <div class="chat-content">
                        <div class="chat-top-line">
                            <div class="chat-name" title="{{ $displayName }}">
                                {{ $displayName }}
                            </div>

                            @if($unreadCount > 0)
                                <span class="chat-badge chat-badge-unread">
                                    {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                                </span>
                            @endif
                        </div>

                        <div class="chat-email" title="{{ $email }}">
                            {{ $email ?: 'Không có email' }}
                        </div>

                        @if($lastBody)
                            <div class="chat-last-message" title="{{ $lastBody }}">
                                @if($latestMessage->sender_role === 'admin')
                                    <span class="chat-prefix">Bạn:</span>
                                @endif
                                {{ $lastBody }}
                            </div>
                        @else
                            <div class="chat-last-message empty">
                                Chưa có tin nhắn
                            </div>
                        @endif
                    </div>

                    <div class="chat-meta">
                        <div class="chat-time">
                            {{ $lastTime }}
                        </div>

                        <div class="chat-badges">
                            <span class="chat-badge {{ $status === 'open' ? 'chat-badge-open' : 'chat-badge-closed' }}">
                                {{ $status === 'open' ? 'Đang mở' : 'Đã đóng' }}
                            </span>
                        </div>

                        <div class="chat-actions">

                            <span class="btn btn-sm btn-outline-success">
                                <i class="bi bi-chat-dots-fill me-1"></i> Trả lời
                            </span>
                        </div>
                    </div>
                </a>
            @empty
                <div class="chat-empty">
                    <div class="chat-empty-icon">💬</div>
                    <h5 class="fw-bold mb-2">Chưa có cuộc trò chuyện nào</h5>
                    <p class="mb-0">Khi khách hàng nhắn tin, cuộc trò chuyện sẽ xuất hiện tại đây.</p>
                </div>
            @endforelse
        </div>
    </div>

    <div class="mt-3">
        {{ $conversations->links() }}
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('chatSearchInput');
    const items = document.querySelectorAll('.chat-item');

    if (!input) {
        return;
    }

    input.addEventListener('input', function () {
        const keyword = this.value.trim().toLowerCase();

        items.forEach(function (item) {
            const haystack = item.dataset.search || '';
            item.style.display = haystack.includes(keyword) ? '' : 'none';
        });
    });
});
</script>
@endpush
