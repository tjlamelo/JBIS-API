<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Models;

use App\Core\Domain\Identity\Concerns\AuditsModelChanges;
use App\Core\Domain\Identity\States\Document\UserDocumentStatus;
use App\Core\Domain\Location\Models\Country;
use Database\Factories\UserDocumentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class UserDocument extends Model
{
    use AuditsModelChanges, HasFactory, SoftDeletes;

    /** Disque local miroir (assets.jbis.cm), sans Cloudinary. */
    public const STORAGE_DISK = 'jbis_assets';

    public const STORAGE_ROOT = 'Document';

    protected $table = 'user_documents';

    protected $fillable = [
        'user_id',
        'uploaded_by',
        'document_type_id',
        'document_number',
        'issuing_country_id',
        'file_path',
        'original_filename',
        'mime_type',
        'file_size',
        'issue_date',
        'expiry_date',
        'status',
        'rejection_reason',
        'validated_at',
        'validated_by',
        'notes',
        'is_verified_copy',
        'is_sensitive',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'uploaded_by' => 'integer',
        'document_type_id' => 'integer',
        'issuing_country_id' => 'integer',
        'file_size' => 'integer',
        'validated_by' => 'integer',
        'issue_date' => 'date',
        'expiry_date' => 'date',
        'validated_at' => 'datetime',
        'is_verified_copy' => 'boolean',
        'is_sensitive' => 'boolean',
        'status' => UserDocumentStatus::class,
    ];

    protected static function newFactory(): UserDocumentFactory
    {
        return UserDocumentFactory::new();
    }

    public static function storageFolderForUser(int $userId): string
    {
        return self::STORAGE_ROOT.'/users/'.$userId;
    }

    public function getUrlAttribute(): ?string
    {
        $path = $this->file_path;
        if (! is_string($path) || $path === '') {
            return null;
        }

        return Storage::disk(self::STORAGE_DISK)->url($path);
    }

    public function documentType(): BelongsTo
    {
        return $this->belongsTo(DocumentType::class, 'document_type_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function issuingCountry(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'issuing_country_id');
    }

    public function linkedVisaHistories(): HasMany
    {
        return $this->hasMany(UserVisaHistory::class, 'document_id');
    }

    public function isExpired(): bool
    {
        return $this->expiry_date !== null && $this->expiry_date->isPast();
    }

    public function belongsToUser(int $userId): bool
    {
        return (int) $this->user_id === $userId;
    }
}
