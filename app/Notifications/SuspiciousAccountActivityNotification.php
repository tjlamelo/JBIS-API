<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SuspiciousAccountActivityNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param array<int, string> $flags
     */
    public function __construct(
        private readonly string $title,
        private readonly string $ip,
        private readonly string $device,
        private readonly array $flags = [],
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage())
            ->subject('Alerte securite compte JBIS')
            ->greeting('Bonjour '.$notifiable->name.',')
            ->line($this->title)
            ->line('IP detectee : '.$this->ip)
            ->line('Appareil : '.$this->device);

        if ($this->flags !== []) {
            $message->line('Signaux : '.implode(', ', $this->flags));
        }

        return $message
            ->line('Si cette activite ne vient pas de vous, changez votre mot de passe immediatement.')
            ->action('Securiser mon compte', rtrim((string) env('FRONTEND_URL', 'http://localhost:3000'), '/').'/login');
    }
}
