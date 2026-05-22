<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Support\ApplicationRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Comptes de démonstration — un utilisateur par rôle Spatie.
 * Mot de passe commun en local : password
 */
class UserSeeder extends Seeder
{
    /** @var list<array{email: string, name: string, role: string, phone: string|null}> */
    private const DEMO_USERS = [
        [
            'email' => 'superadmin@jbis.cm',
            'name' => 'Super Admin JBIS',
            'role' => ApplicationRole::SUPERADMIN,
            'phone' => '+237600000001',
        ],
        [
            'email' => 'admin@jbis.cm',
            'name' => 'Admin JBIS',
            'role' => ApplicationRole::ADMIN,
            'phone' => '+237600000002',
        ],
        [
            'email' => 'staff@jbis.cm',
            'name' => 'Staff JBIS',
            'role' => ApplicationRole::STAFF,
            'phone' => '+237600000003',
        ],
        [
            'email' => 'partner@jbis.cm',
            'name' => 'Partenaire JBIS',
            'role' => ApplicationRole::PARTNER,
            'phone' => '+237600000004',
        ],
        [
            'email' => 'candidate@jbis.cm',
            'name' => 'Candidat Démo',
            'role' => ApplicationRole::CANDIDATE,
            'phone' => '+237600000005',
        ],
    ];

    public function run(): void
    {
        $password = Hash::make('password');

        foreach (self::DEMO_USERS as $row) {
            $user = User::query()->updateOrCreate(
                ['email' => $row['email']],
                [
                    'name' => $row['name'],
                    'phone_number1' => $row['phone'],
                    'password' => $password,
                    'email_verified_at' => now(),
                    'active' => true,
                ],
            );

            $user->syncRoles([$row['role']]);
        }
    }
}
