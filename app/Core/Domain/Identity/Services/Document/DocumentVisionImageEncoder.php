<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Services\Document;

/**
 * Réduit la taille des images envoyées aux modèles vision (TPM / payload).
 */
final class DocumentVisionImageEncoder
{
    public function toDataUrl(string $binary, string $mimeType): string
    {
        $mime = strtolower($mimeType);
        $encoded = $this->maybeCompress($binary, $mime);

        return sprintf('data:%s;base64,%s', $encoded['mime'], base64_encode($encoded['binary']));
    }

    /**
     * @return array{binary: string, mime: string}
     */
    private function maybeCompress(string $binary, string $mime): array
    {
        if (! extension_loaded('gd') || ! str_starts_with($mime, 'image/')) {
            return ['binary' => $binary, 'mime' => $mime];
        }

        $maxEdge = max(640, (int) config('ai.document_extraction.vision_max_edge', 1280));
        $quality = max(40, min(90, (int) config('ai.document_extraction.vision_jpeg_quality', 70)));

        $image = @imagecreatefromstring($binary);
        if ($image === false) {
            return ['binary' => $binary, 'mime' => $mime];
        }

        $width = imagesx($image);
        $height = imagesy($image);
        if ($width < 1 || $height < 1) {
            imagedestroy($image);

            return ['binary' => $binary, 'mime' => $mime];
        }

        $scale = min(1.0, $maxEdge / max($width, $height));
        $targetWidth = max(1, (int) round($width * $scale));
        $targetHeight = max(1, (int) round($height * $scale));

        if ($scale < 1.0) {
            $resized = imagecreatetruecolor($targetWidth, $targetHeight);
            if ($resized === false) {
                imagedestroy($image);

                return ['binary' => $binary, 'mime' => $mime];
            }

            imagecopyresampled($resized, $image, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);
            imagedestroy($image);
            $image = $resized;
        }

        ob_start();
        imagejpeg($image, null, $quality);
        $compressed = ob_get_clean();
        imagedestroy($image);

        if (! is_string($compressed) || $compressed === '') {
            return ['binary' => $binary, 'mime' => $mime];
        }

        return ['binary' => $compressed, 'mime' => 'image/jpeg'];
    }
}
