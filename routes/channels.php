<?php

use App\Services\Chat\ChatService;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('chat.{conversationId}', function ($user, $conversationId) {
    return app(ChatService::class)->canAccessConversation($user, (int) $conversationId);
});
