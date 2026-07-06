<?php

declare(strict_types=1);

namespace Tests\Unit\Ai\Intel;

use App\Core\Domain\Shared\Ai\Intel\DocumentExtractionDraftEnricher;
use App\Core\Domain\Shared\Ai\Intel\DocumentExtractionProfileRegistry;
use Tests\TestCase;

final class DocumentExtractionProfileRegistryTest extends TestCase
{
    public function test_supports_pdf_and_images(): void
    {
        self::assertTrue(DocumentExtractionProfileRegistry::supportsExtractableMime('image/png'));
        self::assertTrue(DocumentExtractionProfileRegistry::supportsExtractableMime('application/pdf'));
        self::assertFalse(DocumentExtractionProfileRegistry::supportsExtractableMime('application/msword'));
    }

    public function test_new_document_types_are_extractable(): void
    {
        foreach (['WORK_CERTIFICATE', 'PROFESSIONAL_CERTIFICATION', 'TRAINING_CERTIFICATE', 'VISA'] as $code) {
            self::assertTrue(DocumentExtractionProfileRegistry::isExtractable($code), $code);
        }
    }

    public function test_enriches_work_certificate_into_experience(): void
    {
        $enricher = $this->app->make(DocumentExtractionDraftEnricher::class);

        $draft = $enricher->enrich([
            'user_profile' => ['full_name' => 'EBENE EBENE Thierry'],
            'work_certificate' => [
                'job_title' => 'Agent commercial',
                'company_name' => 'Société PREMIER GAME',
                'start_date' => '2013-01-01',
                'end_date' => '2019-12-31',
                'responsibilities' => 'Distribution des produits',
            ],
        ], 'WORK_CERTIFICATE');

        self::assertSame('Thierry', $draft['user_profile']['first_name'] ?? null);
        self::assertCount(1, $draft['experiences'] ?? []);
        self::assertSame('Agent commercial', $draft['experiences'][0]['job_title'] ?? null);
        self::assertArrayNotHasKey('work_certificate', $draft);
    }

    public function test_enriches_birth_certificate_profile_and_gender(): void
    {
        $enricher = $this->app->make(DocumentExtractionDraftEnricher::class);

        $draft = $enricher->enrich([
            'user_profile' => [
                'full_name' => 'EBENE EBENE Thierry',
                'gender' => 'Masculin',
            ],
            'birth_record' => [
                'father_name' => 'Jean EBENE',
                'mother_name' => 'Marie EBENE',
                'issue_date' => '2020-01-01',
            ],
        ], 'BIRTH_CERTIFICATE');

        self::assertSame('M', $draft['user_profile']['gender'] ?? null);
        self::assertSame('Jean EBENE', $draft['birth_record']['father_name'] ?? null);
    }
}
