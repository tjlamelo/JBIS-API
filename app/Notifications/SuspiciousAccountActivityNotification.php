<?php

namespace App\Notifications;

use App\Core\Domain\Communication\Support\MailBranding;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SuspiciousAccountActivityNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param  array<int, string>  $flags
     */
    public function __construct(
        private readonly string $title,
        private readonly string $ip,
        private readonly string $device,
        private readonly array $flags = [],
        private readonly ?int $riskScore = null,
        private readonly ?string $riskLevel = null,
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
        $brand = MailBranding::productName();
        $lines = [
            $this->title,
            'IP détectée : '.$this->ip,
            'Appareil : '.$this->device,
        ];

        if ($this->flags !== []) {
            $lines[] = 'Signaux : '.implode(', ', $this->flags);
        }

        if ($this->riskScore !== null) {
            $lines[] = 'Score de risque : '.$this->riskScore.($this->riskLevel ? ' ('.$this->riskLevel.')' : '');
        }

        $lines[] = 'Si cette activité ne vient pas de vous, changez votre mot de passe immédiatement.';

        return (new MailMessage)
            ->subject('Alerte sécurité compte '.$brand)
            ->view('emails.system.notification', [
                ...MailBranding::viewData(),
                'title' => 'Alerte sécurité — '.$brand,
                'headerSubtitle' => 'Sécurité du compte',
                'userName' => $notifiable->name ?? 'utilisateur',
                'lines' => $lines,
                'actionUrl' => (string) config('app.frontend_url', 'http://localhost:3000').'/login',
                'actionLabel' => 'Sécuriser mon compte',
            ]);
    }
}
