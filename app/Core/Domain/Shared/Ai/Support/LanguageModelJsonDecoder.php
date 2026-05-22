<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Ai\Support;

use App\Core\Domain\Shared\Ai\Exceptions\LanguageModelInvalidJsonException;

/**
 * Décode la sortie texte du modèle en JSON (supporte parfois des blocs markdown ```json).
 */
final class LanguageModelJsonDecoder
{
    /**
     * @return array<string, mixed>
     */
    public static function decodeObject(string $text): array
    {
        $trimmed = trim($text);
        if (preg_match('/^```(?:json)?\s*(\{.*\})\s*```$/s', $trimmed, $m) === 1) {
            $trimmed = $m[1];
        }

        try {
            $decoded = json_decode($trimmed, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new LanguageModelInvalidJsonException(
                'Réponse JSON invalide du modèle : '.$e->getMessage(),
                previous: $e
            );
        }

        if (! is_array($decoded)) {
            throw new LanguageModelInvalidJsonException('Le JSON décodé n\'est pas un objet.');
        }

        return $decoded;
    }
}
