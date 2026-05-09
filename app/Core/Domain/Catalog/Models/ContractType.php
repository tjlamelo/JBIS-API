<?php

namespace App\Core\Domain\Catalog\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class ContractType extends Model
{
    use HasFactory, HasTranslations;

    protected $table = 'contract_types';

    /**
     * Les champs traduisibles gérés par Spatie.
     * ATTENTION : Ne mets JAMAIS 'color_code' ici, sinon Laravel 
     * attendra un array au lieu d'une string.
     */
    public array $translatable = [
        'name',
        'slug',
    ];

    /**
     * Les champs autorisés pour le remplissage de masse.
     */
    protected $fillable = [
        'name',
        'slug',
        'color_code',
    ];

    // --- RELATIONS ---

    /**
     * Une offre appartient à un type de contrat.
     */
    public function offers()
    {
        return $this->hasMany(Offer::class, 'contract_type_id');
    }
}