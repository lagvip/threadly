<?php

namespace App\Http\Controllers\Client;

use App\Events\ChatMessageSent;
use App\Http\Controllers\Controller;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ChatController extends Controller
{

    public function send(Request $request)
    {
        $validated = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $message = DB::transaction(function () use ($validated) {
            $conversation = ChatConversation::firstOrCreate(
                [
                    'user_id' => Auth::id(),
                    'status' => 'open',
                ],
                [
                    'last_message_at' => now(),
                ]
            );

            $message = ChatMessage::create([
                'conversation_id' => $conversation->id,
                'sender_id' => Auth::id(),
                'sender_role' => 'user',
                'body' => $validated['body'],
            ]);

            $conversation->update([
                'last_message_at' => now(),
            ]);

            return $message;
        });

        broadcast(new ChatMessageSent($message))->toOthers();

        return response()->json([
            'ok' => true,
            'message' => [
                'id' => $message->id,
                'conversation_id' => $message->conversation_id,
                'sender_id' => $message->sender_id,
                'sender_role' => $message->sender_role,
                'sender_name' => Auth::user()->name,
                'body' => $message->body,
                'created_at' => $message->created_at->format('H:i d/m/Y'),
            ],
        ]);
    }
    public function widgetData()
    {
        $conversation = ChatConversation::firstOrCreate(
            [
                'user_id' => Auth::id(),
                'status' => 'open',
            ],
            [
                'last_message_at' => now(),
            ]
        );

        $messages = $conversation->messages()
            ->with('sender')
            ->oldest()
            ->get()
            ->map(function ($message) {
                return [
                    'id' => $message->id,
                    'conversation_id' => $message->conversation_id,
                    'sender_id' => $message->sender_id,
                    'sender_name' => $message->sender?->name ?? 'Người dùng',
                    'sender_role' => $message->sender_role,
                    'body' => $message->body,
                    'created_at' => $message->created_at?->format('H:i'),
                    'created_at_full' => $message->created_at?->format('H:i d/m/Y'),
                ];
            });

        return response()->json([
            'ok' => true,
            'conversation_id' => $conversation->id,
            'messages' => $messages,
        ]);
    }
}
