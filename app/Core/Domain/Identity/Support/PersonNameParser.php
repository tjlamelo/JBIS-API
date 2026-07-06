<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Support;

/**
 * Parse les noms complets, y compris les conventions africaines (Cameroun, Afrique centrale, etc.) :
 * - NOM NOM Prénom Prénom (ex. ATANGANA OWONA Francois Mavis)
 * - Prénom NOM NOM (ex. Hilaire TAMAKUE GUIFO)
 * - NOM Prénom (ex. DUPONT Jean)
 */
final class PersonNameParser
{
    /**
     * @return array{first_name: string, last_name: string, full_name: string}
     */
    public function parse(?string $firstName, ?string $lastName, ?string $fullName = null): array
    {
        $first = $this->collapseSpaces((string) $firstName);
        $last = $this->collapseSpaces((string) $lastName);
        $full = $this->collapseSpaces((string) $fullName);

        if ($this->fieldsRepresentSameIdentity($first, $last)) {
            $full = $this->pickBestFullNameWhenDuplicated($first, $last, $full);
            $first = '';
            $last = '';
        }

        if ($full === '' && $first !== '' && $last !== '') {
            if ($this->isMostlyUppercase($first) && ! $this->isMostlyUppercase($last)) {
                $full = $first.' '.$last;
            } elseif (! $this->isMostlyUppercase($first) && $this->isMostlyUppercase($last)) {
                $full = $last.' '.$first;
            } else {
                $full = $last.' '.$first;
            }
        }

        if ($full === '' && $first !== '') {
            $full = $first;
        }
        if ($full === '' && $last !== '') {
            $full = $last;
        }

        if ($full === '') {
            return ['first_name' => '', 'last_name' => '', 'full_name' => ''];
        }

        if ($first !== '' && $last !== '' && $this->namesLookComplete($first, $last, $full)) {
            return [
                'first_name' => $this->formatGivenNames(explode(' ', $first)),
                'last_name' => $this->preserveFamilyNames(explode(' ', $last)),
                'full_name' => $full,
            ];
        }

        $split = $this->splitFullName($full);

        return [
            'first_name' => $split['first_name'],
            'last_name' => $split['last_name'],
            'full_name' => $full,
        ];
    }

    private function fieldsRepresentSameIdentity(string $first, string $last): bool
    {
        if ($first === '' || $last === '') {
            return false;
        }

        $firstTokens = $this->normalizedTokens($first);
        $lastTokens = $this->normalizedTokens($last);

        if ($firstTokens === [] || $lastTokens === []) {
            return false;
        }

        return $firstTokens === $lastTokens;
    }

    private function pickBestFullNameWhenDuplicated(string $first, string $last, string $full): string
    {
        if ($full !== '' && ! $this->fieldsRepresentSameIdentity($full, $first)) {
            return $full;
        }

        $parts = explode(' ', $first !== '' ? $first : $last);
        if (count($parts) >= 2 && ! $this->isMostlyUppercase($parts[0])) {
            $given = $this->formatGivenNames([$parts[0]]);
            $family = $this->preserveFamilyNames(array_slice($parts, 1));

            return trim($given.' '.$family);
        }

        return $first !== '' ? $first : $last;
    }

    private function namesLookComplete(string $first, string $last, string $full): bool
    {
        if ($this->fieldsRepresentSameIdentity($first, $last)) {
            return false;
        }

        $tokens = explode(' ', $full);
        $firstTokens = explode(' ', $first);
        $lastTokens = explode(' ', $last);

        if ($this->isMostlyUppercase($first) && ! $this->isMostlyUppercase($last)) {
            return false;
        }

        if (count($tokens) > 2 && (count($firstTokens) + count($lastTokens)) >= count($tokens)) {
            $firstLooksLikeGiven = ! $this->isMostlyUppercase($first);
            $lastLooksLikeFamily = $this->isMostlyUppercase($last);
            $noOverlap = $this->normalizedTokens($first) !== $this->normalizedTokens($last);

            return $firstLooksLikeGiven && $lastLooksLikeFamily && $noOverlap
                && count($firstTokens) < count($tokens)
                && count($lastTokens) < count($tokens);
        }

        return count($firstTokens) >= 1 && count($lastTokens) >= 1
            && ! ($this->isMostlyUppercase($first) && ! $this->isMostlyUppercase($last));
    }

    /**
     * @return array{first_name: string, last_name: string}
     */
    private function splitFullName(string $full): array
    {
        $parts = explode(' ', $full);

        if (count($parts) === 1) {
            return [
                'first_name' => $this->formatGivenNames($parts),
                'last_name' => '',
            ];
        }

        $leadingUpper = 0;
        foreach ($parts as $part) {
            if ($this->isMostlyUppercase($part)) {
                $leadingUpper++;
            } else {
                break;
            }
        }

        if ($leadingUpper >= 1 && $leadingUpper < count($parts)) {
            return [
                'last_name' => $this->preserveFamilyNames(array_slice($parts, 0, $leadingUpper)),
                'first_name' => $this->formatGivenNames(array_slice($parts, $leadingUpper)),
            ];
        }

        $trailingUpper = 0;
        for ($i = count($parts) - 1; $i >= 0; $i--) {
            if ($this->isMostlyUppercase($parts[$i])) {
                $trailingUpper++;
            } else {
                break;
            }
        }

        if ($trailingUpper >= 1 && $trailingUpper < count($parts)) {
            return [
                'first_name' => $this->formatGivenNames(array_slice($parts, 0, -$trailingUpper)),
                'last_name' => $this->preserveFamilyNames(array_slice($parts, -$trailingUpper)),
            ];
        }

        if ($leadingUpper === count($parts)) {
            if (count($parts) >= 4 && count($parts) % 2 === 0) {
                $half = (int) (count($parts) / 2);

                return [
                    'last_name' => $this->preserveFamilyNames(array_slice($parts, 0, $half)),
                    'first_name' => $this->formatGivenNames(array_slice($parts, $half)),
                ];
            }

            if (count($parts) === 3) {
                return [
                    'last_name' => $this->preserveFamilyNames(array_slice($parts, 0, 2)),
                    'first_name' => $this->formatGivenNames(array_slice($parts, 2)),
                ];
            }

            return [
                'last_name' => $this->preserveFamilyNames(array_slice($parts, 0, -1)),
                'first_name' => $this->formatGivenNames([$parts[count($parts) - 1]]),
            ];
        }

        if ($this->allTitleCaseParts($parts)) {
            if (count($parts) === 3) {
                return [
                    'first_name' => $this->formatGivenNames([$parts[0]]),
                    'last_name' => $this->preserveFamilyNames(array_slice($parts, 1)),
                ];
            }

            if (count($parts) === 4) {
                return [
                    'last_name' => $this->preserveFamilyNames(array_slice($parts, 0, 2)),
                    'first_name' => $this->formatGivenNames(array_slice($parts, 2)),
                ];
            }
        }

        return [
            'first_name' => $this->formatGivenNames(array_slice($parts, 0, -1)),
            'last_name' => $this->preserveFamilyNames([$parts[count($parts) - 1]]),
        ];
    }

    /**
     * @param  list<string>  $parts
     */
    private function allTitleCaseParts(array $parts): bool
    {
        foreach ($parts as $part) {
            if ($part === '' || $this->isMostlyUppercase($part)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return list<string>
     */
    private function normalizedTokens(string $value): array
    {
        $tokens = array_values(array_filter(explode(' ', $this->collapseSpaces($value))));

        return array_map(
            static fn (string $token): string => mb_strtoupper($token, 'UTF-8'),
            $tokens,
        );
    }

    /**
     * @param  list<string>  $parts
     */
    private function preserveFamilyNames(array $parts): string
    {
        $normalized = array_values(array_filter(array_map(
            static fn (string $part): string => mb_strtoupper(trim($part), 'UTF-8'),
            $parts,
        )));

        return implode(' ', $normalized);
    }

    /**
     * @param  list<string>  $parts
     */
    private function formatGivenNames(array $parts): string
    {
        $formatted = array_values(array_filter(array_map(function (string $part): string {
            $part = trim($part);
            if ($part === '') {
                return '';
            }

            if ($this->isMostlyUppercase($part)) {
                return $this->titleCase($part);
            }

            return $part;
        }, $parts)));

        return implode(' ', $formatted);
    }

    private function isMostlyUppercase(string $value): bool
    {
        $letters = preg_replace('/[^a-zA-ZÀ-ÿ]/', '', $value) ?? '';

        return $letters !== '' && mb_strtoupper($letters, 'UTF-8') === $letters;
    }

    private function titleCase(string $value): string
    {
        return mb_convert_case(mb_strtolower($value, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
    }

    private function collapseSpaces(string $value): string
    {
        return trim(preg_replace('/\s+/u', ' ', $value) ?? $value);
    }
}
