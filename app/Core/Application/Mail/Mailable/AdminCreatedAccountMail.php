<?php

declare(strict_types=1);

namespace App\Core\Application\Mail\Mailable;

use App\Core\Domain\Communication\Support\JbisMailbox;
use App\Core\Domain\Communication\Support\MailBranding;
use App\Core\Domain\Identity\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class AdminCreatedAccountMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly User $user,
        public readonly string $plainPassword,
    ) {}

    public function envelope(): Envelope
    {
        return JbisMailbox::transactionalEnvelope(
            __('Votre compte :brand a été créé', ['brand' => MailBranding::productName()]),
        );
    }

    public function content(): Content
    {
        $frontend = rtrim((string) config('app.frontend_url', 'http://localhost:3000'), '/');
        $loginUrl = $frontend.'/login?'.http_build_query([
            'email' => $this->user->email,
            'first_login' => '1',
        ]);

        return new Content(
            view: 'emails.welcome.admin-created-account',
            with: [
                'user' => $this->user,
                'userName' => $this->user->profile?->first_name ?: $this->user->name,
                'plainPassword' => $this->plainPassword,
                'loginUrl' => $loginUrl,
                'frontendUrl' => $frontend,
                ...MailBranding::viewData(),
            ],
        );
    }
}
