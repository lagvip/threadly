<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\ChatRepositoryInterface;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ChatRepository implements ChatRepositoryInterface
{
    protected function conversationsForAdmin(): Builder
    {
        return ChatConversation::with(['user', 'latestMessage.sender'])
            ->withCount([
                'messages as unread_count' => function ($query) {
                    $query->where('sender_role', 'user')->whereNull('read_at');
                },
            ]);
    }

    public function paginateConversationsForAdmin(int $perPage = 20): LengthAwarePaginator
    {
        return $this->conversationsForAdmin()
            ->orderByRaw('COALESCE(last_message_at, created_at) DESC')
            ->paginate($perPage);
    }

    public function findConversation(int $id): ?ChatConversation
    {
        return ChatConversation::find($id);
    }

    public function firstOrCreateOpenConversationForUser(int $userId): ChatConversation
    {
        return DB::transaction(function () use ($userId) {
            User::whereKey($userId)->lockForUpdate()->firstOrFail();

            return ChatConversation::firstOrCreate(
                [
                    'user_id' => $userId,
                    'status' => 'open',
                ],
                [
                    'last_message_at' => now(),
                ]
            );
        });
    }

    public function messagesForConversation(ChatConversation $conversation): Collection
    {
        return $conversation->messages()->with('sender')->oldest()->get();
    }

    public function createMessage(ChatConversation $conversation, int $senderId, string $senderRole, string $body): ChatMessage
    {
        return ChatMessage::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $senderId,
            'sender_role' => $senderRole,
            'body' => $body,
        ]);
    }

    public function updateConversation(ChatConversation $conversation, array $data): bool
    {
        return $conversation->update($data);
    }

    public function markUserMessagesAsRead(ChatConversation $conversation): int
    {
        return ChatMessage::where('conversation_id', $conversation->id)
            ->where('sender_role', 'user')
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }
}
