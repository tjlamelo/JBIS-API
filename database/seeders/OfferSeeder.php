<?php

namespace Database\Seeders;

use App\Core\Domain\Catalog\Models\Offer;
use App\Core\Domain\Catalog\Models\Category;
use App\Core\Domain\Catalog\Models\Company;
use App\Core\Domain\Catalog\Models\Program;
use App\Core\Domain\Catalog\Models\ContractType;
use App\Core\Domain\Catalog\Models\OfferType;
use App\Core\Domain\Catalog\Models\WorkSchedule;
use App\Core\Domain\Catalog\Models\EducationLevel;
use App\Core\Domain\Catalog\Models\Benefit;
use App\Core\Domain\Catalog\Models\Trade;
use App\Core\Domain\Location\Models\City;
use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Candidacy\Models\RequiredDocument;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class OfferSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@jbis.cm')->first();
        
        // Référentiels principaux
        $itCat = Category::where('name->fr', 'LIKE', '%Informatique%')->first();
        $transpCat = Category::where('name->fr', 'LIKE', '%Logistique%')->first();
        
        // slug est une string dans la migration contract_types
        $contractCDI = ContractType::where('slug', 'full-time')->first();
        $contractCDD = ContractType::where('slug', 'fixed-term')->first()
            ?? ContractType::where('slug', 'cdd')->first();

        $offerTypeJob = OfferType::where('slug', 'job')->first();
        $workScheduleDay = WorkSchedule::where('slug', 'day')->first();
        $workScheduleRotating = WorkSchedule::where('slug', 'rotating')->first();
        $educationBac = EducationLevel::where('slug', 'bac')->first();
        $educationBepc = EducationLevel::where('slug', 'bepc')->first();

        $cityToronto = City::where('slug', 'toronto')->first();
        $cityDubai = City::where('slug', 'dubai-city')->first();

        $progCanada = Program::where('name->fr', 'LIKE', '%Express%')->first();
        $progDubai = Program::where('name->fr', 'LIKE', '%Dubaï%')->first();

        $amanTaxi = Company::where('name', 'Aman Taxi Dubai')->first();
        $benefits = Benefit::limit(3)->get();

        $taxiTrade = Trade::query()->where('slug', 'taxi-driver')->first();
        $fullstackTrade = Trade::query()->where('slug', 'full-stack-developer')->first();

        $offers = [
            [
                'trade_id' => $taxiTrade?->id,
                'city_id' => $cityDubai?->id,
                'country_id' => $cityDubai?->region->country_id,
                'category_id' => $transpCat?->id,
                'contract_type_id' => $contractCDD?->id,
                'offer_type_id' => $offerTypeJob?->id,
                'work_schedule_id' => $workScheduleRotating?->id,
                'education_level_id' => $educationBepc?->id,
                'company_id' => $amanTaxi?->id,
                'program_id' => $progDubai?->id,
                'salary_min' => 2500, 'salary_max' => 4000, 'currency' => 'AED',
                'address' => 'Aman Taxi HQ, Al Qusais',
                'requirements' => ['fr' => 'Permis de conduire valide, Anglais basique.', 'en' => 'Valid license, basic English.'],
                'responsibilities' => ['fr' => 'Transporter les clients.', 'en' => 'Transporting clients.'],
                'work_mode' => 'on-site',
                'status' => 'PUBLISHED',
            ],
            [
                'trade_id' => $fullstackTrade?->id,
                'city_id' => $cityToronto?->id,
                'country_id' => $cityToronto?->region->country_id,
                'category_id' => $itCat?->id,
                'contract_type_id' => $contractCDI?->id,
                'offer_type_id' => $offerTypeJob?->id,
                'work_schedule_id' => $workScheduleDay?->id,
                'education_level_id' => $educationBac?->id,
                'company_id' => Company::first()?->id,
                'program_id' => $progCanada?->id,
                'salary_min' => 4500, 'salary_max' => 6500, 'currency' => 'CAD',
                'address' => 'Downtown Toronto',
                'requirements' => ['fr' => '3 ans d\'expérience Laravel.', 'en' => '3 years Laravel experience.'],
                'responsibilities' => ['fr' => 'Développement features.', 'en' => 'Feature development.'],
                'work_mode' => 'hybrid',
                'status' => 'PUBLISHED',
            ],
        ];

        foreach ($offers as $o) {
            if (empty($o['trade_id'])) {
                continue;
            }

            $trade = Trade::query()->find($o['trade_id']);
            if (! $trade) {
                continue;
            }

            $slugFr = Str::slug($trade->getTranslation('name', 'fr', false)).'-'.Str::random(5);
            $slugEn = Str::slug($trade->getTranslation('name', 'en', false)).'-'.Str::random(5);
            
            $attributes = [
                'user_id'           => $admin?->id,
                'trade_id'          => $trade->id,
                'country_id'        => $o['country_id'],
                'city_id'           => $o['city_id'],
                'category_id' => $o['category_id'],
                'contract_type_id'  => $o['contract_type_id'],
                'offer_type_id'     => $o['offer_type_id'],
                'work_schedule_id'  => $o['work_schedule_id'],
                'education_level_id'=> $o['education_level_id'],
                'company_id'        => $o['company_id'],
                'program_id'        => $o['program_id'],
                'slug'              => [
                    'fr' => $slugFr,
                    'en' => $slugEn,
                ],
                'description'       => [
                    'fr' => "Opportunité via JBIS.",
                    'en' => "Opportunity via JBIS.",
                ],
                'address'           => $o['address'],
                'salary_min'        => $o['salary_min'],
                'salary_max'        => $o['salary_max'],
                'currency'          => $o['currency'],
                'is_salary_public'  => true,
                'is_company_public' => true,
                'requirements'      => $o['requirements'],
                'responsibilities'  => $o['responsibilities'],
                'status'            => $o['status'],
                'work_mode'         => $o['work_mode'],
                'published_at'      => now(),
                'available_positions' => rand(2, 5),
            ];

            $offer = Offer::query()
                ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(slug, '$.fr')) = ?", [$slugFr])
                ->first();

            if ($offer) {
                $offer->fill($attributes);
                $offer->save();
            } else {
                $offer = Offer::query()->create($attributes);
            }

            // Liaison des bénéfices via la table pivot
            $offer->benefits()->syncWithoutDetaching($benefits->pluck('id')->all());

            // Liaison des documents requis via la pivot offer_required_document
            if ($trade->slug === 'full-stack-developer') {
                $docs = RequiredDocument::whereIn('slug', [
                    'passeport-valide',
                    'diplome-le-plus-eleve',
                    'portfolio-github',
                ])->get();

                $offer->requiredDocuments()->sync(
                    $docs->mapWithKeys(
                        fn (RequiredDocument $doc, int $index): array => [
                            $doc->id => [
                                'is_mandatory' => $doc->slug !== 'portfolio-github',
                                'sort_order' => $index + 1,
                            ],
                        ]
                    )->toArray()
                );
            } else {
                $docs = RequiredDocument::whereIn('slug', [
                    'passeport-valide',
                    'certificat-de-visite-medicale',
                    'casier-judiciaire',
                ])->get();

                $offer->requiredDocuments()->sync(
                    $docs->mapWithKeys(
                        fn (RequiredDocument $doc, int $index): array => [
                            $doc->id => [
                                'is_mandatory' => true,
                                'sort_order' => $index + 1,
                            ],
                        ]
                    )->toArray()
                );
            }
        }
    }
}