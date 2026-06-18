<?php

namespace App\Core\Domain\Location\Models;

use App\Core\Domain\Catalog\Models\Offer;
use App\Core\Domain\Catalog\Models\Program;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Translatable\HasTranslations;

class Language extends Model
{
    use HasTranslations;

    protected $table = 'languages';

    public array $translatable = ['name'];

    protected $fillable = ['name', 'code'];

    /**
     * Relation avec les Programmes (Général)
     */
    public function programs(): BelongsToMany
    {
        return $this->belongsToMany(Program::class, 'language_program')
                    ->withPivot('is_mandatory')
                    ->withTimestamps();
    }

    /**
     * Relation avec les Offres (Spécifique)
     */
    public function offers(): BelongsToMany
    {
        return $this->belongsToMany(Offer::class, 'language_offer')
                    ->withPivot('required_level', 'language_level_id')
                    ->withTimestamps();
    }
}