<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Services\ShopChatService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatbotController extends Controller
{
    public function __construct(
        protected ShopChatService $shopChatService
    ) {}

    public function index()
    {
        return view('client.ai.chat');
    }

    public function ask(Request $request)
    {
        $data = $request->validate([
            'message' => ['required', 'string', 'max:1000'],
        ]);

        $reply = $this->shopChatService->reply(Auth::user(), $data['message']);

        return response()->json([
            'reply' => $reply,
        ]);
    }
}
