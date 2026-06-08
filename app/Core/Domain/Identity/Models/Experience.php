<?php

namespace App\Core\Domain\Identity\Models;

use App\Core\Domain\Catalog\Models\ContractType;
use App\Core\Domain\Identity\Concerns\AuditedModel; // Assure-toi que le modèle existe ici
use App\Core\Domain\Location\Models\Country;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Experience extends AuditedModel
{
    use HasFactory, SoftDeletes;

    protected $table = 'experiences';

    protected $fillable = [
        'user_id',
        'contract_type_id',
        'document_id',
        'job_title',
        'company_name',
        'country_id',
        'city_name',
        'start_date',
        'end_date',
        'is_current',
        'responsibilities',
        'achievements',
        'status',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_current' => 'boolean',
        'user_id' => 'integer',
        'contract_type_id' => 'integer',
        'approved_at' => 'datetime',
    ];

    /**
     * Calcule la durée de l'expérience (Accessor)
     * Utilisation : $experience->duration_label
     */
    public function getDurationLabelAttribute(): string
    {
        $end = $this->is_current ? now() : $this->end_date;
        if (! $end) {
            return '';
        }

        return $this->start_date->diffForHumans($end, [
            'syntax' => Carbon::DIFF_RELATIVE_TO_NOW,
            'parts' => 2,
        ]);
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(UserDocument::class, 'document_id');
    }

    /**
     * Relation vers l'utilisateur
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation vers le type de contrat (FK)
     */
    public function contractType(): BelongsTo
    {
        return $this->belongsTo(ContractType::class);
    }

    /**
     * Relation vers le pays
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }
}
