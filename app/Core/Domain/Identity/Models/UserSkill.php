<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Models;

use App\Core\Domain\Catalog\Models\Skill;
use App\Core\Domain\Identity\Concerns\AuditedModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserSkill extends AuditedModel
{
    use SoftDeletes;

    protected $table = 'user_skills';

    protected $fillable = [
        'user_id',
        'skill_id',
        'years_of_experience',
        'level',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'skill_id' => 'integer',
        'years_of_experience' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function skill(): BelongsTo
    {
        return $this->belongsTo(Skill::class);
    }
}
