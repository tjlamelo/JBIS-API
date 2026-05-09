<?php

declare(strict_types=1);

namespace App\Core\Domain\Catalog\QueryBuilders;

use Illuminate\Database\Eloquent\Builder;

class ProgramBuilder extends Builder
{
    /**
     * Recherche FullText optimisée et multilingue pour les programmes.
     * Interroge les index virtuels MySQL configurés dans la migration.
     *
     * @param string $term
     * @return self
     */
    public function search(string $term): self
    {
        return $this->where(function (Builder $query) use ($term): void {
            $locale = app()->getLocale();

            // Vérification de la disponibilité de l'index FullText pour la langue active
            if (in_array($locale, ['fr', 'en'])) {
                $searchTerm = $term . '*'; // Permet la recherche de mots incomplets
                
                $query->whereRaw(
                    "MATCH(name_{$locale}, description_{$locale}) AGAINST(? IN BOOLEAN MODE)", 
                    [$searchTerm]
                );
            } else {
                // Fallback si la langue (ex: 'es') n'a pas de colonne virtuelle indexée
                $query->where('name->' . $locale, 'like', "%{$term}%")
                      ->orWhere('description->' . $locale, 'like', "%{$term}%");
            }
        });
    }

    /**
     * Filtre les programmes qui sont actifs.
     *
     * @return self
     */
    public function active(): self
    {
        return $this->where('status', 'active');
    }

    /**
     * Filtre les programmes valides selon les dates de début et de fin.
     * (Un programme sans date de fin est considéré comme toujours valide).
     *
     * @return self
     */
    public function validDates(): self
    {
        return $this->where(function (Builder $query) {
            $now = now()->toDateString();
            
            $query->where(function ($q) use ($now) {
                // Doit avoir commencé (ou pas de date de début définie)
                $q->whereNull('start_date')
                  ->orWhere('start_date', '<=', $now);
            })->where(function ($q) use ($now) {
                // Ne doit pas être terminé (ou pas de date de fin définie)
                $q->whereNull('end_date')
                  ->orWhere('end_date', '>=', $now);
            });
        });
    }

/**
     * Filtre les programmes par zone géographique (ID).
     *
     * @param string|int $zoneId
     * @return self
     */
    public function byZoneId($zoneId): self
    {
        return $this->where('geographic_zone_id', $zoneId);
    }

    /**
     * Filtre les programmes par pays.
     *
     * @param string $country
     * @return self
     */
    public function byCountry(string $country): self
    {
        return $this->where('country', $country);
    }

    /**
     * Filtre les programmes qui ont au moins une offre d'emploi publiée.
     * Très utile pour ne pas afficher un programme vide aux candidats.
     *
     * @return self
     */
    public function withActiveOffers(): self
    {
        return $this->whereHas('Offers', function (Builder $query) {
            // Fait appel au scope 'published' défini dans JobOfferBuilder
            $query->published();
        });
    }
}