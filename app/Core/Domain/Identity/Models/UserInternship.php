<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Models;

use App\Core\Domain\Identity\Concerns\AuditsModelChanges;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserInternship extends Model
{
    use AuditsModelChanges;

    protected $table = 'internships';

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'organization',
        'location',
        'supervisor_name',
        'supervisor_contact',
        'start_date',
        'end_date',
        'is_current',
        'description',
        'technologies',
        'certificate_document_id',
        'status',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
        'is_current' => 'boolean',
        'certificate_document_id' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function certificateDocument(): BelongsTo
    {
        return $this->belongsTo(UserDocument::class, 'certificate_document_id');
    }
}
