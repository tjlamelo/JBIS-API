<?php

namespace App\Core\Domain\Location\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Country extends Model
{
    use HasFactory, HasTranslations;

    protected $fillable = ['name', 'code', 'flag', 'phone_code', 'is_active'];

    public $translatable = ['name'];

    protected static function newFactory()
    {
        return \Database\Factories\CountryFactory::new();
    }
}