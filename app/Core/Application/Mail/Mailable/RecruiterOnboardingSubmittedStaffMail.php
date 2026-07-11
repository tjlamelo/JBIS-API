<?php

declare(strict_types=1);

namespace App\Core\Application\Mail\Mailable;

use App\Core\Domain\Communication\Support\JbisMailbox;
use App\Core\Domain\Recruiter\Models\RecruiterOnboardingApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RecruiterOnboardingSubmittedStaffMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly RecruiterOnboardingApplication $application,
    ) {}

    public function envelope(): Envelope
    {
        return JbisMailbox::transactionalEnvelope(
            'Nouvelle demande portail recruteur — '.$this->application->company_name,
        );
    }

    public function content(): Content
    {
        $frontendUrl = rtrim((string) config('services.recruiter.frontend_url', env('FRONTEND_URL', 'http://localhost:3000')), '/');

        return new Content(
            view: 'emails.recruiter.onboarding-submitted-staff',
            with: [
                'application' => $this->application,
                'reviewUrl' => $frontendUrl.'/admin/recruiters/onboarding/'.$this->application->id,
            ],
        );
    }
}
