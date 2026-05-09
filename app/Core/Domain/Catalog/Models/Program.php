<?php

namespace App\Core\Domain\Catalog\Models;

use App\Core\Domain\Catalog\QueryBuilders\ProgramBuilder;
use App\Core\Domain\Location\Models\GeographicZone;
use App\Core\Domain\Location\Models\Language; // 🟢 Ajout
use Illuminate\Database\Eloquent\Attributes\UseEloquentBuilder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Spatie\Translatable\HasTranslations;
use App\Core\Domain\Candidacy\Models\RequiredDocument;

#[UseEloquentBuilder(ProgramBuilder::class)]
class Program extends Model
{
    use HasFactory, SoftDeletes, HasTranslations;

    protected $table = 'programs';

    public array $translatable = ['name', 'description', 'slug'];

    protected $fillable = [
        'name', 'description', 'slug', 'geographic_zone_id',
        'procedure_cost', 'currency', 'procedure_duration', 'duration_unit',
        'required_age', 'image', 'meta', 'status', 
        'start_date', 'end_date', 'published_at', 'user_id',
    ];

    protected $casts = [
        'procedure_cost'     => 'float',
        'procedure_duration' => 'integer',
        'start_date'         => 'date',
        'end_date'           => 'date',
        'published_at'       => 'datetime',
        'meta'               => 'array',
    ];

    // --- RELATIONS ---

    /**
     * Langues acceptées dans ce programme d'immigration/mobilité
     */
    public function languages(): BelongsToMany
    {
        return $this->belongsToMany(Language::class, 'language_program')
                    ->withPivot('is_mandatory')
                    ->withTimestamps();
    }

    public function offers(): HasMany
    {
        return $this->hasMany(Offer::class, 'program_id');
    }

    public function geographicZone(): BelongsTo
    {
        return $this->belongsTo(GeographicZone::class, 'geographic_zone_id');
    }

    public function requiredDocuments(): BelongsToMany
    {
        return $this->belongsToMany(RequiredDocument::class, 'program_required_document')
                    ->withPivot('is_mandatory', 'sort_order')
                    ->withTimestamps();
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($program) {
            if (empty($program->slug)) {
                $nameFr = $program->getTranslation('name', 'fr') ?? current($program->name);
                $slugBase = Str::slug($nameFr) . '-' . Str::random(5);
                $program->setTranslation('slug', 'fr', $slugBase);
                $program->setTranslation('slug', 'en', $slugBase);
            }
        });
    }
}