<?php

namespace App\Http\Controllers\Admin;

use App\Events\Chat\StaffMessageSent;
use App\Http\Controllers\Controller;
use App\Models\StaffChat;
use App\Models\StaffChatMessage;
use App\Models\User;
use App\Notifications\StaffChatNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StaffChatController extends Controller
{
    /**
     * Список всех сотрудников + чатов
     */
    public function index(Request $request): View
    {
        $hrId = auth()->id();

        // Все сотрудники (employee, hr, admin — кроме текущего)
        $employees = User::where('id', '!=', $hrId)
            ->whereIn('role', ['employee', 'hr', 'admin'])
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'role', 'avatar']);

        // Существующие чаты с непрочитанными
        $chats = StaffChat::where('hr_id', $hrId)
            ->with(['employee:id,name,email,avatar', 'lastMessage'])
            ->withCount(['messages as unread_count' => function ($q) use ($hrId) {
                $q->where('sender_id', '!=', $hrId)->whereNull('read_at');
            }])
            ->orderByDesc('last_message_at')
            ->get()
            ->keyBy('employee_id');

        return view('admin.staff-chat.index', compact('employees', 'chats'));
    }

    /**
     * Страница чата с конкретным сотрудником
     */
    public function show(StaffChat $chat): View
    {
        $hrId = auth()->id();

        // Проверяем что это наш чат
        if ($chat->hr_id !== $hrId) {
            abort(403);
        }

        $chat->load('employee');
        $chat->markAsReadFor($hrId);

        $messages = $chat->messages()
            ->with('sender:id,name,avatar')
            ->orderBy('created_at')
            ->get();

        return view('admin.staff-chat.show', compact('chat', 'messages'));
    }

    /**
     * Начать чат с сотрудником (или открыть существующий)
     */
    public function start(User $employee)
    {
        $chat = StaffChat::getOrCreate(auth()->id(), $employee->id);

        return redirect()->route('admin.staff-chat.show', $chat);
    }

    /**
     * Отправить сообщение
     */
    public function sendMessage(Request $request, StaffChat $chat): JsonResponse
    {
        if ($chat->hr_id !== auth()->id()) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $request->validate(['message' => 'required|string|max:5000']);

        $message = StaffChatMessage::create([
            'staff_chat_id' => $chat->id,
            'sender_id' => auth()->id(),
            'message' => $request->input('message'),
        ]);

        $chat->update(['last_message_at' => now()]);

        $message->load('sender:id,name,avatar');

        // Broadcast
        broadcast(new StaffMessageSent($message))->toOthers();

        // Уведомление сотруднику
        $chat->employee->notify(new StaffChatNotification($chat, auth()->user(), $message->message));

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
        if ($chat->hr_id !== auth()->id()) {
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
                'is_mine' => $m->sender_id === auth()->id(),
            ]);

        if ($messages->count() > 0) {
            $chat->markAsReadFor(auth()->id());
        }

        return response()->json(['messages' => $messages]);
    }
}
