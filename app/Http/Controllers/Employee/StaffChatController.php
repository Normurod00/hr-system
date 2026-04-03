<?php

namespace App\Http\Controllers\Employee;

use App\Events\Chat\StaffMessageSent;
use App\Http\Controllers\Controller;
use App\Models\StaffChat;
use App\Models\StaffChatMessage;
use App\Notifications\StaffChatNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StaffChatController extends Controller
{
    /**
     * Список чатов сотрудника (с HR)
     */
    public function index(): View
    {
        $userId = auth()->id();

        $chats = StaffChat::where('employee_id', $userId)
            ->with(['hr:id,name,email,avatar', 'lastMessage'])
            ->withCount(['messages as unread_count' => function ($q) use ($userId) {
                $q->where('sender_id', '!=', $userId)->whereNull('read_at');
            }])
            ->orderByDesc('last_message_at')
            ->get();

        return view('employee.staff-chat.index', compact('chats'));
    }

    /**
     * Страница чата с HR
     */
    public function show(StaffChat $chat): View
    {
        $userId = auth()->id();

        if ($chat->employee_id !== $userId) {
            abort(403);
        }

        $chat->load('hr');
        $chat->markAsReadFor($userId);

        $messages = $chat->messages()
            ->with('sender:id,name,avatar')
            ->orderBy('created_at')
            ->get();

        return view('employee.staff-chat.show', compact('chat', 'messages'));
    }

    /**
     * Отправить сообщение
     */
    public function sendMessage(Request $request, StaffChat $chat): JsonResponse
    {
        $userId = auth()->id();

        if ($chat->employee_id !== $userId) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $request->validate(['message' => 'required|string|max:5000']);

        $message = StaffChatMessage::create([
            'staff_chat_id' => $chat->id,
            'sender_id' => $userId,
            'message' => $request->input('message'),
        ]);

        $chat->update(['last_message_at' => now()]);

        $message->load('sender:id,name,avatar');

        // Broadcast
        broadcast(new StaffMessageSent($message))->toOthers();

        // Уведомление HR
        $chat->hr->notify(new StaffChatNotification($chat, auth()->user(), $message->message));

        return response()->json([
            'success' => true,
            'message' => [
                'id' => $message->id,
                'message' => $message->message,
                'sender_name' => $message->sender->name,
                'formatted_time' => $message->formatted_time,
                'is_mine' => true,
            ],
        ]);
    }

    /**
     * Получить новые сообщения (polling fallback)
     */
    public function getMessages(Request $request, StaffChat $chat): JsonResponse
    {
        $userId = auth()->id();

        if ($chat->employee_id !== $userId) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $lastId = $request->input('last_id', 0);

        $messages = $chat->messages()
            ->with('sender:id,name')
            ->where('id', '>', $lastId)
            ->orderBy('created_at')
            ->get()
            ->map(fn($m) => [
                'id' => $m->id,
                'message' => $m->message,
                'sender_name' => $m->sender->name,
                'formatted_time' => $m->formatted_time,
                'is_mine' => $m->sender_id === $userId,
            ]);

        if ($messages->count() > 0) {
            $chat->markAsReadFor($userId);
        }

        return response()->json(['messages' => $messages]);
    }
}
