<?php

declare(strict_types=1);

namespace App\Core\Domain\Recruiter\Models;

use App\Core\Domain\Catalog\Models\Company;
use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Recruiter\Enums\RecruiterOrganizationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RecruiterOrganization extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'company_id',
        'status',
        'portal_host',
        'api_host',
        'mailbox_email',
        'provisioning_error',
        'provisioned_at',
        'settings',
    ];

    protected $casts = [
        'status' => RecruiterOrganizationStatus::class,
        'settings' => 'array',
        'provisioned_at' => 'datetime',
        'company_id' => 'integer',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'recruiter_organization_user')
            ->withPivot('is_owner')
            ->withTimestamps();
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(RecruiterProfileSubmission::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(RecruiterProfileAssignment::class);
    }

    public function offerSubmissions(): HasMany
    {
        return $this->hasMany(RecruiterOfferSubmission::class);
    }

    public function onboardingApplication(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(RecruiterOnboardingApplication::class, 'recruiter_organization_id');
    }
}
