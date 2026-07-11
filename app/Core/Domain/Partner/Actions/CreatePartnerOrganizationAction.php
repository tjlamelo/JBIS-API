<?php

declare(strict_types=1);

namespace App\Core\Domain\Partner\Actions;

use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Support\ApplicationRole;
use App\Core\Domain\Partner\Enums\PartnerOrganizationStatus;
use App\Core\Domain\Partner\Models\PartnerOrganization;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class CreatePartnerOrganizationAction
{
    /**
     * @param  array{name: string, slug?: string|null, company_id?: int|null, owner_user_id?: int|null}  $data
     */
    public function execute(array $data): PartnerOrganization
    {
        return DB::transaction(function () use ($data): PartnerOrganization {
            $slug = isset($data['slug']) && is_string($data['slug']) && $data['slug'] !== ''
                ? Str::slug($data['slug'])
                : Str::slug((string) $data['name']);

            $organization = PartnerOrganization::query()->create([
                'name' => (string) $data['name'],
                'slug' => $slug,
                'company_id' => $data['company_id'] ?? null,
                'status' => PartnerOrganizationStatus::Active,
                'settings' => [],
            ]);

            if (! empty($data['owner_user_id'])) {
                $owner = User::query()->findOrFail((int) $data['owner_user_id']);
                $owner->syncRoles([ApplicationRole::PARTNER]);
                $organization->members()->attach($owner->id, ['is_owner' => true]);
            }

            return $organization->load('members:id,name,email');
        });
    }
}
