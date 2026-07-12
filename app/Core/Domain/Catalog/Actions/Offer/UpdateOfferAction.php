<?php

declare(strict_types=1);

namespace App\Core\Domain\Catalog\Actions\Offer;

use App\Core\Domain\Catalog\DTOs\Offer\OfferDto;
use App\Core\Domain\Catalog\Models\Offer;
use App\Core\Domain\Catalog\Support\OfferPublicationScheduler;
use App\Core\Domain\Location\Models\LanguageLevel;
use App\Core\Infrastructure\Cache\CatalogCacheInvalidator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UpdateOfferAction
{
    public function __construct(
        private readonly CatalogCacheInvalidator $catalogCache,
    ) {}

    public function execute(int $offerId, OfferDto $dto): Offer
    {
        $attributes = $dto->toArray();
        $benefitIds = $attributes['benefit_ids'] ?? null;
        $languageRequirements = $attributes['language_requirements'] ?? null;
        $skillRequirements = $attributes['skill_requirements'] ?? null;
        $requiredDocuments = $attributes['required_documents'] ?? null;

        unset(
            $attributes['benefit_ids'],
            $attributes['language_requirements'],
            $attributes['skill_requirements'],
            $attributes['required_documents']
        );
        unset($attributes['id']);

        /** @var Offer|null $offer */
        $offer = Offer::query()->find($offerId);

        if (! $offer) {
            throw new ModelNotFoundException("Offer {$offerId} not found.");
        }

        if (! array_key_exists('status', $attributes) && $offer->status !== null) {
            $attributes['status'] = $offer->status instanceof \BackedEnum
                ? $offer->status->value
                : (string) $offer->status;
        }

        $attributes = OfferPublicationScheduler::normalize($attributes);

        $offer->fill($attributes);
        $offer->save();

        if (is_array($benefitIds)) {
            $offer->benefits()->sync(array_values(array_map('intval', $benefitIds)));
        }

        if (is_array($languageRequirements)) {
            $offer->languages()->sync(
                collect($languageRequirements)->mapWithKeys(
                    function (array $item): array {
                        $languageId = (int) ($item['language_id'] ?? 0);
                        $languageLevelId = isset($item['language_level_id']) ? (int) $item['language_level_id'] : null;
                        $requiredLevel = isset($item['required_level']) ? (string) $item['required_level'] : null;

                        if ($languageLevelId === null && $requiredLevel) {
                            $languageLevelId = LanguageLevel::query()->where('code', $requiredLevel)->value('id');
                        }

                        return [
                            $languageId => [
                                'language_level_id' => $languageLevelId,
                                'required_level' => $requiredLevel,
                            ],
                        ];
                    }
                )->filter(fn ($_pivot, $id) => (int) $id > 0)->toArray()
            );
        }

        if (is_array($skillRequirements)) {
            $offer->skills()->sync(
                collect($skillRequirements)->mapWithKeys(
                    fn (array $item) => [
                        (int) ($item['skill_id'] ?? 0) => [
                            'level' => $item['level'] ?? null,
                        ],
                    ]
                )->filter(fn ($_pivot, $id) => (int) $id > 0)->toArray()
            );
        }

        if (is_array($requiredDocuments)) {
            $offer->requiredDocuments()->sync(
                collect($requiredDocuments)->mapWithKeys(
                    fn (array $item) => [
                        (int) ($item['required_document_id'] ?? 0) => [
                            'is_mandatory' => (bool) ($item['is_mandatory'] ?? true),
                            'sort_order' => (int) ($item['sort_order'] ?? 0),
                        ],
                    ]
                )->filter(fn ($_pivot, $id) => (int) $id > 0)->toArray()
            );
        }

        $offer = $offer->refresh();
        $this->catalogCache->invalidate();

        return $offer;
    }
}
