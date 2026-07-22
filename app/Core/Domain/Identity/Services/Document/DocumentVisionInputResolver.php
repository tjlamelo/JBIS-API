<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Services\Document;

use App\Core\Domain\Identity\Exceptions\Document\DocumentStorageException;
use Illuminate\Support\Facades\Log;

/**
 * Résout l'entrée image pour les modèles vision (base64 ou URL publique).
 */
final class DocumentVisionInputResolver
{
    public function __construct(
        private readonly DocumentStorageService $documentStorage,
        private readonly DocumentVisionImageEncoder $visionImageEncoder,
    ) {}

    public function fromStoragePath(string $relativePath, ?string $mimeType = null): string
    {
        return $this->resolve(
            relativePath: $relativePath,
            mimeType: $mimeType,
            absolutePath: null,
        );
    }

    public function fromAbsolutePath(string $absolutePath, string $mimeType = 'image/jpeg'): string
    {
        return $this->resolve(
            relativePath: null,
            mimeType: $mimeType,
            absolutePath: $absolutePath,
        );
    }

    private function resolve(?string $relativePath, ?string $mimeType, ?string $absolutePath): string
    {
        $mode = strtolower((string) config('ai.document_extraction.vision_input', 'base64'));

        if ($mode === 'url' && $relativePath !== null && $relativePath !== '') {
            $publicUrl = $this->documentStorage->publicUrl($relativePath);
            if ($publicUrl !== null && $publicUrl !== '' && $this->isPubliclyReachableUrl($publicUrl)) {
                Log::info('[document_extraction] Vision via URL publique', [
                    'file_path' => $relativePath,
                    'public_url' => $publicUrl,
                ]);

                return $publicUrl;
            }

            Log::warning('[document_extraction] Mode URL demandé mais URL non joignable, repli base64', [
                'file_path' => $relativePath,
                'public_url' => $publicUrl,
            ]);
        }

        if ($relativePath !== null && $relativePath !== '' && $this->documentStorage->exists($relativePath)) {
            return $this->documentStorage->visionDataUrl($relativePath, $mimeType);
        }

        if ($absolutePath !== null && is_file($absolutePath)) {
            return $this->absoluteVisionDataUrl($absolutePath, $mimeType);
        }

        if ($relativePath !== null && $relativePath !== '') {
            $publicUrl = $this->documentStorage->publicUrl($relativePath);
            if ($publicUrl !== null && $publicUrl !== '') {
                return $publicUrl;
            }
        }

        throw new DocumentStorageException('Impossible de préparer l\'entrée vision du document.');
    }

    private function absoluteVisionDataUrl(string $absolutePath, string $mimeType): string
    {
        $binary = file_get_contents($absolutePath);
        if ($binary === false || $binary === '') {
            throw new DocumentStorageException(sprintf('Fichier image vide : %s', $absolutePath));
        }

        $detectedMime = mime_content_type($absolutePath);
        $mime = is_string($detectedMime) && str_starts_with($detectedMime, 'image/')
            ? $detectedMime
            : $mimeType;

        return $this->visionImageEncoder->toDataUrl($binary, $mime);
    }

    private function isPubliclyReachableUrl(string $url): bool
    {
        $host = parse_url($url, PHP_URL_HOST);
        if (! is_string($host) || $host === '') {
            return false;
        }

        $host = strtolower($host);

        return ! in_array($host, ['localhost', '127.0.0.1', '::1'], true)
            && ! str_ends_with($host, '.local')
            && ! str_ends_with($host, '.test');
    }
}
