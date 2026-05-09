<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Liste des rôles par convention
        $roles = [
            'superadmin',
            'admin',
            'staff',
            'user',
            'guest',
            'partner', 
            'custom_user',
        ];

        // Récupère toutes les permissions
        $allPermissions = Permission::all();

        foreach ($roles as $roleName) {
            $role = Role::firstOrCreate(['name' => $roleName]);

            // Attribution des permissions selon le rôle
            switch ($roleName) {
                case 'superadmin':
                    // Toutes les permissions
                    $role->syncPermissions($allPermissions);
                    break;

                case 'admin':
                    // Permissions importantes (gestion des ressources principales)
                    $role->syncPermissions($allPermissions->whereIn('name', [
                        'user.view','user.create','user.update','user.delete',
                        'application.view','application.create','application.update','application.delete',
                        'offer.view','offer.create','offer.update','offer.delete',
                        'company.view','company.create','company.update','company.delete',
                        'program.view','program.create','program.update','program.delete',
                    ]));
                    break;

                case 'staff':
                    // Permissions opérationnelles
                    $role->syncPermissions($allPermissions->whereIn('name', [
                        'application.view','application.create','application.update',
                        'offer.view','offer.create','offer.update',
                        'company.view',
                    ]));
                    break;

                case 'partner':
                    // Permissions partenaires (similaire à staff mais limité si besoin)
                    $role->syncPermissions($allPermissions->whereIn('name', [
                        'application.view','application.create',
                        'offer.view',
                        'company.view',
                    ]));
                    break;

                case 'user':
                    // Permissions basiques
                    $role->syncPermissions($allPermissions->whereIn('name', [
                        'application.view','application.create',
                        'offer.view',
                    ]));
                    break;

                case 'guest':
                    // Permissions très limitées
                    $role->syncPermissions([]);
                    break;
            }
        }
    }
}
