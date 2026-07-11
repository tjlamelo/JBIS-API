<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Partner\Actions\CreatePartnerOrganizationAction;
use Illuminate\Database\Seeder;

class PartnerOrganizationSeeder extends Seeder
{
    public function run(): void
    {
        $partner = User::query()->where('email', 'partner@jbis.cm')->first();
        if ($partner === null) {
            return;
        }

        app(CreatePartnerOrganizationAction::class)->execute([
            'name' => 'Institut SIANTOU',
            'slug' => 'institut-siantou',
            'owner_user_id' => $partner->id,
        ]);
    }
}
