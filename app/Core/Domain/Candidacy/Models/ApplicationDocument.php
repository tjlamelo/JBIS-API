<?php

namespace App\Core\Domain\Candidacy\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationDocument extends Model
{
    protected $table = 'application_documents';

    protected $fillable = [
        'application_id',
        'required_document_id',
        'user_document_id',
        'reviewed_by',
        'review_date',
        'comments',
        'status',
    ];

    protected $dates = [
        'review_date',
        'created_at',
        'updated_at',
    ];

    // Relations
    public function application(): BelongsTo
    {
        return $this->belongsTo(\App\Core\Domain\Candidacy\Models\Application::class);
    }

    public function requiredDocument(): BelongsTo
    {
        return $this->belongsTo(\App\Core\Domain\Candidacy\Models\RequiredDocument::class);
    }

    public function userDocument(): BelongsTo
    {
        return $this->belongsTo(\App\Core\Domain\Identity\Models\UserDocument::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(\App\Core\Domain\Identity\Models\User::class, 'reviewed_by');
    }
}