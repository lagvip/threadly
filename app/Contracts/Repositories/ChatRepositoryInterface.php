<?php

namespace App\Contracts\Repositories;

use App\Models\ChatConversation;
use App\Models\ChatMessage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

interface ChatRepositoryInterface
{
    public function conversationsForAdmin(): Builder;

    public function firstOrCreateOpenConversationForUser(int $userId): ChatConversation;

    public function messagesForConversation(ChatConversation $conversation): Collection;

    public function createMessage(ChatConversation $conversation, int $senderId, string $senderRole, string $body): ChatMessage;

    public function markUserMessagesAsRead(ChatConversation $conversation): int;
}
