<?php

namespace App\Notifications;

use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApplicationStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Application $application,
        public string $newStatus,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database', 'broadcast'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $vacancy = $this->application->vacancy?->title ?? 'вакансию';
        $statusLabels = [
            'in_review' => 'рассматривается',
            'invited' => 'Вы приглашены на собеседование',
            'rejected' => 'отклонена',
            'hired' => 'Вы приняты на работу',
        ];
        $label = $statusLabels[$this->newStatus] ?? $this->newStatus;

        $mail = (new MailMessage)
            ->subject("Обновление заявки: {$vacancy}")
            ->greeting("Обновление по вашей заявке")
            ->line("Ваша заявка на вакансию **{$vacancy}**: {$label}.");

        if ($this->newStatus === 'invited') {
            $mail->action('Перейти в чат', url("/chat/{$this->application->id}"));
        }

        return $mail;
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'application_status',
            'application_id' => $this->application->id,
            'vacancy_title' => $this->application->vacancy?->title,
            'new_status' => $this->newStatus,
            'message' => "Статус заявки изменён на: {$this->newStatus}",
            'url' => "/chat/{$this->application->id}",
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}
