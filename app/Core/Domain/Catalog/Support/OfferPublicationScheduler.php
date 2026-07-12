<?php

declare(strict_types=1);

namespace App\Core\Domain\Catalog\Support;

use App\Core\Domain\Catalog\States\OfferStatus;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

/**
 * Normalise statut / published_at pour publication immédiate ou planifiée.
 */
final class OfferPublicationScheduler
{
    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    public static function normalize(array $attributes, ?CarbonInterface $now = null): array
    {
        $now = $now ?? Carbon::now();
        $status = isset($attributes['status']) ? (string) $attributes['status'] : null;
        $publishedAtRaw = $attributes['published_at'] ?? null;

        $publishedAt = null;
        if ($publishedAtRaw !== null && $publishedAtRaw !== '') {
            try {
                $publishedAt = Carbon::parse($publishedAtRaw);
            } catch (\Throwable) {
                $publishedAt = null;
                unset($attributes['published_at']);
            }
        }

        // Publication planifiée dans le futur → reste brouillon jusqu'au cron / dueForScheduledPublication.
        if ($publishedAt !== null && $publishedAt->gt($now)) {
            $attributes['status'] = OfferStatus::Draft->value;
            $attributes['published_at'] = $publishedAt->toDateTimeString();

            return $attributes;
        }

        if ($status === OfferStatus::Published->value) {
            if ($publishedAt === null) {
                $attributes['published_at'] = $now->toDateTimeString();
            } else {
                $attributes['published_at'] = $publishedAt->toDateTimeString();
            }
        }

        if ($status === OfferStatus::Draft->value && $publishedAt !== null && $publishedAt->lte($now)) {
            // Date planifiée déjà passée : publier immédiatement.
            $attributes['status'] = OfferStatus::Published->value;
            $attributes['published_at'] = $publishedAt->toDateTimeString();
        }

        return $attributes;
    }
}
