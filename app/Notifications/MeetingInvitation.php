<?php

namespace App\Notifications;

use App\Models\VideoMeeting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MeetingInvitation extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public VideoMeeting $meeting,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database', 'broadcast'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Приглашение на встречу: {$this->meeting->title}")
            ->greeting('Вы приглашены на видеовстречу')
            ->line("**{$this->meeting->title}**")
            ->line("Дата: {$this->meeting->scheduled_at->format('d.m.Y H:i')}")
            ->line("Длительность: {$this->meeting->duration_minutes} минут")
            ->action('Присоединиться', url("/admin/meetings/{$this->meeting->id}/room"))
            ->line('Пожалуйста, присоединитесь вовремя.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'meeting_invitation',
            'meeting_id' => $this->meeting->id,
            'title' => $this->meeting->title,
            'scheduled_at' => $this->meeting->scheduled_at->toISOString(),
            'message' => "Приглашение на встречу: {$this->meeting->title}",
            'url' => "/admin/meetings/{$this->meeting->id}",
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}
