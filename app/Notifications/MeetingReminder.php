<?php

namespace App\Notifications;

use App\Models\VideoMeeting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MeetingReminder extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public VideoMeeting $meeting,
        public int $minutesBefore,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database', 'broadcast'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Напоминание: {$this->meeting->title} через {$this->minutesBefore} мин")
            ->greeting('Напоминание о встрече')
            ->line("Встреча **{$this->meeting->title}** начнётся через {$this->minutesBefore} минут.")
            ->action('Присоединиться', url("/admin/meetings/{$this->meeting->id}/room"));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'meeting_reminder',
            'meeting_id' => $this->meeting->id,
            'title' => $this->meeting->title,
            'minutes_before' => $this->minutesBefore,
            'message' => "Встреча '{$this->meeting->title}' через {$this->minutesBefore} мин",
            'url' => "/admin/meetings/{$this->meeting->id}/room",
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}
