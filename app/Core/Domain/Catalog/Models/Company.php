<?php

namespace App\Core\Domain\Catalog\Models;

use App\Core\Domain\Location\Models\City;
use App\Core\Domain\Location\Models\Country;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Company extends Model
{
    use HasFactory, SoftDeletes;

    protected static function newFactory()
    {
        return \Database\Factories\CompanyFactory::new();
    }

    protected $fillable = [
        'name',
        'slug',
        'category_id',
        'country_id',
        'city_id',
        'address',
        'type',
        'status',
        'email',
        'phone',
        'website',
        'description',
        'logo',
        'is_approved',
        'approved_by',
    ];

    protected $casts = [
        'is_approved' => 'boolean',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function (Company $company): void {
            if (empty($company->slug)) {
                $company->slug = Str::slug($company->name).'-'.Str::random(5);
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function offers(): HasMany
    {
        return $this->hasMany(Offer::class, 'company_id');
    }
}
