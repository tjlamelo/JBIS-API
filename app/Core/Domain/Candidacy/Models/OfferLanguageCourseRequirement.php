<?php

declare(strict_types=1);

namespace App\Core\Domain\Candidacy\Models;

use App\Core\Domain\Catalog\Models\Offer;
use App\Core\Domain\Catalog\Models\Training;
use App\Core\Domain\Location\Models\Language;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfferLanguageCourseRequirement extends Model
{
    protected $table = 'offer_language_course_requirements';

    protected $fillable = [
        'offer_id',
        'language_id',
        'training_id',
        'target_level',
        'is_mandatory',
        'observations',
    ];

    protected $casts = [
        'offer_id' => 'integer',
        'language_id' => 'integer',
        'training_id' => 'integer',
        'is_mandatory' => 'boolean',
    ];

    public function offer(): BelongsTo
    {
        return $this->belongsTo(Offer::class);
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }

    public function training(): BelongsTo
    {
        return $this->belongsTo(Training::class);
    }
}
