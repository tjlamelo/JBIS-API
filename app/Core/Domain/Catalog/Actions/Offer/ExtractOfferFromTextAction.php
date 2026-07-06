<?php

declare(strict_types=1);

namespace App\Core\Domain\Catalog\Actions\Offer;

use App\Core\Domain\Catalog\Models\Company;
use App\Core\Domain\Catalog\Models\Trade;
use App\Core\Domain\Location\Models\Country;
use App\Core\Domain\Shared\Ai\Intel\JobOfferFromPlainTextService;
use App\Core\Domain\Shared\Ai\Intel\OfferExtractionDraftEnricher;

final class ExtractOfferFromTextAction
{
    public function __construct(
        private readonly JobOfferFromPlainTextService $plainTextService,
        private readonly OfferExtractionDraftEnricher $draftEnricher,
    ) {}

    /**
     * @param  array<string, mixed>  $formContext
     * @return array<string, mixed>
     */
    public function execute(string $rawText, array $formContext = [], string $scope = 'full'): array
    {
        $context = $this->buildAiContext($formContext);
        $context['raw_text'] = $rawText;
        $rawDraft = $this->plainTextService->structureDraftWithContext($rawText, $context, $scope);
        $enriched = $this->draftEnricher->enrich($rawDraft, $context, $scope);

        if ($scope === 'editorial') {
            return $this->filterEditorialDraft($enriched);
        }

        return $enriched;
    }

    /**
     * @param  array<string, mixed>  $draft
     * @return array<string, mixed>
     */
    private function filterEditorialDraft(array $draft): array
    {
        return [
            'description' => $draft['description'] ?? ['fr' => '', 'en' => ''],
            'responsibilities' => $draft['responsibilities'] ?? ['fr' => '', 'en' => ''],
            'requirements' => $draft['requirements'] ?? ['fr' => '', 'en' => ''],
            'specific_documents' => $draft['specific_documents'] ?? ['fr' => '', 'en' => ''],
            'notes' => $draft['notes'] ?? '',
        ];
    }

    /**
     * @param  array<string, mixed>  $formContext
     * @return array<string, mixed>
     */
    private function buildAiContext(array $formContext): array
    {
        $context = [];

        if (! empty($formContext['trade_id'])) {
            $trade = Trade::query()
                ->with('category:id,name')
                ->find((int) $formContext['trade_id']);

            if ($trade !== null) {
                $context['trade_id'] = (int) $trade->id;
                $context['trade_name'] = [
                    'fr' => (string) $trade->getTranslation('name', 'fr', false),
                    'en' => (string) $trade->getTranslation('name', 'en', false),
                ];
                if ($trade->category !== null) {
                    $context['sector_name'] = [
                        'fr' => (string) $trade->category->getTranslation('name', 'fr', false),
                        'en' => (string) $trade->category->getTranslation('name', 'en', false),
                    ];
                }
            }
        }

        if (! empty($formContext['country_id'])) {
            $country = Country::query()->find((int) $formContext['country_id']);
            if ($country !== null) {
                $context['country_id'] = (int) $country->id;
                $context['country_name'] = [
                    'fr' => (string) $country->getTranslation('name', 'fr', false),
                    'en' => (string) $country->getTranslation('name', 'en', false),
                ];
            }
        }

        foreach (['company_id', 'city_id', 'currency', 'work_mode'] as $key) {
            if (! empty($formContext[$key])) {
                $context[$key] = $formContext[$key];
            }
        }

        if (! empty($formContext['company_id'])) {
            $company = Company::query()->find((int) $formContext['company_id']);
            if ($company !== null) {
                $context['company_id'] = (int) $company->id;
                $context['company_name'] = (string) $company->name;
            }
        }

        return $context;
    }
}
