<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Ai\Support;

/**
 * Tente de fermer un objet JSON tronqué (réponse IA interrompue ou champ trop long).
 */
final class LanguageModelTruncatedJsonRepairer
{
    public static function repair(string $json): string
    {
        $json = trim($json);
        if ($json === '' || $json[0] !== '{') {
            return $json;
        }

        $result = '';
        $inString = false;
        $escape = false;
        $depth = 0;
        $length = strlen($json);

        for ($i = 0; $i < $length; $i++) {
            $char = $json[$i];

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

            if (! $inString) {
                if ($char === '{') {
                    $depth++;
                } elseif ($char === '}') {
                    $depth--;
                }
            }

            $result .= $char;
        }

        if ($inString) {
            $result .= '"';
        }

        while ($depth > 0) {
            $result .= '}';
            $depth--;
        }

        return $result;
    }
}
