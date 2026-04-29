<?php

namespace App\Http\Controllers\Admin;

use App\Events\ChatMessageSent;
use App\Http\Controllers\Controller;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ChatController extends Controller
{
    public function index()
    {
        $conversations = ChatConversation::with([
                'user',
                'latestMessage.sender',
            ])
            ->withCount([
                'messages as unread_count' => function ($query) {
                    $query->where('sender_role', 'user')
                        ->whereNull('read_at');
                }
            ])
            ->orderByRaw('COALESCE(last_message_at, created_at) DESC')
            ->paginate(20);

        return view('admin.chats.index', compact('conversations'));
    }

    public function show(ChatConversation $conversation)
    {
        $conversation->load('user');

        ChatMessage::where('conversation_id', $conversation->id)
            ->where('sender_role', 'user')
            ->whereNull('read_at')
            ->update([
                'read_at' => now(),
            ]);

        $messages = $conversation->messages()
            ->with('sender')
            ->oldest()
            ->get();

        return view('admin.chats.show', compact('conversation', 'messages'));
    }

    public function send(Request $request, ChatConversation $conversation)
    {
        $validated = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $message = DB::transaction(function () use ($conversation, $validated) {
            if (!$conversation->admin_id) {
                $conversation->update([
                    'admin_id' => Auth::id(),
                ]);
            }

            $message = ChatMessage::create([
                'conversation_id' => $conversation->id,
                'sender_id' => Auth::id(),
                'sender_role' => 'admin',
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
}
