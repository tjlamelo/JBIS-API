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

final class StaffWelcomeMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly User $user,
        public readonly string $language = 'fr',
    ) {
        $this->locale($language);
    }

    public function envelope(): Envelope
    {
        $title = LocalizedCopy::line('notifications.staff_welcome.title', $this->language);

        return JbisMailbox::transactionalEnvelope($title);
    }

    public function content(): Content
    {
        $frontend = rtrim((string) config('app.frontend_url', 'http://localhost:3000'), '/');
        $name = $this->user->profile?->first_name ?: $this->user->name ?: 'collègue';

        return new Content(
            view: 'emails.welcome.staff',
            with: [
                'user' => $this->user,
                'userName' => $name,
                'intro' => LocalizedCopy::line('notifications.staff_welcome.mail_intro', $this->language),
                'actionLabel' => LocalizedCopy::line('notifications.staff_welcome.mail_action', $this->language),
                'actionUrl' => $frontend.'/admin/tasks',
                'locale' => $this->language,
                ...MailBranding::viewData(),
            ],
        );
    }
}
