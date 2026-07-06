<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Models;

use App\Core\Domain\Shared\Ai\Enums\DocumentExtractionStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserDocumentExtraction extends Model
{
    protected $table = 'user_document_extractions';

    protected $fillable = [
        'user_document_id',
        'user_id',
        'document_type_code',
        'status',
        'draft_payload',
        'applied_payload',
        'error_message',
        'reviewed_by',
        'reviewed_at',
        'applied_at',
    ];

    protected $casts = [
        'user_document_id' => 'integer',
        'user_id' => 'integer',
        'draft_payload' => 'array',
        'applied_payload' => 'array',
        'reviewed_by' => 'integer',
        'reviewed_at' => 'datetime',
        'applied_at' => 'datetime',
        'status' => DocumentExtractionStatus::class,
    ];

    public function userDocument(): BelongsTo
    {
        return $this->belongsTo(UserDocument::class, 'user_document_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
