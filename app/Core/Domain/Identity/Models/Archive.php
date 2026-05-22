<?php

namespace App\Core\Domain\Identity\Models;

use App\Core\Domain\Identity\Concerns\AuditsModelChanges;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Archive extends Model
{
    use AuditsModelChanges, HasFactory, SoftDeletes;

    protected $table = 'archives';

    protected $fillable = [
        'user_id',
        'original_name',
        'stored_name',
        'file_type',
        'extension',
        'mime_type',
        'size',
        'category',
        'description',
        'disk',
        'is_public',
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'size' => 'integer',
        'user_id' => 'integer',
    ];

    public function getUrlAttribute(): ?string
    {
        return $this->stored_name
            ? Storage::disk($this->disk)->url($this->stored_name)
            : null;
    }

    public function getReadableSizeAttribute(): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($this->size, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, 2).' '.$units[$pow];
    }

    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
