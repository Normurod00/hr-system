<?php

namespace App\Events\Chat;

use App\Models\ChatMessage;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $chatRoomId,
        public int $messageId,
        public int $senderId,
        public string $senderName,
        public string $body,
        public string $sentAt,
    ) {}

    public static function fromMessage(ChatMessage $message): self
    {
        return new self(
            chatRoomId: $message->chat_room_id,
            messageId: $message->id,
            senderId: $message->sender_id,
            senderName: $message->sender->name ?? 'Unknown',
            body: $message->body,
            sentAt: $message->created_at->toISOString(),
        );
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('chat.' . $this->chatRoomId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'message.sent';
    }
}
