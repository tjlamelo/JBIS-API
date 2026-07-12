<?php

declare(strict_types=1);

namespace App\Core\Domain\Catalog\QueryBuilders;

use App\Core\Domain\Catalog\States\OfferStatus;
use Illuminate\Database\Eloquent\Builder;

class OfferBuilder extends Builder
{
    /**
     * Recherche FullText optimisée avec calcul de pertinence.
     */
    public function search(string $term): self
    {
        $trimmedTerm = trim($term);
        if (empty($trimmedTerm)) {
            return $this;
        }

        return $this->where(function (Builder $query) use ($trimmedTerm): void {
            $locale = app()->getLocale();
            $lang = in_array($locale, ['fr', 'en']) ? $locale : 'fr';
            $fulltextColumns = "description_{$lang}";

            $cleanTerm = preg_replace('/[^a-zA-Z0-9\s]/', ' ', $trimmedTerm);
            $cleanTerm = trim(preg_replace('/\s+/', ' ', (string) $cleanTerm));

            $words = explode(' ', $cleanTerm);
            $booleanTerm = '';

            foreach ($words as $word) {
                $len = strlen($word);
                if ($len >= 3) {
                    $booleanTerm .= "+{$word}* ";
                } elseif ($len > 0) {
                    $booleanTerm .= "{$word}* ";
                }
            }
            $booleanTerm = trim($booleanTerm);

            if (empty($booleanTerm)) {
                $query->where(function (Builder $inner) use ($trimmedTerm, $locale): void {
                    $inner->whereHas('trade', function (Builder $trade) use ($trimmedTerm, $locale): void {
                        $trade->where("name->{$locale}", 'like', "%{$trimmedTerm}%");
                    })->orWhere("description->{$locale}", 'like', "%{$trimmedTerm}%");
                });

                return;
            }

            $this->selectRaw("*, 
                MATCH({$fulltextColumns}) AGAINST(? IN NATURAL LANGUAGE MODE) as relevance_score",
                [$cleanTerm]
            );

            $query->where(function (Builder $inner) use ($booleanTerm, $fulltextColumns, $words, $locale): void {
                $inner->whereRaw(
                    "MATCH({$fulltextColumns}) AGAINST(? IN BOOLEAN MODE)",
                    [$booleanTerm]
                )->orWhereHas('trade', function (Builder $trade) use ($words, $locale): void {
                    foreach ($words as $word) {
                        if (strlen($word) >= 2) {
                            $trade->where("name->{$locale}", 'like', "%{$word}%");
                        }
                    }
                });
            });
        });
    }

    /**
     * Filtre les offres strictement publiées et déjà en ligne (published_at atteint).
     */
    public function published(): self
    {
        return $this
            ->where('status', OfferStatus::Published)
            ->where(function (Builder $query): void {
                $query->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            });
    }

    /**
     * Brouillons prêts à passer en ligne (publication planifiée atteinte).
     */
    public function dueForScheduledPublication(): self
    {
        return $this
            ->where('status', OfferStatus::Draft)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    /**
     * Filtre les offres qui ne sont pas encore expirées.
     */
    public function notExpired(): self
    {
        return $this->where(function (Builder $query) {
            $query->whereNull('expiration_date')
                ->orWhere('expiration_date', '>=', now());
        });
    }

    /**
     * Filtre les offres liées à un programme spécifique.
     */
    public function forProgram(string|int $programIdOrSlug): self
    {
        if (is_numeric($programIdOrSlug)) {
            return $this->where('program_id', $programIdOrSlug);
        }

        return $this->whereHas('program', function (Builder $query) use ($programIdOrSlug) {
            $query->where('slug', $programIdOrSlug);
        });
    }
}
