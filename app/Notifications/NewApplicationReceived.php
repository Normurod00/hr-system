<?php

namespace App\Notifications;

use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewApplicationReceived extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Application $application,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database', 'broadcast'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $candidateName = $this->application->candidate?->name ?? 'Кандидат';
        $vacancyTitle = $this->application->vacancy?->title ?? 'Вакансия';

        return (new MailMessage)
            ->subject("Новая заявка: {$candidateName}")
            ->greeting('Новая заявка на вакансию')
            ->line("Кандидат **{$candidateName}** откликнулся на вакансию **{$vacancyTitle}**.")
            ->action('Посмотреть заявку', url("/admin/applications/{$this->application->id}"))
            ->line('Заявка ожидает рассмотрения.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'new_application',
            'application_id' => $this->application->id,
            'candidate_name' => $this->application->candidate?->name,
            'vacancy_title' => $this->application->vacancy?->title,
            'message' => "Новая заявка от {$this->application->candidate?->name}",
            'url' => "/admin/applications/{$this->application->id}",
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }

    public function broadcastType(): string
    {
        return 'new.application';
    }
}
