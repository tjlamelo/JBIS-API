<?php

declare(strict_types=1);

namespace Tests\Unit\Ai\Intel;

use App\Core\Domain\Identity\Support\CountryNameResolver;
use App\Core\Domain\Identity\Support\LanguageCatalogResolver;
use App\Core\Domain\Identity\Support\SkillCatalogResolver;
use App\Core\Domain\Shared\Ai\Intel\OfferExtractionDraftEnricher;
use Tests\TestCase;

class OfferExtractionDraftEnricherTest extends TestCase
{
    public function test_enriches_localized_fields_and_contract_hint(): void
    {
        $enricher = new OfferExtractionDraftEnricher(
            new CountryNameResolver(),
            new LanguageCatalogResolver(),
            new SkillCatalogResolver(),
        );

        $draft = [
            'description' => ['fr' => '', 'en' => 'Security guard role in UAE.'],
            'responsibilities' => ['en' => '12 hours per day, 30 days per month'],
            'requirements' => ['en' => 'Basic English. Minimum height 5.6 feet.'],
            'specifications' => ['en' => 'SIRA training fee deducted over 4 months.'],
            'work_mode' => 'on-site',
            'salary_min' => 1800,
            'salary_max' => 1800,
            'currency' => 'AED',
            'is_salary_public' => true,
            'country_hint' => 'UAE',
            'contract_type_hint' => '2 years',
            'work_schedule_hint' => '12 hours per day',
            'inferred_benefits' => ['Accommodation', 'Medical insurance'],
            'language_requirements' => [
                ['language_hint' => 'English', 'level_hint' => 'basic'],
            ],
            'notes' => 'Food variant salary not applied automatically.',
        ];

        $result = $enricher->enrich($draft);

        $this->assertStringContainsString('Security guard role in UAE.', $result['description']['en']);
        $this->assertStringContainsString('Security guard role in UAE.', $result['description']['fr']);
        $this->assertStringContainsString('SIRA training fee', $result['requirements']['en']);
        $this->assertMatchesRegularExpression('/^1\.\s/', $result['responsibilities']['en']);
        $this->assertMatchesRegularExpression('/^1\.\s/', $result['requirements']['en']);
        $this->assertSame(1800.0, $result['salary_min']);
        $this->assertSame('AED', $result['currency']);
        $this->assertFalse($result['is_salary_public']);
        $this->assertSame('on-site', $result['work_mode']);
        $this->assertNotEmpty($result['language_requirements']);
        $this->assertSame('', $result['notes']);
    }

    public function test_applies_defaults_when_salary_missing(): void
    {
        $enricher = new OfferExtractionDraftEnricher(
            new CountryNameResolver(),
            new LanguageCatalogResolver(),
            new SkillCatalogResolver(),
        );

        $result = $enricher->enrich([
            'description' => ['fr' => 'Poste local', 'en' => 'Local role'],
            'responsibilities' => ['fr' => 'Missions', 'en' => 'Duties'],
            'requirements' => ['fr' => 'Profil', 'en' => 'Profile'],
        ]);

        $this->assertSame(1, $result['available_positions']);
        $this->assertSame('on-site', $result['work_mode']);
        $this->assertFalse($result['is_salary_public']);
        $this->assertNull($result['salary_min']);
    }

    public function test_formats_editorial_sections_with_numbered_lists(): void
    {
        $enricher = new OfferExtractionDraftEnricher(
            new CountryNameResolver(),
            new LanguageCatalogResolver(),
            new SkillCatalogResolver(),
        );

        $result = $enricher->enrich([
            'description' => ['en' => 'Role overview. Second sentence about the team.'],
            'responsibilities' => ['en' => "- Patrol the site\n- Work 12 hours per day"],
            'requirements' => ['en' => "* Basic English\n* Minimum height 5.6 feet"],
        ], [], 'editorial');

        $this->assertStringContainsString("Role overview.", $result['description']['en']);
        $this->assertSame("1. Patrol the site\n2. Work 12 hours per day", $result['responsibilities']['en']);
        $this->assertSame("1. Basic English\n2. Minimum height 5.6 feet", $result['requirements']['en']);
    }

    public function test_hides_company_name_when_company_provided(): void
    {
        $enricher = new OfferExtractionDraftEnricher(
            new CountryNameResolver(),
            new LanguageCatalogResolver(),
            new SkillCatalogResolver(),
        );

        $result = $enricher->enrich(
            [
                'description' => ['fr' => 'Offre', 'en' => 'Offer'],
                'responsibilities' => ['fr' => '1. Mission', 'en' => '1. Duty'],
                'requirements' => ['fr' => '1. Profil', 'en' => '1. Profile'],
            ],
            ['company_id' => 42],
        );

        $this->assertFalse($result['is_company_public']);
    }

    public function test_supplements_structured_fields_from_raw_text_when_ai_omits_them(): void
    {
        $enricher = new OfferExtractionDraftEnricher(
            new CountryNameResolver(),
            new LanguageCatalogResolver(),
            new SkillCatalogResolver(),
        );

        $rawText = <<<'TEXT'
Security Guard Job Offer – UAE

Salary: 1800 AED per month
Duty: 12 hours per day, 30 days per month

Company provides:
* Accommodation
* Uniform
* Medical insurance
* Visa
* Transportation

Requirements:
* Basic English (reading, writing, and understanding)
* Minimum height: 5.6 feet

Contract: 2 years
TEXT;

        $result = $enricher->enrich(
            [
                'description' => ['fr' => 'Poste', 'en' => 'Role'],
                'responsibilities' => ['fr' => '1. Surveillance', 'en' => '1. Patrol'],
                'requirements' => ['fr' => '1. Profil', 'en' => '1. Profile'],
            ],
            ['raw_text' => $rawText],
        );

        $this->assertSame(1800.0, $result['salary_min']);
        $this->assertSame(1800.0, $result['salary_max']);
        $this->assertSame('AED', $result['currency']);
        $this->assertFalse($result['is_salary_public']);
        $this->assertNotEmpty($result['inferred_benefits'] ?? $result['benefit_ids'] ?? []);
        $this->assertTrue(
            ! empty($result['benefit_ids']) || ! empty($result['language_requirements']) || ! empty($result['unmatched']['benefits'] ?? []),
        );
    }
}
