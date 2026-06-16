<?php

namespace App\Http\Controllers\Admin;

use App\Events\Content\ChatMessageSent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Chat\SendChatMessageRequest;
use App\Models\ChatConversation;
use App\Services\Chat\ChatService;

class ChatController extends Controller
{
    public function __construct(protected ChatService $chat) {}

    public function index()
    {
        $this->authorize('viewAny', ChatConversation::class);

        return view('admin.chats.index', $this->chat->adminIndexData());
    }

    public function show(ChatConversation $conversation)
    {
        $this->authorize('view', $conversation);

        return view('admin.chats.show', $this->chat->adminShowData($conversation));
    }

    public function send(SendChatMessageRequest $request, ChatConversation $conversation)
    {
        $this->authorize('update', $conversation);

        $message = $this->chat->sendFromAdmin($conversation, (int) $request->user()->id, (string) $request->input('body'));

        broadcast(new ChatMessageSent($message))->toOthers();

        return response()->json($this->chat->messagePayload($message, $request->user()));
    }
}
