<?php

namespace App\Core\Domain\Catalog\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

class SkillCategory extends Model
{
    use HasFactory, HasTranslations;

    protected $table = 'skill_categories';

    public array $translatable = ['name'];

    protected $fillable = [
        'name',
        'slug',
    ];

    public function skills(): HasMany
    {
        return $this->hasMany(Skill::class, 'skill_category_id');
    }
}
