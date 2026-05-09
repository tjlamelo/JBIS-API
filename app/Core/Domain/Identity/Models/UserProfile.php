<?php

namespace App\Core\Domain\Identity\Models;

use App\Core\Domain\Catalog\Models\Agency;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserProfile extends Model
{
    protected $table = 'user_profiles';

    protected $fillable = [
        'user_id',
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
        'agencies_id',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'pictures' => 'array',
        'number_of_children' => 'integer',
        'is_approved' => 'boolean',
        'agencies_id' => 'integer',
    ];

    /**
     * Relation avec l'utilisateur (Compte)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Pays de nationalité
     */
    public function nationality(): BelongsTo
    {
        return $this->belongsTo(\App\Core\Domain\Shared\Geography\Models\Country::class, 'nationality_country_id');
    }

    /**
     * Ville de résidence
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(\App\Core\Domain\Shared\Geography\Models\City::class, 'residence_city_id');
    }

    /**
     * Agence JBIS de rattachement
     */
    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class, 'agencies_id');
    }

    /**
     * Administrateur ayant approuvé le profil
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}