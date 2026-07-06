<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Ai\Support;

use App\Core\Domain\Shared\Ai\Exceptions\LanguageModelInvalidJsonException;

/**
 * Décode la sortie texte du modèle en JSON (supporte parfois des blocs markdown ```json).
 */
final class LanguageModelJsonDecoder
{
    private const DECODE_FLAGS = JSON_THROW_ON_ERROR | JSON_INVALID_UTF8_SUBSTITUTE;

    /**
     * @return array<string, mixed>
     */
    public static function decodeObject(string $text): array
    {
        $extracted = self::extractJsonText(trim($text));

        $candidates = [
            self::sanitizeJsonControlCharacters($extracted),
            self::sanitizeJsonControlCharacters($extracted, aggressive: true),
            self::stripAsciiControlCharacters($extracted),
            self::sanitizeJsonControlCharacters(LanguageModelTruncatedJsonRepairer::repair($extracted)),
        ];

        $lastException = null;

        foreach (array_unique($candidates) as $candidate) {
            if ($candidate === '') {
                continue;
            }

            try {
                $decoded = json_decode($candidate, true, 512, self::DECODE_FLAGS);

                if (! is_array($decoded)) {
                    throw new LanguageModelInvalidJsonException('Le JSON décodé n\'est pas un objet.');
                }

                return $decoded;
            } catch (\JsonException $exception) {
                $lastException = $exception;
            }
        }

        throw new LanguageModelInvalidJsonException(
            'Réponse JSON invalide du modèle : '.($lastException?->getMessage() ?? 'payload vide'),
            previous: $lastException
        );
    }

    private static function extractJsonText(string $text): string
    {
        if (preg_match('/```(?:json)?\s*(\{.*\})\s*```/s', $text, $matches) === 1) {
            return trim($matches[1]);
        }

        $start = strpos($text, '{');
        $end = strrpos($text, '}');

        if ($start !== false && $end !== false && $end > $start) {
            return trim(substr($text, $start, $end - $start + 1));
        }

        return $text;
    }

    /**
     * Échappe ou supprime les caractères de contrôle ASCII invalides en JSON.
     */
    private static function sanitizeJsonControlCharacters(string $json, bool $aggressive = false): string
    {
        $json = preg_replace('/^\xEF\xBB\xBF/', '', $json) ?? $json;

        $result = '';
        $inString = false;
        $escape = false;
        $length = strlen($json);

        for ($i = 0; $i < $length; $i++) {
            $char = $json[$i];
            $ord = ord($char);

            if ($escape) {
                $result .= $char;
                $escape = false;

                continue;
            }

            if ($char === '\\' && $inString) {
                $result .= $char;
                $escape = true;

                continue;
            }

            if ($char === '"') {
                $inString = ! $inString;
                $result .= $char;

                continue;
            }

            if ($ord < 32) {
                if ($inString) {
                    $result .= match ($char) {
                        "\n" => '\\n',
                        "\r" => '\\r',
                        "\t" => '\\t',
                        default => '',
                    };

                    continue;
                }

                if (in_array($char, ["\n", "\r", "\t"], true)) {
                    $result .= $char;
                }

                continue;
            }

            $result .= $char;
        }

        return $result;
    }

    /**
     * Dernier recours : retire tout caractère de contrôle ASCII hors espaces JSON valides.
     */
    private static function stripAsciiControlCharacters(string $json): string
    {
        $json = preg_replace('/^\xEF\xBB\xBF/', '', $json) ?? $json;

        return preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $json) ?? $json;
    }
}
