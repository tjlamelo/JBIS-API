<?php

declare(strict_types=1);

namespace App\Core\Domain\Candidacy\Models;

use App\Core\Domain\Catalog\Models\Program;
use App\Core\Domain\Catalog\Models\Offer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RequiredDocument extends Model
{
    protected $table = 'required_documents';

    protected $fillable = [
        'program_id',
        'offer_id',
        'name',
        'slug',
        'type',
        'description',
        'is_mandatory',
        'template_path',
    ];

    protected $casts = [
        'program_id' => 'integer',
        'offer_id' => 'integer',
        'name' => 'array',
        'is_mandatory' => 'boolean',
    ];

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function offer(): BelongsTo
    {
        return $this->belongsTo(Offer::class);
    }
}