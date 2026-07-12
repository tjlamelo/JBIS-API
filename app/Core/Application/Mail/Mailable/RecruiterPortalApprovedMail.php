<?php

declare(strict_types=1);

namespace App\Core\Application\Mail\Mailable;

use App\Core\Domain\Communication\Support\JbisMailbox;
use App\Core\Domain\Communication\Support\MailBranding;
use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Recruiter\Models\RecruiterOrganization;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RecruiterPortalApprovedMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly User $user,
        public readonly RecruiterOrganization $organization,
    ) {}

    public function envelope(): Envelope
    {
        return JbisMailbox::transactionalEnvelope(
            'Votre portail recruteur '.MailBranding::productName().' est prêt'
        );
    }

    public function content(): Content
    {
        $frontendUrl = (string) config('app.frontend_url', 'http://localhost:3000');
        $portalUrl = $frontendUrl.'/recruiter';

        return new Content(
            view: 'emails.recruiter.portal-approved',
            with: [
                'user' => $this->user,
                'organization' => $this->organization,
                'portalUrl' => $portalUrl,
                'loginUrl' => $frontendUrl.'/login',
                ...MailBranding::viewData(),
            ],
        );
    }
}
