<?php

declare(strict_types=1);

namespace App\Core\Domain\Candidacy\Models;

use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Models\UserDocument;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationDocument extends Model
{
    protected $table = 'application_documents';

    protected $fillable = [
        'application_id',
        'user_document_id',
        'application_step_id',
        'status',
        'admin_notes',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'application_id' => 'integer',
        'user_document_id' => 'integer',
        'application_step_id' => 'integer',
        'reviewed_by' => 'integer',
        'reviewed_at' => 'datetime',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function userDocument(): BelongsTo
    {
        return $this->belongsTo(UserDocument::class);
    }

    public function applicationStep(): BelongsTo
    {
        return $this->belongsTo(ApplicationStep::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
