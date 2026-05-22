<?php

namespace App\Core\Domain\Catalog\Models;

use App\Core\Domain\Candidacy\Models\RequiredDocument;
use App\Core\Domain\Catalog\QueryBuilders\ProgramBuilder;
use App\Core\Domain\Location\Models\GeographicZone;
use App\Core\Domain\Location\Models\Language;
use App\Core\Domain\Shared\Media\Actions\DeleteMediaAction;
use App\Core\Domain\Shared\Media\Support\MediaUrlResolver;
use Illuminate\Database\Eloquent\Attributes\UseEloquentBuilder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Spatie\Translatable\HasTranslations;

#[UseEloquentBuilder(ProgramBuilder::class)]
class Program extends Model
{
    use HasFactory, HasTranslations, SoftDeletes;

    protected $table = 'programs';

    public array $translatable = ['name', 'description', 'slug'];

    protected $fillable = [
        'name', 'description', 'slug', 'geographic_zone_id',
        'procedure_duration', 'duration_unit',
        'age_min', 'age_max',
        'is_featured', 'is_urgent', 'views_count',
        'image_media', 'status',
        'start_date', 'end_date', 'published_at', 'user_id',
    ];

    /**
     * @return array{url:string, fallback_url:?string}|null
     */
    public function getImageAttribute(): ?array
    {
        return app(MediaUrlResolver::class)
            ->all(is_array($this->image_media) ? $this->image_media : null);
    }

    protected $casts = [
        'procedure_duration' => 'integer',
        'age_min' => 'integer',
        'age_max' => 'integer',
        'is_featured' => 'boolean',
        'is_urgent' => 'boolean',
        'views_count' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
        'published_at' => 'datetime',
        'image_media' => 'array',
    ];

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

        static::updating(function (Program $program): void {
            if (! $program->isDirty('image_media')) {
                return;
            }

            $original = $program->getOriginal('image_media');
            if (! is_array($original) || $original === []) {
                return;
            }

            $optimizedPath = (string) ($original['local_optimized_path'] ?? '');
            $rawPath = (string) ($original['local_raw_path'] ?? '');
            $cloudinaryId = isset($original['cloudinary_id']) ? (string) $original['cloudinary_id'] : null;

            if ($optimizedPath === '' && $rawPath === '' && ! $cloudinaryId) {
                return;
            }

            app(DeleteMediaAction::class)->execute($optimizedPath, $rawPath, $cloudinaryId);
        });

        static::deleting(function (Program $program): void {
            $media = $program->image_media;
            if (! is_array($media) || $media === []) {
                return;
            }

            $optimizedPath = (string) ($media['local_optimized_path'] ?? '');
            $rawPath = (string) ($media['local_raw_path'] ?? '');
            $cloudinaryId = isset($media['cloudinary_id']) ? (string) $media['cloudinary_id'] : null;

            if ($optimizedPath === '' && $rawPath === '' && ! $cloudinaryId) {
                return;
            }

            app(DeleteMediaAction::class)->execute($optimizedPath, $rawPath, $cloudinaryId);
        });

        static::creating(function ($program) {
            if (empty($program->slug)) {
                $nameFr = $program->getTranslation('name', 'fr') ?? current($program->name);
                $slugBase = Str::slug($nameFr).'-'.Str::random(5);
                $program->setTranslation('slug', 'fr', $slugBase);
                $program->setTranslation('slug', 'en', $slugBase);
            }
        });
    }
}
