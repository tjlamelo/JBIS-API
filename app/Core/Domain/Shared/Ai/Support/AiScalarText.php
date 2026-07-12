<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Ai\Support;

/**
 * Convertit proprement les valeurs IA (souvent string|list) en texte scalaire.
 * Évite les "Array to string conversion" quand le modèle renvoie des listes à puces.
 */
final class AiScalarText
{
    public static function from(mixed $value, string $listGlue = "\n"): string
    {
        if ($value === null || $value === false) {
            return '';
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        if (is_string($value)) {
            return trim($value);
        }

        if (is_array($value)) {
            $parts = [];
            foreach ($value as $item) {
                if (is_array($item)) {
                    // Objets {text|name|value|description} ou listes imbriquées.
                    $nested = self::from(
                        $item['text']
                            ?? $item['name']
                            ?? $item['value']
                            ?? $item['description']
                            ?? $item['title']
                            ?? $item,
                        $listGlue,
                    );
                    if ($nested !== '') {
                        $parts[] = $nested;
                    }

                    continue;
                }

                $scalar = self::from($item, $listGlue);
                if ($scalar !== '') {
                    $parts[] = $scalar;
                }
            }

            return trim(implode($listGlue, $parts));
        }

        if (is_object($value) && method_exists($value, '__toString')) {
            return trim((string) $value);
        }

        return '';
    }

    public static function nullable(mixed $value, string $listGlue = "\n"): ?string
    {
        $text = self::from($value, $listGlue);

        return $text === '' ? null : $text;
    }
}
