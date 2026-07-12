<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Recruiter\Enums\RecruiterOrganizationStatus;
use App\Core\Domain\Recruiter\Models\RecruiterOrganization;
use Illuminate\Database\Seeder;

final class RecruiterOrganizationSeeder extends Seeder
{
    private const DEMO_SLUG = 'demo';

    public function run(): void
    {
        $recruiter = User::query()->where('email', 'recruiter@jbis.cm')->first();
        if ($recruiter === null) {
            return;
        }

        $organization = RecruiterOrganization::query()->updateOrCreate(
            ['slug' => self::DEMO_SLUG],
            [
                'name' => 'Recruteur Démo JBIS',
                'status' => RecruiterOrganizationStatus::Active,
                'portal_host' => null,
                'api_host' => null,
                'settings' => [],
                'provisioning_error' => null,
                'provisioned_at' => now(),
            ],
        );

        $organization->members()->syncWithoutDetaching([
            $recruiter->id => ['is_owner' => true],
        ]);
    }
}
