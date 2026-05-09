<?php

namespace App\Core\Domain\Identity\Models;


use App\Core\Domain\Identity\Builders\UserBuilder;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasOne;
 
use Spatie\Permission\Traits\HasRoles;


class User extends Authenticatable implements MustVerifyEmail
{
 
    use HasApiTokens, HasFactory, Notifiable, HasRoles, TwoFactorAuthenticatable ;

    /**
     * Résout le problème du Seeder (DDD Architecture)
     */
    protected static function newFactory()
    {
        return \Database\Factories\UserFactory::new();
    }

    // Définir les champs à indexer
    public function toSearchableArray()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
        ];
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone_number1',
        'password',
        'active'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function newEloquentBuilder($query): UserBuilder
    {
        return new UserBuilder($query);
    }

    public function profile(): HasOne
    {
        return $this->hasOne(\App\Core\Domain\Identity\Models\UserProfile::class);
    }

    public function documents()
    {
        return $this->hasMany(\App\Core\Domain\Identity\Models\UserDocument::class);
    }

    public function experiences(): HasMany
    {
        return $this->hasMany(\App\Core\Domain\Identity\Models\Experience::class);
    }

    public function languages()
    {
        return $this->hasMany(\App\Core\Domain\Identity\Models\Language::class);
    }

    public function educations(): HasMany
    {
        return $this->hasMany(\App\Core\Domain\Identity\Models\Education::class);
    }

    public function certifications(): HasMany
    {
        return $this->hasMany(\App\Core\Domain\Catalog\Models\Certification::class);
    }

    public function Offers()
    {
        // CORRECTION DE LA CLÉ ÉTRANGÈRE ICI 👇 (user_id au lieu de posted_by)
        return $this->hasMany(\App\Core\Domain\Catalog\Models\Offer::class, 'user_id');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(\App\Core\Domain\Candidacy\Models\Application::class);
    }

    public function devices(): HasMany
    {
        return $this->hasMany(\App\Core\Domain\Identity\Models\UserDevice::class);
    }
}