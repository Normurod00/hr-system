<?php

use App\Models\ChatRoom;
use App\Models\StaffChat;
use App\Models\VideoMeeting;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
*/

// Персональные уведомления пользователя
Broadcast::channel('notifications.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});

// Чат между HR и кандидатом
Broadcast::channel('chat.{roomId}', function ($user, $roomId) {
    $room = ChatRoom::find($roomId);
    if (!$room) return false;

    return $room->participants()->where('user_id', $user->id)->exists()
        || $user->canAccessAdmin();
});

// Чат HR ↔ Сотрудник
Broadcast::channel('staff-chat.{chatId}', function ($user, $chatId) {
    $chat = StaffChat::find($chatId);
    if (!$chat) return false;
    return $chat->hr_id === $user->id || $chat->employee_id === $user->id;
});

// Видеокомната (presence channel — видно кто онлайн)
Broadcast::channel('meeting.{meetingId}', function ($user, $meetingId) {
    $meeting = VideoMeeting::find($meetingId);
    if (!$meeting || !$meeting->canJoin($user)) {
        return null;
    }

    return [
        'id' => $user->id,
        'name' => $user->name,
    ];
});
