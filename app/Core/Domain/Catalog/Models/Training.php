<?php

declare(strict_types=1);

namespace App\Core\Domain\Catalog\Models;

use App\Core\Domain\Identity\Models\UserTraining;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Training extends Model
{
    protected $table = 'trainings';

    protected $fillable = [
        'domain',
        'title',
        'organization',
        'description',
        'start_date',
        'end_date',
        'duration_hours',
        'duration_days',
        'mode',
        'location',
        'prerequisites',
        'is_certified',
        'certificate_name',
        'is_active',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'duration_hours' => 'integer',
        'duration_days' => 'integer',
        'is_certified' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function userTrainings(): HasMany
    {
        return $this->hasMany(UserTraining::class, 'training_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
