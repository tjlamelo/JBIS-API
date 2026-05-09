<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class OfferDependenciesSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        // 1) User (offers.user_id)
        $admin = DB::table('users')->where('email', 'admin@jbis.cm')->first();
        if (! $admin) {
            $adminId = DB::table('users')->insertGetId([
                'name' => 'JBIS Admin',
                'email' => 'admin@jbis.cm',
                'password' => Hash::make('password'),
                'email_verified_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        } else {
            $adminId = (int) $admin->id;
        }

        // 2) Countries / Regions / Cities (offers.country_id, offers.city_id)
        $country = DB::table('countries')->where('code', 'CM')->first();
        if (! $country) {
            $countryId = DB::table('countries')->insertGetId([
                'name' => json_encode(['fr' => 'Cameroun', 'en' => 'Cameroon'], JSON_UNESCAPED_UNICODE),
                'code' => 'CM',
                'phone_code' => '237',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        } else {
            $countryId = (int) $country->id;
        }

        $region = DB::table('regions')->where('slug', 'centre')->first();
        if (! $region) {
            $regionId = DB::table('regions')->insertGetId([
                'country_id' => $countryId,
                'name' => json_encode(['fr' => 'Centre', 'en' => 'Center'], JSON_UNESCAPED_UNICODE),
                'slug' => 'centre',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        } else {
            $regionId = (int) $region->id;
        }

        $city = DB::table('cities')->where('slug', 'yaounde')->first();
        if (! $city) {
            DB::table('cities')->insert([
                'region_id' => $regionId,
                'name' => json_encode(['fr' => 'Yaoundé', 'en' => 'Yaounde'], JSON_UNESCAPED_UNICODE),
                'slug' => 'yaounde',
                'zip_code' => '0000',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // 3) Offer category (offers.offer_category_id)
        $category = DB::table('offer_categories')->where('slug', 'it-technology')->first();
        if (! $category) {
            DB::table('offer_categories')->insert([
                'name' => json_encode(['fr' => 'Informatique & Technologie', 'en' => 'IT & Technology'], JSON_UNESCAPED_UNICODE),
                'slug' => 'it-technology',
                'icon' => 'computer-chip',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // 4) Contract type (offers.contract_type_id)
        $contractType = DB::table('contract_types')->where('slug', 'full-time')->first();
        if (! $contractType) {
            DB::table('contract_types')->insert([
                'name' => json_encode(['fr' => 'CDI', 'en' => 'Full-time'], JSON_UNESCAPED_UNICODE),
                'slug' => 'full-time',
                'color_code' => '#00ff88',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // 5) Company (offers.company_id)
        $company = DB::table('companies')->where('name', 'JBIS Demo Company')->first();
        if (! $company) {
            DB::table('companies')->insert([
                'name' => 'JBIS Demo Company',
                'slug' => 'jbis-demo-company',
                'industry' => 'Technology',
                'country' => 'Cameroon',
                'city' => 'Yaounde',
                'address' => 'Yaounde Centre',
                'email' => 'contact+demo@jbis.cm',
                'is_approved' => true,
                'approved_by' => $adminId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // 6) Program (offers.program_id)
        $program = DB::table('programs')->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(name, '$.en')) = ?", ['Demo Program'])->first();
        if (! $program) {
            $suffix = Str::lower(Str::random(5));
            DB::table('programs')->insert([
                'name' => json_encode(['fr' => 'Programme Démo', 'en' => 'Demo Program'], JSON_UNESCAPED_UNICODE),
                'description' => json_encode(['fr' => 'Programme de test', 'en' => 'Testing program'], JSON_UNESCAPED_UNICODE),
                'slug' => json_encode(['fr' => 'programme-demo-'.$suffix, 'en' => 'demo-program-'.$suffix], JSON_UNESCAPED_UNICODE),
                'user_id' => $adminId,
                'procedure_cost' => 0,
                'currency' => 'XAF',
                'status' => 'active',
                'published_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // 7) Optional dependencies in offers migration
        // These tables are referenced by FK in offers migration but may be missing.
        if (Schema::hasTable('work_schedules')) {
            $exists = DB::table('work_schedules')->where('slug', 'day')->exists();
            if (! $exists) {
                DB::table('work_schedules')->insert([
                    'name' => json_encode(['fr' => 'Horaire de jour', 'en' => 'Day shift'], JSON_UNESCAPED_UNICODE),
                    'slug' => 'day',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        if (Schema::hasTable('education_levels')) {
            $exists = DB::table('education_levels')->where('slug', 'bac')->exists();
            if (! $exists) {
                DB::table('education_levels')->insert([
                    'name' => json_encode(['fr' => 'Baccalauréat', 'en' => 'High School Diploma'], JSON_UNESCAPED_UNICODE),
                    'slug' => 'bac',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }
}
