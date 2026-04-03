<?php

namespace App\Events\Meeting;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MeetingEnded implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $meetingId,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PresenceChannel('meeting.' . $this->meetingId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'meeting.ended';
    }
}
