<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Catalog\Queries;

use App\Core\Domain\Catalog\Models\Program;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ProgramIndexQuery extends QueryBuilder
{
    public function __construct()
    {
        // On utilise notre Eloquent Builder personnalisé (ProgramBuilder)
        parent::__construct(Program::query());

        $this->allowedFilters([
            // 1. Recherche FullText (Custom Builder)
            AllowedFilter::callback('search', fn($query, $value) => $query->search((string) $value)),
            
            // 2. Filtre Domaine (Gestion JSON Spatie Translatable)
            AllowedFilter::callback('domain', function ($query, $value) {
                $locale = app()->getLocale();
                $query->where("domain->{$locale}", $value);
            }),

        
            AllowedFilter::exact('geographic_zone_id'),

            // 4. Filtres exacts restants
            AllowedFilter::exact('status'),
            AllowedFilter::exact('country'),
            AllowedFilter::exact('language'),
            
            // 5. Filtres par prix (Intervalle)
            AllowedFilter::callback('min_cost', fn($query, $value) => $query->where('procedure_cost', '>=', $value)),
            AllowedFilter::callback('max_cost', fn($query, $value) => $query->where('procedure_cost', '<=', $value)),
        ])
        ->allowedSorts(['name_fr', 'name_en', 'procedure_cost', 'created_at'])
        ->defaultSort('-created_at');
    }

    /**
     * Scope de sécurité pour l'affichage public
     */
    public function public(): self
    {
        return $this->active()->validDates();
    }
}