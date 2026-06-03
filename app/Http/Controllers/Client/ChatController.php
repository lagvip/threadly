<?php

namespace App\Http\Controllers\Client;

use App\Events\ChatMessageSent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Chat\SendChatMessageRequest;
use App\Services\Chat\ChatService;

class ChatController extends Controller
{
    public function __construct(protected ChatService $chat)
    {
    }

    public function send(SendChatMessageRequest $request)
    {
        $message = $this->chat->sendFromUser((int) $request->user()->id, (string) $request->input('body'));

        broadcast(new ChatMessageSent($message))->toOthers();

        return response()->json($this->chat->messagePayload($message, $request->user()));
    }

    public function widgetData()
    {
        return response()->json($this->chat->widgetData((int) auth()->id()));
    }
}
