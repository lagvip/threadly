<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\Chatbot\AskChatbotRequest;
use App\Services\Client\Chatbot\ShopChatService;

class ChatbotController extends Controller
{
    public function __construct(
        protected ShopChatService $shopChatService
    ) {}

    public function index()
    {
        return view('client.ai.chat');
    }

    public function ask(AskChatbotRequest $request)
    {
        $reply = $this->shopChatService->reply($request->user(), (string) $request->input('message'));

        return response()->json([
            'reply' => $reply,
        ]);
    }
}
