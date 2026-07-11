<?php

declare(strict_types=1);

namespace App\Core\Domain\Partner\Models;

use App\Core\Domain\Catalog\Models\Company;
use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Partner\Enums\PartnerOrganizationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PartnerOrganization extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'company_id',
        'status',
        'settings',
    ];

    protected $casts = [
        'status' => PartnerOrganizationStatus::class,
        'settings' => 'array',
        'company_id' => 'integer',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'partner_organization_user')
            ->withPivot('is_owner')
            ->withTimestamps();
    }

    public function cohorts(): HasMany
    {
        return $this->hasMany(PartnerCohort::class);
    }
}
