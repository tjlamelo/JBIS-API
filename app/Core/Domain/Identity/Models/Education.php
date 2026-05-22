<?php

namespace App\Core\Domain\Identity\Models;

use App\Core\Domain\Identity\Concerns\AuditsModelChanges;
use App\Core\Domain\Catalog\Models\EducationLevel;
use App\Core\Domain\Location\Models\Country;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Education extends Model
{
    use AuditsModelChanges, SoftDeletes;

    protected $table = 'education';

    protected $fillable = [
        'user_id',
        'education_level_id',
        'document_id', // Clé étrangère vers user_documents
        'degree',
        'institution_name',
        'field_of_study',
        'country_id',
        'residence_city_id',
        'start_date',
        'end_date',
        'is_current',
        'grade',
        'is_approved',
        'approved_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_current' => 'boolean',
        'user_id' => 'integer',
        'education_level_id' => 'integer',
        'document_id' => 'integer',
        'is_approved' => 'boolean',
        'approved_by' => 'integer',
    ];

    /**
     * Le document (scan) associé à ce diplôme
     */
    public function document(): BelongsTo
    {
        return $this->belongsTo(UserDocument::class, 'document_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(EducationLevel::class, 'education_level_id');
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }
}
