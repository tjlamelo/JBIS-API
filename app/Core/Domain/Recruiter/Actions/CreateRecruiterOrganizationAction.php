<?php

declare(strict_types=1);

namespace App\Core\Domain\Recruiter\Actions;

use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Support\ApplicationRole;
use App\Core\Domain\Recruiter\Enums\RecruiterOrganizationStatus;
use App\Core\Domain\Recruiter\Models\RecruiterOrganization;
use App\Core\Domain\Recruiter\Support\RecruiterHostBuilder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class CreateRecruiterOrganizationAction
{
    public function __construct(private readonly RecruiterHostBuilder $hosts) {}

    /**
     * @param  array{name: string, slug?: string|null, company_id?: int|null, owner_user_id?: int|null}  $data
     */
    public function execute(array $data): RecruiterOrganization
    {
        return DB::transaction(function () use ($data): RecruiterOrganization {
            $slug = isset($data['slug']) && is_string($data['slug']) && $data['slug'] !== ''
                ? Str::slug($data['slug'])
                : Str::slug((string) $data['name']);

            $organization = RecruiterOrganization::query()->create([
                'name' => (string) $data['name'],
                'slug' => $slug,
                'company_id' => $data['company_id'] ?? null,
                'status' => RecruiterOrganizationStatus::Pending,
                'portal_host' => $this->hosts->portalHost($slug),
                'api_host' => $this->hosts->apiHost($slug),
                'settings' => [],
            ]);

            if (! empty($data['owner_user_id'])) {
                $owner = User::query()->findOrFail((int) $data['owner_user_id']);
                $owner->syncRoles([ApplicationRole::RECRUITER]);
                $organization->members()->attach($owner->id, ['is_owner' => true]);
            }

            return $organization->load('members:id,name,email');
        });
    }
}
