<?php
declare(strict_types=1);

namespace App\Core\Domain\Catalog\Actions;

use App\Core\Domain\Catalog\DTOs\OfferDto;
use App\Core\Domain\Catalog\Models\Offer;
use App\Core\Domain\Location\Models\LanguageLevel;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UpdateOfferAction
{
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

        // Default publication time on update when publishing without planning.
        if (($attributes['status'] ?? null) === 'PUBLISHED' && array_key_exists('published_at', $attributes) && empty($attributes['published_at'])) {
            $attributes['published_at'] = now()->toDateTimeString();
        }

        /** @var Offer|null $offer */
        $offer = Offer::query()->find($offerId);

        if (! $offer) {
            throw new ModelNotFoundException("Offer {$offerId} not found.");
        }

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

        return $offer->refresh();
    }
}