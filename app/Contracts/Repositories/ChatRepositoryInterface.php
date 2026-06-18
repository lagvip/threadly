<?php

namespace App\Contracts\Repositories;

use App\Models\ChatConversation;
use App\Models\ChatMessage;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface ChatRepositoryInterface
{
    public function paginateConversationsForAdmin(int $perPage = 20): LengthAwarePaginator;

    public function findConversation(int $id): ?ChatConversation;

    public function firstOrCreateOpenConversationForUser(int $userId): ChatConversation;

    public function messagesForConversation(ChatConversation $conversation): Collection;

    public function createMessage(ChatConversation $conversation, int $senderId, string $senderRole, string $body): ChatMessage;

    public function updateConversation(ChatConversation $conversation, array $data): bool;

    public function markUserMessagesAsRead(ChatConversation $conversation): int;
}
