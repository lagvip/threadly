<?php

namespace App\Services\Chat;

use App\Contracts\Repositories\ChatRepositoryInterface;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ChatService
{
    public function __construct(protected ChatRepositoryInterface $chats) {}

    public function adminIndexData(): array
    {
        return [
            'conversations' => $this->chats->conversationsForAdmin()
                ->orderByRaw('COALESCE(last_message_at, created_at) DESC')
                ->paginate(20),
        ];
    }

    public function adminShowData(ChatConversation $conversation): array
    {
        $conversation->load('user');
        $this->chats->markUserMessagesAsRead($conversation);

        return [
            'conversation' => $conversation,
            'messages' => $this->chats->messagesForConversation($conversation),
        ];
    }

    public function canAccessConversation(User $user, int $conversationId): bool
    {
        $conversation = $this->chats->findConversation($conversationId);

        if (! $conversation) {
            return false;
        }

        $isOwner = (int) $conversation->user_id === (int) $user->id;
        $isStaff =
            (method_exists($user, 'isAdmin') && $user->isAdmin())
            || (method_exists($user, 'isManager') && $user->isManager());

        return $isOwner || $isStaff;
    }

    public function sendFromUser(int $userId, string $body): ChatMessage
    {
        return DB::transaction(function () use ($userId, $body) {
            $conversation = $this->chats->firstOrCreateOpenConversationForUser($userId);

            return $this->createMessage($conversation, $userId, 'user', $body);
        });
    }

    public function sendFromAdmin(ChatConversation $conversation, int $adminId, string $body): ChatMessage
    {
        return DB::transaction(function () use ($conversation, $adminId, $body) {
            if (! $conversation->admin_id) {
                $this->chats->updateConversation($conversation, ['admin_id' => $adminId]);
            }

            return $this->createMessage($conversation, $adminId, 'admin', $body);
        });
    }

    public function widgetData(int $userId): array
    {
        $conversation = $this->chats->firstOrCreateOpenConversationForUser($userId);

        return [
            'ok' => true,
            'conversation_id' => $conversation->id,
            'messages' => $this->chats->messagesForConversation($conversation)
                ->map(fn ($message) => [
                    'id' => $message->id,
                    'conversation_id' => $message->conversation_id,
                    'sender_id' => $message->sender_id,
                    'sender_name' => $message->sender?->name ?? 'Người dùng',
                    'sender_role' => $message->sender_role,
                    'body' => $message->body,
                    'created_at' => $message->created_at?->format('H:i'),
                    'created_at_full' => $message->created_at?->format('H:i d/m/Y'),
                ]),
        ];
    }

    public function messagePayload(ChatMessage $message, User $sender): array
    {
        return [
            'ok' => true,
            'message' => [
                'id' => $message->id,
                'conversation_id' => $message->conversation_id,
                'sender_id' => $message->sender_id,
                'sender_role' => $message->sender_role,
                'sender_name' => $sender->name,
                'body' => $message->body,
                'created_at' => $message->created_at->format('H:i d/m/Y'),
            ],
        ];
    }

    protected function createMessage(ChatConversation $conversation, int $senderId, string $senderRole, string $body): ChatMessage
    {
        $message = $this->chats->createMessage($conversation, $senderId, $senderRole, $body);

        $this->chats->updateConversation($conversation, ['last_message_at' => now()]);

        return $message;
    }
}
