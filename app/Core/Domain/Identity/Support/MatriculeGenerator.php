<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Support;

use App\Core\Domain\Identity\Enums\MatriculeService;
use App\Core\Domain\Identity\Models\UserProfile;
use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * Format: JBIS-{SERVICE}{TAG?}-{YYMM}-{XXXX}
 * Exemple: JBIS-INT-2607-A9K2 ou JBIS-NATYDE-2607-B1C3 (tag YDE)
 */
final class MatriculeGenerator
{
    private const PREFIX = 'JBIS';

    private const MAX_ATTEMPTS = 40;

    public function generate(string $serviceKey, ?string $customTag = null): string
    {
        $service = MatriculeService::tryFrom($serviceKey);
        if ($service === null) {
            throw new InvalidArgumentException(__('Service de matricule invalide.'));
        }

        $tag = $this->normalizeTag($customTag);
        $servicePart = $tag !== null ? $service->code().$tag : $service->code();
        $datePart = now()->format('ym');

        for ($attempt = 0; $attempt < self::MAX_ATTEMPTS; $attempt++) {
            $randomPart = strtoupper(Str::random(4));
            $matricule = sprintf('%s-%s-%s-%s', self::PREFIX, $servicePart, $datePart, $randomPart);

            if (! UserProfile::query()->where('matricule', $matricule)->exists()) {
                return $matricule;
            }
        }

        throw new InvalidArgumentException(__('Impossible de générer un matricule unique. Réessayez.'));
    }

    private function normalizeTag(?string $customTag): ?string
    {
        if ($customTag === null) {
            return null;
        }

        $tag = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $customTag) ?? '');
        if ($tag === '') {
            return null;
        }

        return substr($tag, 0, 6);
    }
}
