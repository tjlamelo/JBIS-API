<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Ai\Support;

/**
 * Rassemble les centres d'intérêt depuis les clés alternatives que l'IA peut renvoyer.
 */
final class InterestsDraftCollector
{
    /** @var list<string> */
    private const ROOT_KEYS = [
        'interests',
        'hobbies',
        'loisirs',
        'centres_interet',
        'centres_d_interet',
        'centre_interet',
        'centres_dinteret',
        'passions',
        'activites',
        'activities',
        'leisure',
        'leisure_activities',
    ];

    /**
     * @param  array<string, mixed>  $draft
     * @return list<array{name: string}>
     */
    public static function collect(array $draft): array
    {
        $items = [];
        $seen = [];

        foreach (self::ROOT_KEYS as $key) {
            if (! array_key_exists($key, $draft)) {
                continue;
            }

            foreach (self::expandRaw($draft[$key]) as $name) {
                $normalized = mb_strtolower(trim($name));
                if ($normalized === '' || isset($seen[$normalized])) {
                    continue;
                }

                $seen[$normalized] = true;
                $items[] = ['name' => trim($name)];
            }
        }

        return $items;
    }

    /**
     * @return list<string>
     */
    private static function expandRaw(mixed $raw): array
    {
        if (is_string($raw)) {
            return self::splitString($raw);
        }

        if (! is_array($raw)) {
            return [];
        }

        $names = [];

        foreach ($raw as $key => $item) {
            if (is_string($item)) {
                $names = [...$names, ...self::splitString($item)];

                continue;
            }

            if (! is_array($item)) {
                continue;
            }

            $name = self::extractName($item);
            if ($name !== '') {
                $names[] = $name;
            }
        }

        return $names;
    }

    /**
     * @param  array<string, mixed>  $item
     */
    private static function extractName(array $item): string
    {
        foreach (['name', 'title', 'label', 'interest', 'hobby', 'value'] as $field) {
            $value = $item[$field] ?? null;
            if (is_string($value) && trim($value) !== '') {
                return trim($value);
            }
        }

        return '';
    }

    /**
     * @return list<string>
     */
    private static function splitString(string $value): array
    {
        $value = trim($value);
        if ($value === '') {
            return [];
        }

        return array_values(array_filter(array_map(
            static fn (string $part): string => trim($part),
            preg_split('/[,;|•\n]+/u', $value) ?: [],
        ), static fn (string $part): bool => $part !== ''));
    }
}
