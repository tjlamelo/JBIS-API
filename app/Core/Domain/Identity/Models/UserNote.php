<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Models;

use App\Core\Domain\Identity\Concerns\AuditedModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserNote extends AuditedModel
{
    protected $table = 'user_notes';

    protected $fillable = [
        'user_id',
        'author_id',
        'content',
        'is_private',
    ];

    protected function casts(): array
    {
        return [
            'is_private' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
