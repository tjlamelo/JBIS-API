<?php

declare(strict_types=1);

namespace Tests\Unit\Ai\Intel;

use App\Core\Domain\Shared\Ai\Intel\DocumentExtractionDraftEnricher;
use App\Core\Domain\Location\Models\Language;
use Database\Seeders\LanguageLevelSeeder;
use Database\Seeders\LanguageSeeder;
use Tests\TestCase;

final class DocumentExtractionDraftEnricherTest extends TestCase
{
    public function test_reclassifies_internship_and_normalizes_phone_and_languages(): void
    {
        $this->seed([LanguageSeeder::class, LanguageLevelSeeder::class]);

        $enricher = $this->app->make(DocumentExtractionDraftEnricher::class);

        $draft = $enricher->enrich([
            'user_profile' => [
                'first_name' => 'DUPONT',
                'last_name' => 'Jean',
                'residence_city_name' => 'Yaoundé',
                'phone_number2' => '690000000',
            ],
            'experiences' => [
                [
                    'job_title' => 'Stagiaire développeur',
                    'company_name' => 'ACME',
                    'experience_type' => 'internship',
                    'city_name' => 'Douala',
                ],
                [
                    'job_title' => 'Développeur',
                    'company_name' => 'JBIS',
                    'experience_type' => 'employment',
                    'city_name' => 'Paris',
                ],
            ],
            'languages' => [
                ['language_name' => 'Français courant'],
                ['language_name' => 'English', 'proficiency_level' => 'B2'],
            ],
            'skills' => 'PHP, Laravel, SQL',
        ], 'CV');

        self::assertSame('Jean', $draft['user_profile']['first_name'] ?? null);
        self::assertSame('DUPONT', $draft['user_profile']['last_name'] ?? null);
        self::assertStringStartsWith('+237', (string) ($draft['user_profile']['phone_number2'] ?? ''));
        self::assertCount(1, $draft['experiences'] ?? []);
        self::assertCount(1, $draft['internships'] ?? []);
        self::assertCount(2, $draft['languages'] ?? []);
        self::assertNotNull($draft['languages'][0]['resolved_language_id'] ?? null);
        self::assertCount(3, $draft['skills'] ?? []);
    }

    public function test_preserves_african_full_name_and_disambiguates_organization(): void
    {
        $enricher = $this->app->make(DocumentExtractionDraftEnricher::class);

        $draft = $enricher->enrich([
            'user_profile' => [
                'full_name' => 'ATANGANA OWONA Francois Mavis',
            ],
            'educations' => [
                [
                    'degree' => 'Université de Douala',
                    'institution_name' => 'Licence Informatique',
                ],
            ],
            'interests' => 'Football, Lecture, Bénévolat',
        ], 'CV');

        self::assertSame('Francois Mavis', $draft['user_profile']['first_name'] ?? null);
        self::assertSame('ATANGANA OWONA', $draft['user_profile']['last_name'] ?? null);
        self::assertSame('ATANGANA OWONA Francois Mavis', $draft['user_profile']['full_name'] ?? null);
        self::assertSame('Licence Informatique', $draft['educations'][0]['degree'] ?? null);
        self::assertSame('Université de Douala', $draft['educations'][0]['institution_name'] ?? null);
        self::assertCount(3, $draft['interests'] ?? []);
    }

    public function test_fixes_duplicate_ai_name_fields_for_cameroonian_cv(): void
    {
        $enricher = $this->app->make(DocumentExtractionDraftEnricher::class);

        $draft = $enricher->enrich([
            'user_profile' => [
                'first_name' => 'Hilaire Tamakue Guifo',
                'last_name' => 'HILAIRE TAMAKUE GUIFO',
                'full_name' => 'Hilaire TAMAKUE GUIFO',
            ],
        ], 'CV');

        self::assertSame('Hilaire', $draft['user_profile']['first_name'] ?? null);
        self::assertSame('TAMAKUE GUIFO', $draft['user_profile']['last_name'] ?? null);
    }

    public function test_resolves_bio_from_alternate_fields(): void
    {
        $enricher = $this->app->make(DocumentExtractionDraftEnricher::class);

        $draft = $enricher->enrich([
            'summary' => 'Ingénieur logiciel passionné avec 5 ans d\'expérience en développement web et mobile.',
            'user_profile' => [],
        ], 'CV');

        self::assertStringContainsString('Ingénieur logiciel passionné', (string) ($draft['user_profile']['bio'] ?? ''));
    }

    public function test_deduplicates_languages_and_skills_in_draft(): void
    {
        $this->seed([LanguageSeeder::class, LanguageLevelSeeder::class]);

        $enricher = $this->app->make(DocumentExtractionDraftEnricher::class);

        $draft = $enricher->enrich([
            'languages' => [
                ['language_name' => 'Français', 'proficiency_level' => 'Intermédiaire'],
                ['language_name' => 'Français courant'],
                ['language_name' => 'Anglais', 'proficiency_level' => 'Débutant'],
            ],
            'skills' => [
                ['name' => 'Communication'],
                ['name' => 'communication'],
            ],
            'educations' => [
                [
                    'degree' => 'Licence',
                    'institution_name' => 'Université',
                    'start_date' => '2010-01-01',
                ],
                [
                    'degree' => 'Licence',
                    'institution_name' => 'Université',
                    'start_date' => '2010-01-01',
                ],
            ],
        ], 'CV');

        self::assertCount(2, $draft['languages'] ?? []);
        $frenchCount = count(array_filter(
            $draft['languages'] ?? [],
            static fn (array $row): bool => ($row['resolved_language_id'] ?? null) === Language::query()->where('code', 'fr')->value('id'),
        ));
        self::assertSame(1, $frenchCount);
        self::assertCount(1, $draft['skills'] ?? []);
        self::assertCount(1, $draft['educations'] ?? []);
    }

    public function test_reclassifies_internship_from_experience_keywords(): void
    {
        $enricher = $this->app->make(DocumentExtractionDraftEnricher::class);

        $draft = $enricher->enrich([
            'experiences' => [
                [
                    'job_title' => 'assistant comptable',
                    'company_name' => 'GM CONSULTING Sarl',
                    'start_date' => '2019-10-01',
                    'is_current' => true,
                    'responsibilities' => 'stage au sein du cabinet',
                ],
            ],
        ], 'CV');

        self::assertCount(0, $draft['experiences'] ?? []);
        self::assertCount(1, $draft['internships'] ?? []);
        self::assertSame('assistant comptable', $draft['internships'][0]['title'] ?? null);
    }

    public function test_normalizes_dot_language_levels_and_marital_status(): void
    {
        $this->seed([LanguageSeeder::class, LanguageLevelSeeder::class]);

        $enricher = $this->app->make(DocumentExtractionDraftEnricher::class);

        $draft = $enricher->enrich([
            'user_profile' => [
                'marital_status' => 'Célibataire',
            ],
            'languages' => [
                ['language_name' => 'Français', 'proficiency_level' => '●●●'],
                ['language_name' => 'Anglais', 'proficiency_level' => '●●●'],
            ],
            'internships' => [
                ['start_date' => '2019-10-01'],
                [
                    'title' => 'Brand Ambassador',
                    'organization' => 'MEKIS SARL',
                    'start_date' => '2015-01-01',
                ],
            ],
        ], 'CV');

        self::assertSame('SINGLE', $draft['user_profile']['marital_status'] ?? null);
        self::assertStringContainsString('intermédiaire', strtolower((string) ($draft['languages'][0]['proficiency_level'] ?? '')));
        self::assertCount(1, $draft['internships'] ?? []);
    }
}
