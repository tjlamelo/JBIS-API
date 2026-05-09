<?php

 
namespace App\Core\Domain\Shared\Media\Traits;

trait HasMedia
{
    /**
     * Récupère l'URL d'une image spécifique du JSON ou de la photo principale
     */
    public function getPictureUrl(bool $primaryOnly = true, int $index = 0): ?string
    {
        $pictures = $this->pictures ?? [];
        if (empty($pictures)) return null;

        $target = $primaryOnly 
            ? (collect($pictures)->firstWhere('is_primary', true) ?? $pictures[0])
            : ($pictures[$index] ?? null);

        if (!$target) return null;

        return $this->resolveMediaUrl($target);
    }

    /**
     * Logique de Fallback Cloudinary -> Local
     */
    private function resolveMediaUrl(array $media): string
    {
        if (!empty($media['public_url']) && is_string($media['public_url'])) {
            return $media['public_url'];
        }

        if (!empty($media['cloudinary_id'])) {
            return "https://res.cloudinary.com/" . config('cloudinary.cloud_name') . "/image/upload/" . $media['cloudinary_id'];
        }

        $localPath = $media['local_optimized_path'] ?? null;
        if (is_string($localPath) && $localPath !== '') {
            return rtrim(config('filesystems.disks.jbis_assets.url'), '/') . '/' . ltrim($localPath, '/');
        }

        return '';
    }
}