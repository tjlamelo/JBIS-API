<?php

declare(strict_types=1);

namespace App\Core\Application\Mail\Mailable;

use App\Core\Domain\Communication\Support\JbisMailbox;
use App\Core\Domain\Communication\Support\LocalizedCopy;
use App\Core\Domain\Communication\Support\MailBranding;
use App\Core\Domain\Identity\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class CameroonHolidayMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly User $user,
        public readonly string $holidayTitle,
        public readonly string $holidayBody,
        public readonly string $holidayCode,
        public readonly string $holidayDate,
        public readonly string $locale = 'fr',
    ) {}

    public function envelope(): Envelope
    {
        return JbisMailbox::transactionalEnvelope($this->holidayTitle);
    }

    public function content(): Content
    {
        $frontendUrl = rtrim((string) config('app.frontend_url', 'http://localhost:3000'), '/');
        $locale = LocalizedCopy::userLocale($this->user) ?: $this->locale;
        $userName = $this->user->profile?->first_name
            ?: $this->user->name
            ?: ($locale === 'en' ? 'friend' : 'ami(e)');

        return new Content(
            view: 'emails.system.notification',
            with: [
                ...MailBranding::viewData(),
                'title' => $this->holidayTitle,
                'headerSubtitle' => MailBranding::productName(),
                'userName' => $userName,
                'lines' => [
                    $this->holidayBody,
                    LocalizedCopy::line('notifications.holiday_mail.closing', $locale, [
                        'brand' => MailBranding::productName(),
                    ]),
                ],
                'actionUrl' => $frontendUrl.'/dashboard',
                'actionLabel' => LocalizedCopy::line('notifications.holiday_mail.action', $locale),
            ],
        );
    }
}
