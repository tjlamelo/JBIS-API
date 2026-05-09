<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $models = [
            'Agency',
            'Application',
            'ApplicationDocument',
            'Archive',
            'Certification',
            'CertificationOffer',
            'Company',
            'Education',
            'Experience',
            'InterestsAndHobbies',
            'Internship',
            'Interview',
            'Offer',
            'Language',
            'Payment',
            'PaymentInstallment',
            'PaymentSchedule',
            'ProcessFlow',
            'ProcessStep',
            'ProfessionalProfile',
            'Program',
            'RendezVous',
            'RequiredDocument',
            'Training',
            'User',
            'UserDevice',
            'UserDocument',
            'UserProfile',
            'UserSettings',
            'user_certification',
            'user_training'
        ];

        $actions = ['view', 'create', 'update', 'delete'];

        foreach ($models as $model) {
            foreach ($actions as $action) {
                Permission::firstOrCreate(['name' => strtolower($model) . '.' . $action]);
            }
        }
           Permission::firstOrCreate(['name' => 'admin.access']);
    }
}
