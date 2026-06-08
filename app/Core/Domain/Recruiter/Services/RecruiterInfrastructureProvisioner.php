<?php

declare(strict_types=1);

namespace App\Core\Domain\Recruiter\Services;

use App\Core\Domain\Communication\Contracts\MailboxProvisioner;
use App\Core\Domain\Communication\Contracts\SubdomainProvisioner;
use App\Core\Domain\Communication\Services\CpanelMailboxProvisionerService;
use App\Core\Domain\Recruiter\Enums\RecruiterOrganizationStatus;
use App\Core\Domain\Recruiter\Models\RecruiterOrganization;
use App\Core\Domain\Recruiter\Support\RecruiterHostBuilder;
use Illuminate\Support\Str;

final class RecruiterInfrastructureProvisioner
{
    public function __construct(
        private readonly SubdomainProvisioner $subdomainProvisioner,
        private readonly MailboxProvisioner $mailboxProvisioner,
        private readonly RecruiterHostBuilder $hosts,
    ) {}

    public function provision(RecruiterOrganization $organization): RecruiterOrganization
    {
        $organization->status = RecruiterOrganizationStatus::Provisioning;
        $organization->provisioning_error = null;
        $organization->save();

        $docRoot = (string) config('services.cpanel.recruiter_docroot', '');
        if ($docRoot === '') {
            return $this->fail($organization, 'CPANEL_RECRUITER_DOCROOT non configuré.');
        }

        $slug = $organization->slug;
        $portalFqdn = $organization->portal_host ?: $this->hosts->portalHost($slug);
        $apiFqdn = $organization->api_host ?: $this->hosts->apiHost($slug);

        $portalParts = $this->hosts->parsePortalHost($portalFqdn);
        $apiParts = $this->hosts->parseApiHost($apiFqdn);

        if ($portalParts === null || $apiParts === null) {
            return $this->fail($organization, 'Hôtes portail/API invalides.');
        }

        $portalResult = $this->subdomainProvisioner->createSubdomain(
            $portalParts['subdomain'],
            $portalParts['rootdomain'],
            $docRoot,
        );

        if (! $portalResult->success) {
            return $this->fail($organization, $portalResult->message);
        }

        $apiResult = $this->subdomainProvisioner->createSubdomain(
            $apiParts['subdomain'],
            $apiParts['rootdomain'],
            $docRoot,
        );

        if (! $apiResult->success) {
            return $this->fail($organization, $apiResult->message);
        }

        $mailboxEmail = null;
        $mailboxPassword = Str::password(16);

        if ($this->mailboxProvisioner instanceof CpanelMailboxProvisionerService) {
            $mailboxResult = $this->mailboxProvisioner->createMailboxOnDomain(
                'contact',
                $portalFqdn,
                $mailboxPassword,
            );

            if ($mailboxResult->success) {
                $mailboxEmail = $mailboxResult->email;
            }
        }

        $organization->portal_host = $portalFqdn;
        $organization->api_host = $apiFqdn;
        $organization->mailbox_email = $mailboxEmail;
        $organization->status = RecruiterOrganizationStatus::Active;
        $organization->provisioned_at = now();
        $organization->provisioning_error = null;
        $organization->save();

        return $organization;
    }

    private function fail(RecruiterOrganization $organization, string $message): RecruiterOrganization
    {
        $organization->status = RecruiterOrganizationStatus::Failed;
        $organization->provisioning_error = $message;
        $organization->save();

        return $organization;
    }
}
