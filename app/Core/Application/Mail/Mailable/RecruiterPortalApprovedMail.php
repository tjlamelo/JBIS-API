<?php

declare(strict_types=1);

namespace App\Core\Application\Mail\Mailable;

use App\Core\Domain\Communication\Support\JbisMailbox;
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
        return JbisMailbox::transactionalEnvelope('Votre portail recruteur JBIS est prêt');
    }

    public function content(): Content
    {
        $appUrl = rtrim((string) config('app.url', 'http://127.0.0.1:8000'), '/');
        $frontendUrl = rtrim((string) config('services.recruiter.frontend_url', env('FRONTEND_URL', 'http://localhost:3000')), '/');
        $portalUrl = $this->organization->portal_host
            ? 'https://'.$this->organization->portal_host
            : $frontendUrl.'/recruiter';

        return new Content(
            view: 'emails.recruiter.portal-approved',
            with: [
                'user' => $this->user,
                'organization' => $this->organization,
                'portalUrl' => $portalUrl,
                'loginUrl' => $frontendUrl.'/login',
                'logoUrl' => $appUrl.'/assets/img/logo-jbis.png',
            ],
        );
    }
}
