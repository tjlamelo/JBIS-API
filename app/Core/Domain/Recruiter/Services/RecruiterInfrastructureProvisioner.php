<?php

declare(strict_types=1);

namespace App\Core\Domain\Recruiter\Services;

use App\Core\Domain\Recruiter\Enums\RecruiterOrganizationStatus;
use App\Core\Domain\Recruiter\Models\RecruiterOrganization;

/**
 * Active une organisation recruteur sans créer de sous-domaines cPanel.
 * Les recruteurs utilisent le portail partagé ({FRONTEND_URL}/recruiter).
 */
final class RecruiterInfrastructureProvisioner
{
    public function provision(RecruiterOrganization $organization): RecruiterOrganization
    {
        $organization->status = RecruiterOrganizationStatus::Active;
        $organization->portal_host = null;
        $organization->api_host = null;
        $organization->mailbox_email = null;
        $organization->provisioning_error = null;
        $organization->provisioned_at = now();
        $organization->save();

        return $organization;
    }
}
