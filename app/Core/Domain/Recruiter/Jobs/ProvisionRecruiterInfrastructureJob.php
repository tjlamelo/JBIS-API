<?php

declare(strict_types=1);

namespace App\Core\Domain\Recruiter\Jobs;

use App\Core\Domain\Recruiter\Models\RecruiterOrganization;
use App\Core\Domain\Recruiter\Services\RecruiterInfrastructureProvisioner;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

final class ProvisionRecruiterInfrastructureJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public function __construct(public readonly int $organizationId) {}

    public function handle(RecruiterInfrastructureProvisioner $provisioner): void
    {
        $organization = RecruiterOrganization::query()->find($this->organizationId);
        if ($organization === null) {
            return;
        }

        try {
            $provisioner->provision($organization);
        } catch (\Throwable $exception) {
            Log::error('Recruiter infrastructure provisioning failed', [
                'organization_id' => $this->organizationId,
                'message' => $exception->getMessage(),
            ]);

            $organization->status = \App\Core\Domain\Recruiter\Enums\RecruiterOrganizationStatus::Failed;
            $organization->provisioning_error = $exception->getMessage();
            $organization->save();

            throw $exception;
        }
    }
}
