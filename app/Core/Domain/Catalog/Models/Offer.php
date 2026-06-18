<?php

namespace App\Core\Domain\Catalog\Models;

use App\Core\Domain\Candidacy\Models\RequiredDocument;
use App\Core\Domain\Catalog\QueryBuilders\OfferBuilder;
use App\Core\Domain\Catalog\States\OfferStatus;
use App\Core\Domain\Location\Models\City;
use App\Core\Domain\Location\Models\Country; // 🟢 Ajout
use App\Core\Domain\Location\Models\Language;
use App\Core\Domain\Shared\Media\Actions\DeleteMediaAction;
use App\Core\Domain\Shared\Media\Support\MediaUrlResolver;
use Database\Factories\OfferFactory;
use Illuminate\Database\Eloquent\Attributes\UseEloquentBuilder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Spatie\Translatable\HasTranslations;

#[UseEloquentBuilder(OfferBuilder::class)]
class Offer extends Model
{
    use HasFactory, HasTranslations, SoftDeletes;

    protected static function newFactory(): OfferFactory
    {
        return OfferFactory::new();
    }

    protected $table = 'offers';

    public array $translatable = [
        'description', 'specific_documents',
        'responsibilities', 'requirements', 'slug',
    ];

    protected $fillable = [
        'description', 'slug', 'trade_id',
        'salary_min', 'salary_max', 'currency', 'is_salary_public',
        'available_positions', 'address', 'work_mode',
        'category_id', 'contract_type_id', 'city_id', 'country_id',
        'company_id', 'program_id', 'user_id',
        'offer_type_id', 'work_schedule_id', 'education_level_id',
        'meta', 'published_at', 'expiration_date', 'status', 'is_company_public',
        'photo_media',
    ];

    /**
     * Accessor : renvoie l'URL primaire (Cloudinary si activé) + fallback (assets.jbis.cm).
     *
     * @return array{url:string, fallback_url:?string}|null
     */
    public function getPhotoAttribute(): ?array
    {
        return app(MediaUrlResolver::class)
            ->all(is_array($this->photo_media) ? $this->photo_media : null);
    }

    protected $casts = [
        'salary_min' => 'float',
        'salary_max' => 'float',
        'available_positions' => 'integer',
        'is_salary_public' => 'boolean',
        'is_company_public' => 'boolean',
        'published_at' => 'datetime',
        'expiration_date' => 'datetime',
        'status' => OfferStatus::class,
        'meta' => 'array',
        'photo_media' => 'array',
    ];

    // --- RELATIONS ---

    /**
     * Langues requises pour cette offre spécifique
     */
    public function languages(): BelongsToMany
    {
        return $this->belongsToMany(Language::class, 'language_offer')
            ->withPivot('required_level', 'language_level_id')
            ->withTimestamps();
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function contractType(): BelongsTo
    {
        return $this->belongsTo(ContractType::class);
    }

    public function offerType(): BelongsTo
    {
        return $this->belongsTo(OfferType::class);
    }

    public function workSchedule(): BelongsTo
    {
        return $this->belongsTo(WorkSchedule::class);
    }

    public function educationLevel(): BelongsTo
    {
        return $this->belongsTo(EducationLevel::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function trade(): BelongsTo
    {
        return $this->belongsTo(Trade::class);
    }

    /**
     * @return array<string, string>
     */
    public function resolvedTitleTranslations(): array
    {
        if ($this->relationLoaded('trade') && $this->trade) {
            return $this->trade->getTranslations('name');
        }

        if ($this->trade_id) {
            return Trade::query()->find($this->trade_id)?->getTranslations('name') ?? [];
        }

        return [];
    }

    public function benefits(): BelongsToMany
    {
        return $this->belongsToMany(Benefit::class);
    }

    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class, 'offer_skill')
            ->withPivot('level')
            ->withTimestamps();
    }

    public function requiredDocuments(): BelongsToMany
    {
        return $this->belongsToMany(RequiredDocument::class, 'offer_required_document')
            ->withPivot('is_mandatory', 'sort_order')
            ->withTimestamps();
    }

    protected static function boot()
    {
        parent::boot();

        static::updating(function (Offer $offer): void {
            if (! $offer->isDirty('photo_media')) {
                return;
            }

            $original = $offer->getOriginal('photo_media');
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

        static::deleting(function (Offer $offer): void {
            $media = $offer->photo_media;
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

        static::saving(function (Offer $offer): void {
            $offer->meta = array_merge([
                'is_featured' => false,
                'is_urgent' => false,
                'seo' => ['robots' => 'index, follow'],
            ], $offer->meta ?? []);

            if ($offer->trade_id && ($offer->isDirty('trade_id') || ! $offer->category_id)) {
                $trade = $offer->relationLoaded('trade')
                    ? $offer->trade
                    : Trade::query()->find($offer->trade_id);

                if ($trade) {
                    $offer->category_id = $trade->category_id;
                }
            }

            if (empty($offer->getTranslations('slug')) && $offer->trade_id) {
                $trade = $offer->relationLoaded('trade')
                    ? $offer->trade
                    : Trade::query()->find($offer->trade_id);

                if ($trade) {
                    $slugs = [];
                    $uniqueSuffix = Str::random(5);
                    foreach ($trade->getTranslations('name') as $locale => $name) {
                        if ($name !== '') {
                            $slugs[$locale] = Str::slug($name).'-'.$uniqueSuffix;
                        }
                    }

                    if ($slugs !== []) {
                        $offer->setTranslations('slug', $slugs);
                    }
                }
            }
        });
    }
}
