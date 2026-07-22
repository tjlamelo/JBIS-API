<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Services\Document;

use Google\Cloud\Vision\V1\AnnotateImageRequest;
use Google\Cloud\Vision\V1\BatchAnnotateImagesRequest;
use Google\Cloud\Vision\V1\Client\ImageAnnotatorClient;
use Google\Cloud\Vision\V1\Feature;
use Google\Cloud\Vision\V1\Feature\Type;
use Google\Cloud\Vision\V1\Image;
use Illuminate\Support\Facades\Log;

/**
 * OCR via Google Cloud Vision (DOCUMENT_TEXT_DETECTION) sur les pages PDF rendues en JPEG.
 */
final class GoogleCloudVisionOcrService
{
    private ?ImageAnnotatorClient $client = null;

    public function isEnabled(): bool
    {
        if (! (bool) config('ai.document_extraction.ocr.enabled', false)) {
            return false;
        }

        return $this->resolveCredentials() !== null;
    }

    /**
     * @param  list<string>  $absoluteImagePaths
     */
    public function extractFromImagePaths(array $absoluteImagePaths): string
    {
        if (! $this->isEnabled() || $absoluteImagePaths === []) {
            return '';
        }

        $requests = [];
        foreach ($absoluteImagePaths as $absolutePath) {
            $content = @file_get_contents($absolutePath);
            if ($content === false || $content === '') {
                continue;
            }

            $image = (new Image())->setContent($content);
            $feature = (new Feature())->setType(Type::DOCUMENT_TEXT_DETECTION);
            $requests[] = (new AnnotateImageRequest())
                ->setImage($image)
                ->setFeatures([$feature]);
        }

        if ($requests === []) {
            return '';
        }

        try {
            $response = $this->client()->batchAnnotateImages(
                (new BatchAnnotateImagesRequest())->setRequests($requests),
            );

            $pages = [];
            foreach ($response->getResponses() as $index => $annotateResponse) {
                if ($annotateResponse->getError() !== null) {
                    Log::warning('[document_extraction] OCR Vision erreur page', [
                        'page' => $index + 1,
                        'message' => $annotateResponse->getError()?->getMessage(),
                    ]);

                    continue;
                }

                $text = trim((string) ($annotateResponse->getFullTextAnnotation()?->getText() ?? ''));
                if ($text !== '') {
                    $pages[] = sprintf("=== PAGE %d ===\n%s", $index + 1, $text);
                }
            }

            $joined = implode("\n\n", $pages);

            Log::info('[document_extraction] OCR Vision terminé', [
                'pages' => count($pages),
                'char_count' => mb_strlen($joined),
            ]);

            return $joined;
        } catch (\Throwable $exception) {
            Log::warning('[document_extraction] OCR Vision indisponible', [
                'message' => $exception->getMessage(),
            ]);

            return '';
        }
    }

    private function client(): ImageAnnotatorClient
    {
        if ($this->client instanceof ImageAnnotatorClient) {
            return $this->client;
        }

        $credentials = $this->resolveCredentials();
        $options = $credentials !== null ? ['credentials' => $credentials] : [];

        $this->client = new ImageAnnotatorClient($options);

        return $this->client;
    }

    /**
     * @return array<string, mixed>|string|null
     */
    private function resolveCredentials(): array|string|null
    {
        $inlineJson = config('ai.document_extraction.ocr.service_account_json');
        if (is_string($inlineJson) && trim($inlineJson) !== '') {
            $decoded = json_decode($inlineJson, true);
            if (is_array($decoded)) {
                return $decoded;
            }

            Log::warning('[document_extraction] GOOGLE_SERVICE_ACCOUNT_JSON invalide');
        }

        $path = $this->resolveCredentialsPath();
        if ($path !== null && is_readable($path)) {
            return $path;
        }

        return null;
    }

    private function resolveCredentialsPath(): ?string
    {
        $configured = config('ai.document_extraction.ocr.credentials');
        $path = is_string($configured) && $configured !== ''
            ? $configured
            : (string) env('GOOGLE_APPLICATION_CREDENTIALS', '');

        if ($path === '') {
            return null;
        }

        if (! str_starts_with($path, DIRECTORY_SEPARATOR) && ! preg_match('/^[A-Za-z]:[\\\\\\/]/', $path)) {
            $path = base_path($path);
        }

        return $path;
    }

    public function __destruct()
    {
        if ($this->client instanceof ImageAnnotatorClient) {
            $this->client->close();
        }
    }
}
