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

final class OperationsAlertMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly User $user,
        public readonly string $alertSubject,
        public readonly string $alertBody,
        public readonly string $actionUrl,
    ) {}

    public function envelope(): Envelope
    {
        return JbisMailbox::transactionalEnvelope($this->alertSubject);
    }

    public function content(): Content
    {
        $frontend = rtrim((string) config('app.frontend_url', 'http://localhost:3000'), '/');

        return new Content(
            view: 'emails.operations.alert',
            with: [
                'user' => $this->user,
                'alertBody' => $this->alertBody,
                'actionUrl' => str_starts_with($this->actionUrl, 'http')
                    ? $this->actionUrl
                    : $frontend.$this->actionUrl,
                ...MailBranding::viewData(),
            ],
        );
    }
}
