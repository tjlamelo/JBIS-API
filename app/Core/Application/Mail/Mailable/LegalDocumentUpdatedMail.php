<?php

declare(strict_types=1);

namespace App\Core\Application\Mail\Mailable;

use App\Core\Domain\Identity\Models\LegalDocument;
use App\Core\Domain\Identity\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class LegalDocumentUpdatedMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly User $user,
        public readonly LegalDocument $document,
    ) {}

    public function envelope(): Envelope
    {
        $label = $this->documentLabel();

        return new Envelope(
            subject: "JBIS — Mise à jour : {$label}",
        );
    }

    public function content(): Content
    {
        $appUrl = rtrim((string) config('app.url', env('APP_URL', 'http://127.0.0.1:8000')), '/');
        $frontendUrl = rtrim((string) env('FRONTEND_URL', 'http://localhost:3000'), '/');

        return new Content(
            view: 'emails.legal.document-updated',
            with: [
                'user' => $this->user,
                'document' => $this->document,
                'documentLabel' => $this->documentLabel(),
                'consentsUrl' => "{$frontendUrl}/settings/consents",
                'logoUrl' => "{$appUrl}/assets/img/logo-jbis.png",
            ],
        );
    }

    private function documentLabel(): string
    {
        $title = $this->document->getTranslation('title', 'fr', false)
            ?: $this->document->getTranslation('title', 'en', false);

        if (is_string($title) && $title !== '') {
            return $title;
        }

        return match ($this->document->type) {
            'TERMS' => 'Conditions générales',
            'PRIVACY' => 'Politique de confidentialité',
            'COOKIES' => 'Politique cookies',
            default => 'Document légal',
        };
    }
}
