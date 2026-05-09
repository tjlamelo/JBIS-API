<?php

namespace App\Core\Domain\Catalog\Models;

use App\Core\Domain\Catalog\QueryBuilders\OfferBuilder;
use App\Core\Domain\Catalog\States\OfferStatus;
use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Location\Models\Country;
use App\Core\Domain\Location\Models\City;
use App\Core\Domain\Location\Models\Language; // 🟢 Ajout
use Illuminate\Database\Eloquent\Attributes\UseEloquentBuilder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Spatie\Translatable\HasTranslations;
use App\Core\Domain\Candidacy\Models\RequiredDocument;
use App\Core\Domain\Catalog\Models\Benefit;
use App\Core\Domain\Catalog\Models\Skill;

#[UseEloquentBuilder(OfferBuilder::class)]
class Offer extends Model
{
    use HasFactory, HasTranslations, SoftDeletes;

    protected $table = 'offers';

    public array $translatable = [
        'title', 'description', 'specific_documents', 
        'responsibilities', 'requirements', 'slug'
    ];

    protected $fillable = [
        'title', 'description', 'photo', 'slug', 
        'salary_min', 'salary_max', 'currency', 'is_salary_public', 
        'available_positions', 'address', 'work_mode',
        'offer_category_id', 'contract_type_id', 'city_id', 'country_id', 
        'company_id', 'program_id', 'user_id',
        'offer_type_id', 'work_schedule_id', 'education_level_id',
        'meta', 'published_at', 'expiration_date', 'status', 'is_company_public', 
    ];

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

    public function city(): BelongsTo { return $this->belongsTo(City::class); }
    public function country(): BelongsTo { return $this->belongsTo(Country::class); }
    public function contractType(): BelongsTo { return $this->belongsTo(ContractType::class); }
    public function offerType(): BelongsTo { return $this->belongsTo(OfferType::class); }
    public function workSchedule(): BelongsTo { return $this->belongsTo(WorkSchedule::class); }
    public function educationLevel(): BelongsTo { return $this->belongsTo(EducationLevel::class); }
    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function program(): BelongsTo { return $this->belongsTo(Program::class); }
    public function category(): BelongsTo { return $this->belongsTo(OfferCategory::class, 'offer_category_id'); }
    public function benefits(): BelongsToMany { return $this->belongsToMany(Benefit::class); }
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
        static::saving(function ($offer) {
            $offer->meta = array_merge([
                'is_featured' => false,
                'is_urgent' => false,
                'seo' => ['robots' => 'index, follow'],
            ], $offer->meta ?? []);

            if (empty($offer->getTranslations('slug'))) {
                $slugs = [];
                $uniqueSuffix = Str::random(5);
                foreach ($offer->getTranslations('title') as $locale => $title) {
                    $slugs[$locale] = Str::slug($title) . '-' . $uniqueSuffix;
                }
                $offer->setTranslations('slug', $slugs);
            }
        });
    }
}