<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Media\Support;

/**
 * Résout dynamiquement, à la lecture, les URLs d'un média stocké en
 * mode miroir (Cloudinary + assets locaux).
 *
 * Contrat :
 *   - `primary()`     → URL prioritaire (Cloudinary si activé & dispo, sinon local).
 *   - `fallback()`    → URL de secours (toujours le miroir local), pour que le
 *                       front puisse basculer côté navigateur via `<img onError>`.
 *   - `all()`         → couple ['url' => primary, 'fallback_url' => fallback] ou null.
 *
 * Cette classe est pure (pas d'I/O, pas de DB) — elle s'appuie uniquement
 * sur le tableau `*_media` déjà stocké au moment de l'upload. Le fichier
 * est mirroré sur les deux destinations par `StoreMediaAction`, donc les
 * deux URLs pointent sur la **même image** sous deux noms de domaine.
 */
final class MediaUrlResolver
{
    public function primary(?array $media): ?string
    {
        if ($media === null) {
            return null;
        }

        if ($this->cloudinaryEnabled()) {
            $cloudUrl = $this->cloudinaryUrl($media);
            if ($cloudUrl !== null) {
                return $cloudUrl;
            }
        }

        $local = $this->localUrl($media);
        if ($local !== null) {
            return $local;
        }

        $stored = isset($media['public_url']) ? trim((string) $media['public_url']) : '';

        return $stored !== '' ? $stored : null;
    }

    public function fallback(?array $media): ?string
    {
        return $this->localUrl($media);
    }

    /**
     * @return array{url:string, fallback_url:?string}|null
     */
    public function all(?array $media): ?array
    {
        $primary = $this->primary($media);
        if ($primary === null) {
            return null;
        }

        $fallback = $this->fallback($media);
        // Si la primaire est déjà l'URL locale, pas de fallback à exposer.
        if ($fallback === $primary) {
            $fallback = null;
        }

        return [
            'url' => $primary,
            'fallback_url' => $fallback,
        ];
    }

    private function cloudinaryEnabled(): bool
    {
        return (bool) config('media.cloudinary.enabled', true);
    }

    private function cloudinaryUrl(array $media): ?string
    {
        $cloudinaryId = isset($media['cloudinary_id']) ? trim((string) $media['cloudinary_id']) : '';
        if ($cloudinaryId === '') {
            return null;
        }

        // 1) URL pré-calculée au moment de l'upload (cas nominal)
        $stored = isset($media['public_url']) ? trim((string) $media['public_url']) : '';
        if ($stored !== '' && str_contains($stored, 'cloudinary.com')) {
            return $stored;
        }

        // 2) Reconstruction par défaut si seul l'identifiant a été persisté
        $cloudName = (string) config('cloudinary.cloud_name', '');
        if ($cloudName === '') {
            return null;
        }

        return sprintf('https://res.cloudinary.com/%s/image/upload/%s', $cloudName, $cloudinaryId);
    }

    private function localUrl(array $media): ?string
    {
        $optimized = isset($media['local_optimized_path']) ? trim((string) $media['local_optimized_path']) : '';
        $raw = isset($media['local_raw_path']) ? trim((string) $media['local_raw_path']) : '';
        $path = $optimized !== '' ? $optimized : $raw;
        if ($path === '') {
            return null;
        }

        $base = rtrim((string) config('media.local.base_url', config('filesystems.disks.jbis_assets.url', '')), '/');
        if ($base === '') {
            return null;
        }

        return $base.'/'.ltrim($path, '/');
    }
}
