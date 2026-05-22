<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Catalog\Resources\Offer;

use App\Core\Domain\Location\Models\LanguageLevel;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OfferResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $isAdminRequest = $request->is('api/v1/catalog/admin/offers*');
        $searchTerm = $request->input('search');
        $languageLevelIds = $this->languages
            ->pluck('pivot.language_level_id')
            ->filter(fn ($id) => is_numeric($id))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();
        $languageLevelsById = LanguageLevel::query()
            ->whereIn('id', $languageLevelIds)
            ->get(['id', 'code', 'name'])
            ->keyBy('id');

        return [
            'id' => $this->id,
            'title' => $this->getTranslations('title'),
            'slug' => $this->getTranslations('slug'),
            'description' => $this->getTranslations('description'),
            'photo' => $this->photo,
            'photo_url' => is_array($this->photo) ? ($this->photo['url'] ?? null) : $this->photo,
            'photo_fallback_url' => is_array($this->photo) ? ($this->photo['fallback_url'] ?? null) : null,
            'photo_media' => $isAdminRequest ? $this->photo_media : null,
            'responsibilities' => $this->getTranslations('responsibilities'),
            'requirements' => $this->getTranslations('requirements'),
            'specific_documents' => $this->getTranslations('specific_documents'),

            'city' => [
                'id' => $this->city?->id,
                'name' => $this->city?->name,
            ],
            'city_id' => $this->city_id,
            'address' => $this->address,
            'country' => [
                'id' => $this->country?->id,
                'name' => $this->country?->name,
                'code' => $this->country?->code,
            ],
            'country_id' => $this->country_id,

            'contract_type' => [
                'id' => $this->contractType?->id,
                'name' => $this->contractType?->name,
                'color_code' => $this->contractType?->color_code,
            ],
            'offer_type' => [
                'id' => $this->offerType?->id,
                'name' => $this->offerType?->name,
                'slug' => $this->offerType?->slug,
            ],
            'work_schedule' => [
                'id' => $this->workSchedule?->id,
                'name' => $this->workSchedule?->name,
                'slug' => $this->workSchedule?->slug,
            ],
            'education_level' => [
                'id' => $this->educationLevel?->id,
                'name' => $this->educationLevel?->name,
                'slug' => $this->educationLevel?->slug,
            ],
            'contract_type_id' => $this->contract_type_id,
            'offer_type_id' => $this->offer_type_id,
            'work_schedule_id' => $this->work_schedule_id,
            'education_level_id' => $this->education_level_id,
            'offer_category_id' => $this->offer_category_id,
            'company_id' => $this->company_id,
            'program_id' => $this->program_id,

            'salary_min' => $isAdminRequest || $this->is_salary_public ? $this->salary_min : null,
            'salary_max' => $isAdminRequest || $this->is_salary_public ? $this->salary_max : null,
            'currency' => $this->currency,
            'is_salary_public' => (bool) $this->is_salary_public,

            'company' => [
                'id' => $this->company?->id,
                'name' => $isAdminRequest || $this->is_company_public ? $this->company?->name : __('Confidentiel'),
                'logo' => $isAdminRequest || $this->is_company_public ? $this->company?->logo : null,
            ],
            'is_company_public' => (bool) $this->is_company_public,
            'category' => $this->category ? [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'slug' => $this->category->slug,
                'icon' => $this->category->icon,
            ] : null,

            // 🟢 AJOUT DES RELATIONS MANQUANTES
            'benefits' => $this->benefits->map(fn ($b) => [
                'id' => $b->id,
                'name' => $b->getTranslations('name'),
                'slug' => $b->slug,
                'icon' => $b->icon,
            ]),
            'languages' => $this->languages->map(function ($l) use ($languageLevelsById) {
                $level = ($l->pivot?->language_level_id ?? null) ? $languageLevelsById->get((int) $l->pivot->language_level_id) : null;

                return [
                    'id' => $l->id,
                    'name' => $l->getTranslations('name'),
                    'language_level_id' => $l->pivot?->language_level_id ? (int) $l->pivot->language_level_id : null,
                    'required_level' => $l->pivot?->required_level,
                    'required_level_label' => $this->resolveLanguageLevelTranslations(
                        $level?->getTranslations('name') ?? null,
                        $l->pivot?->required_level
                    ),
                    'language_level' => $level ? [
                        'id' => $level->id,
                        'code' => $level->code,
                        'name' => $level->getTranslations('name'),
                    ] : null,
                ];
            }),
            'skills' => $this->skills->map(fn ($s) => [
                'id' => $s->id,
                'name' => $s->getTranslations('name'),
                'slug' => $s->slug,
                'level' => $s->pivot?->level,
                'category' => $s->category?->name ?? null,
            ]),

            'required_documents' => $this->requiredDocuments->map(fn ($d) => [
                'id' => $d->id,
                'name' => $d->name,
                'is_mandatory' => (bool) $d->pivot->is_mandatory,
                'sort_order' => (int) ($d->pivot->sort_order ?? 0),
                'type' => $d->type,
                'slug' => $d->slug,
                'description' => $d->description,
            ]),

            'program' => $this->program ? [
                'id' => $this->program->id,
                'name' => $this->program->getTranslations('name'),
                'slug' => $this->program->slug,
            ] : null,

            'language' => $this->language,
            'work_mode' => $this->work_mode,
            'available_positions' => $this->available_positions,
            'user_id' => $this->user_id,
            'status' => $this->status?->value ?? (string) $this->status,
            'views_count' => (int) ($this->views_count ?? 0),
            'published_at' => $this->published_at?->toDateTimeString(),
            'published_at_human' => $this->published_at?->diffForHumans(),
            'expiration_date' => $this->expiration_date?->toDateTimeString(),
            'meta' => [
                'is_featured' => (bool) ($this->meta['is_featured'] ?? false),
                'is_urgent' => (bool) ($this->meta['is_urgent'] ?? false),
            ],
            'is_featured' => (bool) ($this->meta['is_featured'] ?? false),
            'is_urgent' => (bool) ($this->meta['is_urgent'] ?? false),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
            'deleted_at' => $this->deleted_at?->toDateTimeString(),
        ];
    }

    /**
     * Return translations map in getTranslations-like shape.
     *
     * @return array{fr:string,en:string}|null
     */
    private function resolveLanguageLevelTranslations(?array $translations, ?string $fallback): ?array
    {
        if (is_array($translations) && ($translations['fr'] ?? $translations['en'] ?? null)) {
            return [
                'fr' => (string) ($translations['fr'] ?? $translations['en']),
                'en' => (string) ($translations['en'] ?? $translations['fr']),
            ];
        }

        if (! is_string($fallback) || $fallback === '') {
            return null;
        }

        $decoded = json_decode($fallback, true);
        if (is_array($decoded)) {
            $fr = isset($decoded['fr']) && is_string($decoded['fr']) ? $decoded['fr'] : null;
            $en = isset($decoded['en']) && is_string($decoded['en']) ? $decoded['en'] : null;
            if ($fr !== null || $en !== null) {
                return [
                    'fr' => $fr ?? $en ?? $fallback,
                    'en' => $en ?? $fr ?? $fallback,
                ];
            }
        }

        return [
            'fr' => $fallback,
            'en' => $fallback,
        ];
    }
}
