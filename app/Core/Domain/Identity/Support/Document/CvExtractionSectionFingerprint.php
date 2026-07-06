<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Support\Document;

final class CvExtractionSectionFingerprint
{
    /**
     * @param  array<string, mixed>  $row
     */
    public static function education(array $row): string
    {
        return self::hash([
            self::normalize((string) ($row['degree'] ?? '')),
            self::normalize((string) ($row['institution_name'] ?? '')),
            self::normalizeDate($row['start_date'] ?? null),
        ]);
    }

    /**
     * @param  array<string, mixed>  $row
     */
    public static function experience(array $row): string
    {
        return self::hash([
            self::normalize((string) ($row['job_title'] ?? '')),
            self::normalize((string) ($row['company_name'] ?? '')),
            self::normalizeDate($row['start_date'] ?? null),
        ]);
    }

    /**
     * @param  array<string, mixed>  $row
     */
    public static function certification(array $row): string
    {
        return self::hash([
            self::normalize((string) ($row['name'] ?? '')),
            self::normalize((string) ($row['issuing_organization'] ?? '')),
        ]);
    }

    /**
     * @param  array<string, mixed>  $row
     */
    public static function internship(array $row): string
    {
        return self::hash([
            self::normalize((string) ($row['title'] ?? '')),
            self::normalize((string) ($row['organization'] ?? '')),
            self::normalizeDate($row['start_date'] ?? null),
        ]);
    }

    /**
     * @param  array<string, mixed>  $row
     */
    public static function skill(array $row): string
    {
        return self::normalize((string) ($row['name'] ?? ''));
    }

    /**
     * @param  array<string, mixed>  $row
     */
    public static function language(int $languageId): string
    {
        return 'lang:'.$languageId;
    }

    /**
     * @param  list<string>  $parts
     */
    private static function hash(array $parts): string
    {
        return implode('|', array_filter($parts, static fn (string $part): bool => $part !== ''));
    }

    private static function normalize(string $value): string
    {
        $value = mb_strtolower(trim($value));
        $value = preg_replace('/\s+/u', ' ', $value) ?? $value;

        return trim($value);
    }

    private static function normalizeDate(mixed $value): string
    {
        if (! is_string($value) || trim($value) === '') {
            return '';
        }

        return substr(trim($value), 0, 10);
    }
}
