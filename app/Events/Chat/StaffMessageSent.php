<?php

namespace App\Events\Chat;

use App\Models\StaffChatMessage;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StaffMessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $chatId;
    public int $messageId;
    public int $senderId;
    public string $senderName;
    public string $body;
    public string $sentAt;

    public function __construct(StaffChatMessage $message)
    {
        $this->chatId = $message->staff_chat_id;
        $this->messageId = $message->id;
        $this->senderId = $message->sender_id;
        $this->senderName = $message->sender->name ?? 'Unknown';
        $this->body = $message->message;
        $this->sentAt = $message->created_at->toISOString();
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('staff-chat.' . $this->chatId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'message.sent';
    }
}
