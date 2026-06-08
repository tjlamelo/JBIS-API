<?php

namespace App\Core\Domain\Identity\Models;

use App\Core\Domain\Catalog\Models\Agency;
use App\Core\Domain\Communication\Models\DiscoverySource;
use App\Core\Domain\Identity\Concerns\AuditedModel;
use App\Core\Domain\Recruiter\Enums\ProfileOrigin;
use App\Core\Domain\Recruiter\Models\RecruiterOrganization;
use App\Core\Domain\Recruiter\Models\RecruiterProfileSubmission;
use App\Core\Domain\Shared\Geography\Models\City;
use App\Core\Domain\Shared\Geography\Models\Country;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserProfile extends AuditedModel
{
    protected $table = 'user_profiles';

    protected $fillable = [
        'user_id',
        'profile_origin',
        'first_name',
        'last_name',
        'date_of_birth',
        'place_of_birth',
        'nationality_country_id',
        'residence_city_id',
        'address',
        'phone_number2',
        'phone_number3',
        'gender',
        'pictures',
        'bio',
        'marital_status',
        'number_of_children',
        'matricule',
        'email_institutional',
        'is_approved',
        'approved_by',
        'agency_id',
        'recruiter_organization_id',
        'recruiter_submission_id',
        'total_years_of_experience',
    ];

    protected $casts = [
        'profile_origin' => ProfileOrigin::class,
        'date_of_birth' => 'date',
        'pictures' => 'array',
        'number_of_children' => 'integer',
        'is_approved' => 'boolean',
        'agency_id' => 'integer',
    ];

    /**
     * Relation avec l'utilisateur (Compte)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function discoverySource(): BelongsTo
    {
        return $this->belongsTo(DiscoverySource::class);
    }

    /**
     * Pays de nationalité
     */
    public function nationality(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'nationality_country_id');
    }

    /**
     * Ville de résidence
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class, 'residence_city_id');
    }

    /**
     * Agence JBIS de rattachement
     */
    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class, 'agency_id');
    }

    /**
     * Administrateur ayant approuvé le profil
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function recruiterOrganization(): BelongsTo
    {
        return $this->belongsTo(RecruiterOrganization::class);
    }

    public function recruiterSubmission(): BelongsTo
    {
        return $this->belongsTo(RecruiterProfileSubmission::class, 'recruiter_submission_id');
    }
}
