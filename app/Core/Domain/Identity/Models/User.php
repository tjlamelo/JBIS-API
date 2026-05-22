<?php

namespace App\Core\Domain\Identity\Models;

use App\Core\Domain\Candidacy\Models\Application;
use App\Core\Domain\Catalog\Models\Offer;
use App\Core\Domain\Catalog\Models\OfferCategory;
use App\Core\Domain\Identity\Builders\UserBuilder;
use App\Core\Domain\Identity\Concerns\HasPermissionOverrides;
use App\Core\Domain\Identity\Support\ApplicationPermission;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Contracts\Permission;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, HasPermissionOverrides, HasRoles, Notifiable, TwoFactorAuthenticatable {
        HasRoles::hasPermissionTo as protected hasPermissionToViaRoles;
    }

    /**
     * @param  string|int|Permission|\BackedEnum  $permission
     */
    public function hasPermissionTo($permission, $guardName = null): bool
    {
        $overrideResult = $this->applyPermissionOverrideBeforeRoleCheck($permission);
        if ($overrideResult !== null) {
            return $overrideResult;
        }

        return $this->hasPermissionToViaRoles($permission, $guardName ?? ApplicationPermission::GUARD);
    }

    /**
     * API Sanctum : garder le guard Spatie aligné sur les permissions seedées (`web`).
     */
    public function getDefaultGuardName(): string
    {
        return ApplicationPermission::GUARD;
    }

    /**
     * Résout le problème du Seeder (DDD Architecture)
     */
    protected static function newFactory()
    {
        return UserFactory::new();
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
        'active',
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
            'active' => 'boolean',
        ];
    }

    public function newEloquentBuilder($query): UserBuilder
    {
        return new UserBuilder($query);
    }

    public function profile(): HasOne
    {
        return $this->hasOne(UserProfile::class);
    }

    public function sectors(): BelongsToMany
    {
        return $this->belongsToMany(OfferCategory::class, 'user_sector', 'user_id', 'offer_category_id')
            ->withTimestamps();
    }

    public function documents()
    {
        return $this->hasMany(UserDocument::class);
    }

    public function experiences(): HasMany
    {
        return $this->hasMany(Experience::class);
    }

    public function languages()
    {
        return $this->hasMany(Language::class);
    }

    public function educations(): HasMany
    {
        return $this->hasMany(Education::class);
    }

    public function certifications(): HasMany
    {
        return $this->hasMany(Certification::class);
    }

    public function Offers()
    {
        // CORRECTION DE LA CLÉ ÉTRANGÈRE ICI 👇 (user_id au lieu de posted_by)
        return $this->hasMany(Offer::class, 'user_id');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    public function devices(): HasMany
    {
        return $this->hasMany(UserDevice::class);
    }

    public function settings(): HasOne
    {
        return $this->hasOne(UserSetting::class);
    }

    public function consents(): HasMany
    {
        return $this->hasMany(UserConsent::class);
    }

    public function preferredCountries(): HasMany
    {
        return $this->hasMany(UserPreferredCountry::class);
    }

    public function visaHistories(): HasMany
    {
        return $this->hasMany(UserVisaHistory::class);
    }

    /**
     * Notes internes (staff) rattachées à ce compte candidat.
     */
    public function staffNotes(): HasMany
    {
        return $this->hasMany(UserNote::class, 'user_id');
    }

    /**
     * Notes rédigées par cet utilisateur (agent / admin).
     */
    public function authoredStaffNotes(): HasMany
    {
        return $this->hasMany(UserNote::class, 'author_id');
    }
}
