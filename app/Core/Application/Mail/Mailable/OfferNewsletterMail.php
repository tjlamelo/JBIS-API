<?php

declare(strict_types=1);

namespace App\Core\Application\Mail\Mailable;

use App\Core\Domain\Communication\Support\JbisMailbox;
use App\Core\Domain\Communication\Support\MailBranding;
use App\Core\Domain\Communication\Models\NewsletterSubscription;
use App\Core\Domain\Communication\Services\NewsletterUnsubscribeUrlBuilder;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;

class OfferNewsletterMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * @param  array{national: list<array<string, mixed>>, international: list<array<string, mixed>>, has_national: bool, has_international: bool}  $content
     */
    public function __construct(
        public readonly NewsletterSubscription $subscription,
        public readonly array $content,
        public readonly string $language = 'fr',
    ) {
        $this->locale($language);
    }

    public function envelope(): Envelope
    {
        $brand = MailBranding::productName();
        $subject = $this->language === 'en'
            ? $brand.' — New job opportunities'
            : $brand.' — Nouvelles offres d\'emploi';

        return JbisMailbox::transactionalEnvelope($subject);
    }

    public function headers(): Headers
    {
        $unsubscribeUrl = app(NewsletterUnsubscribeUrlBuilder::class)->build($this->subscription);

        return new Headers(
            text: [
                'List-Unsubscribe' => '<'.$unsubscribeUrl.'>',
                'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click',
            ],
        );
    }

    public function content(): Content
    {
        $frontendUrl = (string) config('app.frontend_url', 'http://localhost:3000');
        $unsubscribeUrl = app(NewsletterUnsubscribeUrlBuilder::class)->build($this->subscription);
        $brand = MailBranding::productName();

        $copy = $this->language === 'en' ? [
            'greeting' => 'Hello'.($this->personalizedName() !== '' ? ' '.$this->personalizedName() : ''),
            'intro' => 'Here are the latest job opportunities matching your '.$brand.' newsletter preferences (scope: '.$this->scopeLabel('en').').',
            'national_title' => 'National opportunities (Cameroon)',
            'international_title' => 'International opportunities',
            'view_all' => 'Browse all offers',
            'unsubscribe' => 'Unsubscribe',
            'unsubscribe_help' => 'No longer want these emails? You can unsubscribe at any time.',
            'footer' => 'You receive this email because you subscribed to the '.$brand.' newsletter.',
        ] : [
            'greeting' => 'Bonjour'.($this->personalizedName() !== '' ? ' '.$this->personalizedName() : ''),
            'intro' => 'Voici les dernières offres d\'emploi correspondant à vos préférences newsletter '.$brand.' (périmètre : '.$this->scopeLabel('fr').').',
            'national_title' => 'Opportunités nationales (Cameroun)',
            'international_title' => 'Opportunités internationales',
            'view_all' => 'Voir toutes les offres',
            'unsubscribe' => 'Se désabonner',
            'unsubscribe_help' => 'Vous ne souhaitez plus recevoir ces e-mails ? Vous pouvez vous désabonner à tout moment.',
            'footer' => 'Vous recevez cet e-mail car vous êtes inscrit à la newsletter '.$brand.'.',
        ];

        return new Content(
            view: 'emails.newsletter.offers',
            with: [
                'subscription' => $this->subscription,
                'content' => $this->content,
                'copy' => $copy,
                'locale' => $this->language,
                'offersUrl' => $frontendUrl.'/offer',
                'unsubscribeUrl' => $unsubscribeUrl,
                ...MailBranding::viewData(),
            ],
        );
    }

    private function personalizedName(): string
    {
        return trim((string) ($this->subscription->name ?? ''));
    }

    private function scopeLabel(string $locale): string
    {
        $scope = $this->subscription->scope instanceof \BackedEnum
            ? $this->subscription->scope->value
            : (string) $this->subscription->scope;

        return match (strtolower($scope)) {
            'national' => $locale === 'en' ? 'national (Cameroon)' : 'national (Cameroun)',
            'international' => $locale === 'en' ? 'international' : 'international',
            default => $locale === 'en' ? 'national & international' : 'national et international',
        };
    }
}
