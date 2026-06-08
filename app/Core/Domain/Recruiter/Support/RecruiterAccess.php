<?php

declare(strict_types=1);

namespace App\Core\Domain\Recruiter\Support;

use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Support\ApplicationRole;
use App\Core\Domain\Recruiter\Enums\RecruiterAssignmentStatus;
use App\Core\Domain\Recruiter\Models\RecruiterOrganization;
use App\Core\Domain\Recruiter\Models\RecruiterProfileAssignment;
use App\Core\Domain\Recruiter\Models\RecruiterProfileSubmission;

final class RecruiterAccess
{
    public function primaryOrganization(User $user): ?RecruiterOrganization
    {
        if (! $user->hasRole(ApplicationRole::RECRUITER)) {
            return null;
        }

        /** @var RecruiterOrganization|null $organization */
        $organization = $user->recruiterOrganizations()->orderByPivot('is_owner', 'desc')->first();

        return $organization;
    }

    public function belongsToOrganization(User $user, int $organizationId): bool
    {
        return $user->recruiterOrganizations()->where('recruiter_organizations.id', $organizationId)->exists();
    }

    public function canViewCandidate(User $recruiter, int $candidateUserId): bool
    {
        $organization = $this->primaryOrganization($recruiter);
        if ($organization === null) {
            return false;
        }

        $hasSubmission = RecruiterProfileSubmission::query()
            ->where('recruiter_organization_id', $organization->id)
            ->where('candidate_user_id', $candidateUserId)
            ->exists();

        if ($hasSubmission) {
            return true;
        }

        return RecruiterProfileAssignment::query()
            ->where('recruiter_organization_id', $organization->id)
            ->where('candidate_user_id', $candidateUserId)
            ->where('status', RecruiterAssignmentStatus::Active)
            ->exists();
    }

    public function resolveOrganizationFromApiHost(string $host): ?RecruiterOrganization
    {
        $host = strtolower(trim($host));
        if ($host === '') {
            return null;
        }

        return RecruiterOrganization::query()
            ->where('api_host', $host)
            ->orWhere('portal_host', $host)
            ->first();
    }

    public function parseSlugFromApiHost(string $host): ?string
    {
        $baseDomain = strtolower((string) config('services.cpanel.recruiter_base_domain', ''));
        $portalPrefix = (string) config('services.cpanel.recruiter_portal_prefix', 'recruteur');
        $pattern = '/^api\.([a-z0-9-]+)\.'.preg_quote($portalPrefix, '/').'\.'.preg_quote($baseDomain, '/').'$/i';

        if (preg_match($pattern, $host, $matches) === 1) {
            return $matches[1];
        }

        return null;
    }
}
