<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Models;

use App\Core\Domain\Identity\Concerns\AuditedModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Archive extends AuditedModel
{
    use HasFactory;
    use SoftDeletes;

    public const STORAGE_DISK = 'jbis_assets';

    protected $table = 'archives';

    protected $fillable = [
        'user_id',
        'uploaded_by',
        'related_user_id',
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
        'uploaded_by' => 'integer',
        'related_user_id' => 'integer',
    ];

    public function getUrlAttribute(): ?string
    {
        if (! $this->stored_name) {
            return null;
        }

        $disk = (string) ($this->disk ?: self::STORAGE_DISK);

        return Storage::disk($disk)->url($this->stored_name);
    }

    public function getReadableSizeAttribute(): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max((int) $this->size, 0);
        $pow = (int) floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= 1024 ** $pow;

        return round($bytes, 2).' '.$units[$pow];
    }

    public function isImage(): bool
    {
        return str_starts_with((string) $this->mime_type, 'image/');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function relatedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'related_user_id');
    }
}
