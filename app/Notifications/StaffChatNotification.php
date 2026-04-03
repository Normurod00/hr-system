<?php

namespace App\Notifications;

use App\Models\StaffChat;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class StaffChatNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public StaffChat $chat,
        public User $sender,
        public string $messageText,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray(object $notifiable): array
    {
        $isHr = $notifiable->canAccessAdmin();

        return [
            'type' => 'staff_chat',
            'chat_id' => $this->chat->id,
            'sender_id' => $this->sender->id,
            'sender_name' => $this->sender->name,
            'message' => mb_substr($this->messageText, 0, 100),
            'url' => $isHr
                ? "/admin/staff-chat/{$this->chat->id}"
                : "/employee/staff-chat/{$this->chat->id}",
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}
