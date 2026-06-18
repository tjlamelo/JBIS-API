<?php

declare(strict_types=1);

namespace App\Core\Domain\Location\Models;

use App\Core\Domain\Catalog\Models\Program;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

class GeographicZone extends Model
{
    use HasFactory, HasTranslations;

    protected $table = 'geographic_zones';

    protected $fillable = [
        'name',
        'slug',
        'sort_order',
        'is_active'
    ];

    public array $translatable = ['name'];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Relation avec les programmes
     */
    public function programs(): HasMany
    {
        return $this->hasMany(Program::class, 'geographic_zone_id');
    }
}