<?php

namespace App\Core\Application\Mail\Mailable;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class GenericCampaignMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        private readonly string $subjectLine,
        private readonly ?string $bodyHtml,
        private readonly ?array $content,
        private readonly ?string $fromName = null,
        private readonly ?string $replyTo = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subjectLine,
            using: [
                function (\Symfony\Component\Mime\Email $message): void {
                    if ($this->fromName) {
                        $message->from(config('mail.from.address'), $this->fromName);
                    }

                    if ($this->replyTo) {
                        $message->replyTo($this->replyTo);
                    }
                },
            ],
        );
    }

    public function content(): Content
    {
        $appUrl = rtrim((string) config('app.url', env('APP_URL', 'http://127.0.0.1:8000')), '/');

        return new Content(
            view: 'emails.campaign.generic',
            with: [
                'content' => $this->bodyHtml,
                'template' => $this->content,
                'logoUrl' => $appUrl.'/assets/img/logo-jbis.png',
            ],
        );
    }
}
