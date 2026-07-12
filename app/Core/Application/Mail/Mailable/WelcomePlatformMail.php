<?php

namespace App\Core\Application\Mail\Mailable;

use App\Core\Domain\Communication\Support\JbisMailbox;
use App\Core\Domain\Identity\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomePlatformMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly User $user,
    ) {}

    public function envelope(): Envelope
    {
        return JbisMailbox::transactionalEnvelope('Bienvenue sur la plateforme JBIS');
    }

    public function content(): Content
    {
        $appUrl = rtrim((string) config('app.url', env('APP_URL', 'http://127.0.0.1:8000')), '/');

        return new Content(
            view: 'emails.welcome.platform',
            with: [
                'user' => $this->user,
                'frontendUrl' => (string) config('app.frontend_url', 'http://localhost:3000'),
                'logoUrl' => $appUrl.'/assets/img/logo-jbis.png',
            ],
        );
    }
}
