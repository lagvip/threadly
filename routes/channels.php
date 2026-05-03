<?php

use App\Models\ChatConversation;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('chat.{conversationId}', function ($user, $conversationId) {
    $conversation = ChatConversation::find($conversationId);

    if (!$conversation) {
        return false;
    }

    $isOwner = (int) $conversation->user_id === (int) $user->id;

    $isStaff =
        (method_exists($user, 'isAdmin') && $user->isAdmin())
        || (method_exists($user, 'isManager') && $user->isManager());

    return $isOwner || $isStaff;
});
