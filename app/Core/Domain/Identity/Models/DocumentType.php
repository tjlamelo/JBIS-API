<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Models;

use App\Core\Application\Api\Support\TranslatableColumnResolver;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentType extends Model
{
    protected $table = 'document_types';

    protected $fillable = [
        'code',
        'label',
        'storage_slug',
        'unique_per_user',
        'requires_expiry_date',
        'requires_document_number',
        'max_file_size_kb',
        'allowed_extensions',
        'allowed_mime_types',
        'sort_order',
        'is_active',
        'visible_to_candidates',
    ];

    protected $casts = [
        'label' => 'array',
        'unique_per_user' => 'boolean',
        'requires_expiry_date' => 'boolean',
        'requires_document_number' => 'boolean',
        'max_file_size_kb' => 'integer',
        'allowed_extensions' => 'array',
        'allowed_mime_types' => 'array',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
        'visible_to_candidates' => 'boolean',
    ];

    /** @return list<string> */
    public static function defaultAllowedExtensions(): array
    {
        return ['pdf', 'jpg', 'jpeg', 'png', 'webp'];
    }

    /** @return list<string> */
    public static function defaultAllowedMimeTypes(): array
    {
        return [
            'application/pdf',
            'image/jpeg',
            'image/png',
            'image/webp',
        ];
    }

    public function userDocuments(): HasMany
    {
        return $this->hasMany(UserDocument::class, 'document_type_id');
    }

    public function allowsMultiple(): bool
    {
        return ! $this->unique_per_user;
    }

    public function isUniquePerUser(): bool
    {
        return $this->unique_per_user;
    }

    public function requiresExpiryDate(): bool
    {
        return $this->requires_expiry_date;
    }

    public function requiresDocumentNumber(): bool
    {
        return $this->requires_document_number;
    }

    /** @return list<string> */
    public function allowedExtensionsList(): array
    {
        $extensions = $this->allowed_extensions;

        return is_array($extensions) && $extensions !== []
            ? array_values($extensions)
            : self::defaultAllowedExtensions();
    }

    /** @return list<string> */
    public function allowedMimeTypesList(): array
    {
        $mimes = $this->allowed_mime_types;

        return is_array($mimes) && $mimes !== []
            ? array_values($mimes)
            : self::defaultAllowedMimeTypes();
    }

    public function resolvedLabel(?string $locale = null): string
    {
        return TranslatableColumnResolver::resolve($this->label, $locale);
    }

    /**
     * @return array<string, mixed>
     */
    public function toCatalogEntry(): array
    {
        return [
            'id' => $this->id,
            'value' => $this->code,
            'code' => $this->code,
            'label' => $this->resolvedLabel(),
            'unique_per_user' => $this->isUniquePerUser(),
            'allows_multiple' => $this->allowsMultiple(),
            'requires_expiry_date' => $this->requiresExpiryDate(),
            'requires_document_number' => $this->requiresDocumentNumber(),
            'allowed_extensions' => $this->allowedExtensionsList(),
            'allowed_mime_types' => $this->allowedMimeTypesList(),
            'max_file_size_kb' => $this->max_file_size_kb,
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function catalogForApi(bool $candidatesOnly = false): array
    {
        return self::query()
            ->when($candidatesOnly, fn (Builder $query) => $query->where('visible_to_candidates', true))
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('label')
            ->get()
            ->map(static fn (self $type): array => $type->toCatalogEntry())
            ->all();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
