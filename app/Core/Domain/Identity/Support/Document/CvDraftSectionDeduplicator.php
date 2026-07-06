<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Support\Document;

use App\Core\Domain\Location\Models\LanguageLevel;

final class CvDraftSectionDeduplicator
{
    /**
     * @param  array<string, mixed>  $draft
     * @return array<string, mixed>
     */
    public function deduplicate(array $draft): array
    {
        $draft['educations'] = $this->dedupeRows($draft['educations'] ?? [], CvExtractionSectionFingerprint::education(...));
        $draft['experiences'] = $this->dedupeRows($draft['experiences'] ?? [], CvExtractionSectionFingerprint::experience(...));
        $draft['certifications'] = $this->dedupeRows($draft['certifications'] ?? [], CvExtractionSectionFingerprint::certification(...));
        $draft['internships'] = $this->dedupeRows($draft['internships'] ?? [], CvExtractionSectionFingerprint::internship(...));
        $draft['skills'] = $this->dedupeSkills($draft['skills'] ?? []);
        $draft['languages'] = $this->dedupeLanguages($draft['languages'] ?? []);

        return $draft;
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     * @param  callable(array<string, mixed>): string  $fingerprint
     * @return list<array<string, mixed>>
     */
    private function dedupeRows(array $rows, callable $fingerprint): array
    {
        $seen = [];
        $result = [];

        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }

            $key = $fingerprint($row);
            if ($key === '' || isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;
            $result[] = $row;
        }

        return $result;
    }

    /**
     * @param  list<array<string, mixed>>  $skills
     * @return list<array<string, mixed>>
     */
    private function dedupeSkills(array $skills): array
    {
        $seen = [];
        $result = [];

        foreach ($skills as $row) {
            if (! is_array($row)) {
                continue;
            }

            $key = CvExtractionSectionFingerprint::skill($row);
            if ($key === '') {
                continue;
            }

            if (isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;
            $result[] = $row;
        }

        return $result;
    }

    /**
     * @param  list<array<string, mixed>>  $languages
     * @return list<array<string, mixed>>
     */
    private function dedupeLanguages(array $languages): array
    {
        $levelRanks = LanguageLevel::query()->pluck('sort_order', 'id');
        $byLanguageId = [];

        foreach ($languages as $row) {
            if (! is_array($row)) {
                continue;
            }

            $languageId = isset($row['resolved_language_id']) ? (int) $row['resolved_language_id'] : 0;
            if ($languageId <= 0) {
                continue;
            }

            $levelId = isset($row['resolved_language_level_id']) ? (int) $row['resolved_language_level_id'] : 0;
            $rank = (int) ($levelRanks[$levelId] ?? 0);

            $existing = $byLanguageId[$languageId] ?? null;
            if ($existing === null) {
                $byLanguageId[$languageId] = ['row' => $row, 'rank' => $rank];

                continue;
            }

            if ($rank >= $existing['rank']) {
                $byLanguageId[$languageId] = ['row' => $row, 'rank' => $rank];
            }
        }

        return array_values(array_map(static fn (array $entry): array => $entry['row'], $byLanguageId));
    }
}
