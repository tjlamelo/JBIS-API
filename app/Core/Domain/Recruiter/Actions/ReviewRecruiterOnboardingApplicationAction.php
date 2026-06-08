<?php

declare(strict_types=1);

namespace App\Core\Domain\Recruiter\Actions;

use App\Core\Application\Mail\Mailable\RecruiterPortalApprovedMail;
use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Recruiter\Enums\RecruiterOnboardingStatus;
use App\Core\Domain\Recruiter\Enums\RecruiterOrganizationStatus;
use App\Core\Domain\Recruiter\Jobs\ProvisionRecruiterInfrastructureJob;
use App\Core\Domain\Recruiter\Models\RecruiterOnboardingApplication;
use Illuminate\Support\Facades\Mail;

final class ReviewRecruiterOnboardingApplicationAction
{
    public function __construct(
        private readonly CreateRecruiterOrganizationAction $createOrganization,
    ) {}

    /**
     * @param  array{decision: string, staff_note?: string|null, rejection_reason?: string|null}  $payload
     */
    public function execute(RecruiterOnboardingApplication $application, User $reviewer, array $payload): RecruiterOnboardingApplication
    {
        $decision = (string) ($payload['decision'] ?? '');
        $status = match ($decision) {
            'approve' => RecruiterOnboardingStatus::Approved,
            'reject' => RecruiterOnboardingStatus::Rejected,
            'needs_changes' => RecruiterOnboardingStatus::NeedsChanges,
            'in_review' => RecruiterOnboardingStatus::InReview,
            default => throw new \InvalidArgumentException(__('Décision de modération invalide.')),
        };

        $application->status = $status;
        $application->reviewed_by = $reviewer->id;
        $application->reviewed_at = now();
        $application->staff_note = $payload['staff_note'] ?? null;
        $application->rejection_reason = $payload['rejection_reason'] ?? null;
        $application->save();

        if ($status === RecruiterOnboardingStatus::Approved) {
            $this->approveAndProvision($application);
        }

        return $application->fresh(['documents', 'applicant', 'organization', 'reviewer']);
    }

    private function approveAndProvision(RecruiterOnboardingApplication $application): void
    {
        $applicant = $application->applicant;
        if ($applicant === null) {
            throw new \InvalidArgumentException(__('Demandeur introuvable.'));
        }

        $organization = $this->createOrganization->execute([
            'name' => $application->company_name,
            'slug' => $application->desired_slug,
            'owner_user_id' => $applicant->id,
        ]);

        $organization->status = RecruiterOrganizationStatus::Pending;
        $organization->save();

        $application->recruiter_organization_id = $organization->id;
        $application->save();

        if (config('services.recruiter.auto_provision_on_approval', false)) {
            ProvisionRecruiterInfrastructureJob::dispatch($organization->id);
        } else {
            $organization->status = RecruiterOrganizationStatus::Active;
            $organization->provisioned_at = now();
            $organization->save();
        }

        Mail::to($applicant->email)->send(new RecruiterPortalApprovedMail($applicant, $organization));
    }
}
