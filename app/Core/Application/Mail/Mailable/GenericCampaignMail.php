<?php

namespace App\Core\Application\Mail\Mailable;

use App\Core\Domain\Communication\Support\JbisMailbox;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
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
        $fromKey = (string) config('mailboxes.routing.newsletter', 'noreply');
        $replyKey = (string) config('mailboxes.routing.campaign_reply_to', 'contact');

        $from = $this->fromName
            ? new Address(JbisMailbox::address($fromKey), $this->fromName)
            : JbisMailbox::from($fromKey);

        $replyTo = $this->replyTo
            ? [new Address($this->replyTo)]
            : [JbisMailbox::from($replyKey)];

        return new Envelope(
            from: $from,
            replyTo: $replyTo,
            subject: $this->subjectLine,
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
