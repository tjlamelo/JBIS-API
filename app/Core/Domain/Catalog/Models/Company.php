<?php

namespace App\Core\Domain\Catalog\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
        'slug', // <--- IMPORTANT : Autoriser l'assignation de masse
        'industry',
        'country',
        'city',
        'address',
        'email',
        'phone',
        'website',
        'description',
        'logo',
        'is_approved',
        'approved_by'
    ];

    /**
     * Boot logic pour automatiser le slug
     */
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($company) {
            if (empty($company->slug)) {
                $company->slug = Str::slug($company->name) . '-' . Str::random(5);
            }
        });
    }

  
    public function Offers()
    {
        return $this->hasMany(\App\Core\Domain\Catalog\Models\Offer::class, 'company_id');
    }
}