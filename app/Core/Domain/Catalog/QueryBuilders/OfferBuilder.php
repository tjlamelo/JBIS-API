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
    if (empty($trimmedTerm)) return $this;

    return $this->where(function (Builder $query) use ($trimmedTerm): void {
        $locale = app()->getLocale();
        $lang = in_array($locale, ['fr', 'en']) ? $locale : 'fr';
        $fulltextColumns = "title_{$lang}, description_{$lang}";

        // 1. NETTOYAGE RIGOUREUX
        // On ne garde que les lettres, chiffres et espaces
        $cleanTerm = preg_replace('/[^a-zA-Z0-9\s]/', ' ', $trimmedTerm);
        $cleanTerm = trim(preg_replace('/\s+/', ' ', $cleanTerm));

        // 2. PRÉPARATION DU TERME BOOLEAN (Souple)
        $words = explode(' ', $cleanTerm);
        $booleanTerm = '';
        
        foreach ($words as $word) {
            $len = strlen($word);
            if ($len >= 3) {
                // 🟢 MOTS LONGS : Obligatoires (+) avec recherche partielle (*)
                $booleanTerm .= "+{$word}* ";
            } elseif ($len > 0) {
                // 🟡 MOTS COURTS (ex: H, F) : Optionnels (pas de +)
                // Ils boostent le score s'ils sont là, mais ne bloquent pas la recherche
                $booleanTerm .= "{$word}* ";
            }
        }
        $booleanTerm = trim($booleanTerm);

        // Fallback si aucun terme n'est exploitable
        if (empty($booleanTerm)) {
            $query->where("title->{$locale}", 'like', "%{$trimmedTerm}%");
            return;
        }

        // 3. CALCUL DU SCORE (Pour le classement qualitatif)
        // On utilise le mode NATURAL LANGUAGE pour une pertinence humaine
        $this->selectRaw("*, 
            MATCH({$fulltextColumns}) AGAINST(? IN NATURAL LANGUAGE MODE) as relevance_score", 
            [$cleanTerm]
        );

        // 4. FILTRE DYNAMIQUE (Mode BOOLEAN)
        $query->whereRaw(
            "MATCH({$fulltextColumns}) AGAINST(? IN BOOLEAN MODE)", 
            [$booleanTerm]
        );
    });
}
    /**
     * Filtre les offres qui sont strictement publiées.
     */
    public function published(): self
    {
        return $this->where('status', OfferStatus::Published);
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