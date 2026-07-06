<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Support;

use App\Core\Domain\Catalog\Models\Skill;
use Illuminate\Support\Collection;

final class SkillCatalogResolver
{
    /** @var Collection<int, Skill>|null */
    private ?Collection $skills = null;

    public function resolveId(?string $skillName): ?int
    {
        $needle = $this->normalize((string) $skillName);
        if ($needle === '' || mb_strlen($needle) < 3) {
            return null;
        }

        $bestId = null;
        $bestScore = 0.0;

        foreach ($this->allSkills() as $skill) {
            foreach (['fr', 'en'] as $locale) {
                $haystack = $this->normalize((string) $skill->getTranslation('name', $locale, false));
                if ($haystack === '') {
                    continue;
                }

                if ($haystack === $needle) {
                    return (int) $skill->id;
                }

                $score = $this->scoreMatch($needle, $haystack);
                if ($score > $bestScore) {
                    $bestScore = $score;
                    $bestId = (int) $skill->id;
                }
            }
        }

        return $bestScore >= 0.55 ? $bestId : null;
    }

    /**
     * @return Collection<int, Skill>
     */
    private function allSkills(): Collection
    {
        if ($this->skills === null) {
            $this->skills = Skill::query()->get(['id', 'name', 'slug']);
        }

        return $this->skills;
    }

    private function scoreMatch(string $needle, string $haystack): float
    {
        if (str_contains($haystack, $needle) || str_contains($needle, $haystack)) {
            return 0.85;
        }

        similar_text($needle, $haystack, $percent);

        return max($percent / 100, $this->tokenOverlapScore($needle, $haystack));
    }

    private function tokenOverlapScore(string $needle, string $haystack): float
    {
        $needleTokens = $this->significantTokens($needle);
        $haystackTokens = $this->significantTokens($haystack);

        if ($needleTokens === [] || $haystackTokens === []) {
            return 0.0;
        }

        $overlap = count(array_intersect($needleTokens, $haystackTokens));

        return $overlap / max(count($needleTokens), count($haystackTokens));
    }

    /**
     * @return list<string>
     */
    private function significantTokens(string $value): array
    {
        $stopWords = ['de', 'du', 'des', 'la', 'le', 'les', 'et', 'en', 'a', 'à', 'au', 'aux', 'pour', 'par', 'sur', 'avec', 'dans', 'une', 'un', 'the', 'and', 'or', 'to', 'of'];

        $tokens = preg_split('/\s+/u', $this->normalize($value)) ?: [];

        return array_values(array_filter(
            $tokens,
            static fn (string $token): bool => mb_strlen($token) >= 4 && ! in_array($token, $stopWords, true),
        ));
    }

    private function normalize(string $value): string
    {
        $value = mb_strtolower(trim($value));
        $value = str_replace(['’', "'"], ' ', $value);
        $value = preg_replace('/[^a-zàâäéèêëïîôùûüç0-9\s]/u', ' ', $value) ?? $value;

        return trim(preg_replace('/\s+/u', ' ', $value) ?? $value);
    }
}
