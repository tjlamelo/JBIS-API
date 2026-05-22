<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Support;

use App\Core\Domain\Shared\Media\Support\MediaUrlResolver;

final class ProfilePicturesSerializer
{
    public function __construct(
        private readonly MediaUrlResolver $mediaUrlResolver,
    ) {}

    /**
     * @param  list<mixed>|null  $pictures
     * @return list<string>
     */
    public function toUrls(?array $pictures): array
    {
        if ($pictures === null || $pictures === []) {
            return [];
        }

        $urls = [];

        foreach ($pictures as $item) {
            $url = $this->resolveItemUrl($item);
            if ($url !== null) {
                $urls[] = $url;
            }
        }

        return array_values(array_slice($urls, 0, 3));
    }

    /**
     * @param  list<mixed>  $pictures
     * @return list<array<string, mixed>>
     */
    public function normalizeForStorage(array $pictures): array
    {
        $normalized = [];

        foreach ($pictures as $item) {
            $entry = $this->normalizeStorageItem($item);
            if ($entry !== null) {
                $normalized[] = $entry;
            }
        }

        return array_values(array_slice($normalized, 0, 3));
    }

    private function resolveItemUrl(mixed $item): ?string
    {
        if (is_string($item)) {
            return $this->resolveStringUrl($item);
        }

        if (! is_array($item)) {
            return null;
        }

        $resolved = $this->mediaUrlResolver->primary($item);
        if ($resolved !== null && $resolved !== '') {
            return $resolved;
        }

        $publicUrl = isset($item['public_url']) ? trim((string) $item['public_url']) : '';

        return $this->resolveStringUrl($publicUrl);
    }

    private function resolveStringUrl(string $value): ?string
    {
        $trimmed = trim($value);
        if ($trimmed === '' || str_starts_with($trimmed, 'blob:')) {
            return null;
        }

        if (filter_var($trimmed, FILTER_VALIDATE_URL)) {
            return $trimmed;
        }

        if (str_starts_with($trimmed, '/')) {
            $base = rtrim((string) config('media.local.base_url', config('filesystems.disks.jbis_assets.url', '')), '/');
            if ($base !== '') {
                return $base.'/'.ltrim($trimmed, '/');
            }
        }

        return null;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function normalizeStorageItem(mixed $item): ?array
    {
        if (is_string($item)) {
            $url = $this->resolveStringUrl($item);
            if ($url === null) {
                return null;
            }

            return ['public_url' => $url];
        }

        if (! is_array($item)) {
            return null;
        }

        $keys = [
            'file_name',
            'local_optimized_path',
            'local_raw_path',
            'cloudinary_id',
            'public_url',
            'is_primary',
            'uploaded_at',
        ];

        $entry = [];
        foreach ($keys as $key) {
            if (array_key_exists($key, $item) && $item[$key] !== null && $item[$key] !== '') {
                $entry[$key] = $item[$key];
            }
        }

        if ($entry === [] || $this->resolveItemUrl($entry) === null) {
            return null;
        }

        return $entry;
    }
}
