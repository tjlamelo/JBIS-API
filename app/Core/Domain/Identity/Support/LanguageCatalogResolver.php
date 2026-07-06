<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Support;

use App\Core\Domain\Location\Models\Language as CatalogLanguage;
use App\Core\Domain\Location\Models\LanguageLevel;
use Illuminate\Database\Eloquent\Builder;

final class LanguageCatalogResolver
{
    public function resolveId(?string $languageNameOrCode): ?int
    {
        $input = trim((string) $languageNameOrCode);
        if ($input === '') {
            return null;
        }

        $code = strtolower($input);
        if (strlen($code) === 2 && ctype_alpha($code)) {
            $byCode = CatalogLanguage::query()->where('code', $code)->value('id');
            if ($byCode !== null) {
                return (int) $byCode;
            }
        }

        $aliases = [
            'anglais' => 'en',
            'english' => 'en',
            'français' => 'fr',
            'francais' => 'fr',
            'french' => 'fr',
            'allemand' => 'de',
            'german' => 'de',
            'espagnol' => 'es',
            'spanish' => 'es',
            'arabe' => 'ar',
            'arabic' => 'ar',
            'portugais' => 'pt',
            'portuguese' => 'pt',
            'chinois' => 'zh',
            'mandarin' => 'zh',
            'russe' => 'ru',
            'russian' => 'ru',
        ];

        $normalized = mb_strtolower(trim($input));
        $normalized = preg_replace('/[^a-zàâäéèêëïîôùûüç\s]/u', '', $normalized) ?? $normalized;
        foreach ($aliases as $alias => $iso) {
            if (str_contains($normalized, $alias)) {
                $id = CatalogLanguage::query()->where('code', $iso)->value('id');
                if ($id !== null) {
                    return (int) $id;
                }
            }
        }

        $id = CatalogLanguage::query()
            ->where(function (Builder $query) use ($input): void {
                $query->where('name->fr', 'like', '%'.$input.'%')
                    ->orWhere('name->en', 'like', '%'.$input.'%');
            })
            ->value('id');

        return $id !== null ? (int) $id : null;
    }

    public function resolveLevelId(?string $proficiencyLabel): ?int
    {
        $label = app(LanguageProficiencyNormalizer::class)->normalize($proficiencyLabel);
        $label = trim($label);
        if ($label === '') {
            return LanguageLevel::query()->orderBy('sort_order')->value('id');
        }

        $normalized = strtolower($label);

        $code = match (true) {
            preg_match('/\b(a1|a2|débutant|debutant|notion|elementary|basic)\b/u', $normalized) === 1 => 'elementary_proficiency',
            preg_match('/\b(b1|intermédiaire|intermediaire|limited)\b/u', $normalized) === 1 => 'limited_working_proficiency',
            preg_match('/\b(b2|professionnel|professional)\b/u', $normalized) === 1 => 'professional_working_proficiency',
            preg_match('/\b(c1|c2|courant|fluent|avancé|avance|full)\b/u', $normalized) === 1 => 'full_professional_proficiency',
            preg_match('/\b(natif|native|maternel|bilingue|bilingual|langue maternelle)\b/u', $normalized) === 1 => 'native_or_bilingual_proficiency',
            default => 'professional_working_proficiency',
        };

        $id = LanguageLevel::query()->where('code', $code)->value('id');

        return $id !== null ? (int) $id : LanguageLevel::query()->orderBy('sort_order')->value('id');
    }
}
