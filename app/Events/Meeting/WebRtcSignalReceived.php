<?php

namespace App\Events\Meeting;

use App\Models\WebRtcSignal;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * ShouldBroadcastNow — мгновенная доставка без queue.
 * WebRTC сигналы (offer/answer/ICE) требуют <100ms латентности.
 */
class WebRtcSignalReceived implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $meetingId;
    public int $senderId;
    public ?int $recipientId;
    public string $type;
    public array $data;

    public function __construct(WebRtcSignal $signal)
    {
        $this->meetingId = $signal->meeting_id;
        $this->senderId = $signal->sender_id;
        $this->recipientId = $signal->recipient_id;
        $this->type = $signal->type;
        $this->data = $signal->data;
    }

    public function broadcastOn(): array
    {
        return [
            new PresenceChannel('meeting.' . $this->meetingId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'webrtc.signal';
    }
}
