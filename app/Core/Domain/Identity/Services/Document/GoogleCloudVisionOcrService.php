<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Services\Document;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * OCR via Google Cloud Vision REST (DOCUMENT_TEXT_DETECTION) — sans SDK PHP.
 */
final class GoogleCloudVisionOcrService
{
    private const VISION_ENDPOINT = 'https://vision.googleapis.com/v1/images:annotate';

    private const VISION_SCOPE = 'https://www.googleapis.com/auth/cloud-vision';

    private ?string $accessToken = null;

    /** @var array<string, mixed>|null */
    private ?array $credentials = null;

    private ?string $lastError = null;

    public function isEnabled(): bool
    {
        if (! (bool) config('ai.document_extraction.ocr.enabled', false)) {
            return false;
        }

        return $this->resolveCredentialsArray() !== null;
    }

    public function lastError(): ?string
    {
        return $this->lastError;
    }

    /**
     * @param  list<string>  $absoluteImagePaths
     */
    public function extractFromImagePaths(array $absoluteImagePaths): string
    {
        $this->lastError = null;

        if (! $this->isEnabled()) {
            $this->lastError = 'OCR désactivé ou compte de service Google introuvable (GOOGLE_SERVICE_ACCOUNT_JSON).';

            return '';
        }

        if ($absoluteImagePaths === []) {
            $this->lastError = 'Aucune image de page PDF à OCR.';

            return '';
        }

        try {
            $credentials = $this->resolveCredentialsArray();
            if ($credentials === null) {
                $this->lastError = 'Compte de service Google invalide ou manquant.';

                return '';
            }

            $token = $this->accessToken($credentials);
            $requests = [];

            foreach ($absoluteImagePaths as $absolutePath) {
                $content = @file_get_contents($absolutePath);
                if ($content === false || $content === '') {
                    continue;
                }

                $requests[] = [
                    'image' => ['content' => base64_encode($content)],
                    'features' => [['type' => 'DOCUMENT_TEXT_DETECTION']],
                ];
            }

            if ($requests === []) {
                $this->lastError = 'Impossible de lire les images PDF pour OCR.';

                return '';
            }

            $response = Http::timeout(90)
                ->withToken($token)
                ->acceptJson()
                ->post(self::VISION_ENDPOINT, ['requests' => $requests]);

            if (! $response->successful()) {
                $this->lastError = sprintf(
                    'OCR Vision HTTP %d : %s',
                    $response->status(),
                    mb_substr((string) $response->body(), 0, 300),
                );
                Log::warning('[document_extraction] OCR Vision HTTP erreur', [
                    'status' => $response->status(),
                    'body' => mb_substr((string) $response->body(), 0, 500),
                ]);

                return '';
            }

            $payload = $response->json();
            $pages = [];
            $pageErrors = [];

            foreach ((array) ($payload['responses'] ?? []) as $index => $annotateResponse) {
                if (! is_array($annotateResponse)) {
                    continue;
                }

                if (isset($annotateResponse['error']['message'])) {
                    $pageErrors[] = sprintf('page %d: %s', $index + 1, $annotateResponse['error']['message']);
                    Log::warning('[document_extraction] OCR Vision erreur page', [
                        'page' => $index + 1,
                        'message' => $annotateResponse['error']['message'],
                    ]);

                    continue;
                }

                $text = trim((string) ($annotateResponse['fullTextAnnotation']['text'] ?? ''));
                if ($text !== '') {
                    $pages[] = sprintf("=== PAGE %d ===\n%s", $index + 1, $text);
                }
            }

            $joined = implode("\n\n", $pages);

            if ($joined === '') {
                $this->lastError = $pageErrors !== []
                    ? 'OCR Vision sans texte : '.implode(' | ', $pageErrors)
                    : 'OCR Vision a renvoyé un texte vide (API Cloud Vision activée sur le projet ?).';
            }

            Log::info('[document_extraction] OCR Vision terminé', [
                'pages' => count($pages),
                'char_count' => mb_strlen($joined),
                'error' => $this->lastError,
            ]);

            return $joined;
        } catch (\Throwable $exception) {
            $this->lastError = $exception->getMessage();
            Log::warning('[document_extraction] OCR Vision indisponible', [
                'message' => $exception->getMessage(),
            ]);

            return '';
        }
    }

    /**
     * @param  array<string, mixed>  $credentials
     */
    private function accessToken(array $credentials): string
    {
        if ($this->accessToken !== null && $this->credentials === $credentials) {
            return $this->accessToken;
        }

        $clientEmail = (string) ($credentials['client_email'] ?? '');
        $privateKey = $this->normalizePrivateKey((string) ($credentials['private_key'] ?? ''));
        $tokenUri = (string) ($credentials['token_uri'] ?? 'https://oauth2.googleapis.com/token');

        if ($clientEmail === '' || $privateKey === '') {
            throw new \RuntimeException('Compte de service Google incomplet (client_email / private_key).');
        }

        $now = time();
        $header = $this->base64UrlEncode(json_encode(['alg' => 'RS256', 'typ' => 'JWT'], JSON_THROW_ON_ERROR));
        $claims = $this->base64UrlEncode(json_encode([
            'iss' => $clientEmail,
            'scope' => self::VISION_SCOPE,
            'aud' => $tokenUri,
            'iat' => $now,
            'exp' => $now + 3600,
        ], JSON_THROW_ON_ERROR));

        $signature = '';
        $signed = openssl_sign("{$header}.{$claims}", $signature, $privateKey, OPENSSL_ALGO_SHA256);
        if ($signed !== true) {
            throw new \RuntimeException(
                'Impossible de signer le JWT Google (private_key invalide dans GOOGLE_SERVICE_ACCOUNT_JSON).'
            );
        }

        $jwt = "{$header}.{$claims}.".$this->base64UrlEncode($signature);

        $response = Http::asForm()->timeout(30)->post($tokenUri, [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt,
        ]);

        if (! $response->successful()) {
            throw new \RuntimeException(
                'Échec OAuth Google Vision (vérifiez la clé et Cloud Vision API) : '
                .mb_substr((string) $response->body(), 0, 300)
            );
        }

        $token = (string) ($response->json('access_token') ?? '');
        if ($token === '') {
            throw new \RuntimeException('Token OAuth Google Vision vide.');
        }

        $this->credentials = $credentials;
        $this->accessToken = $token;

        return $token;
    }

    private function normalizePrivateKey(string $privateKey): string
    {
        $key = str_replace(["\r\n", "\r"], "\n", $privateKey);

        // Dotenv / JSON parfois conserve les séquences littérales \n.
        if (! str_contains($key, "\n") && str_contains($key, '\\n')) {
            $key = str_replace('\\n', "\n", $key);
        }

        return $key;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function resolveCredentialsArray(): ?array
    {
        $inlineJson = config('ai.document_extraction.ocr.service_account_json');
        if (is_string($inlineJson) && trim($inlineJson) !== '') {
            $decoded = json_decode($inlineJson, true);
            if (is_array($decoded)) {
                if (isset($decoded['private_key']) && is_string($decoded['private_key'])) {
                    $decoded['private_key'] = $this->normalizePrivateKey($decoded['private_key']);
                }

                return $decoded;
            }

            Log::warning('[document_extraction] GOOGLE_SERVICE_ACCOUNT_JSON invalide');
            $this->lastError = 'GOOGLE_SERVICE_ACCOUNT_JSON n\'est pas un JSON valide.';
        }

        $path = $this->resolveCredentialsPath();
        if ($path === null || ! is_readable($path)) {
            return null;
        }

        $contents = file_get_contents($path);
        if ($contents === false || trim($contents) === '') {
            return null;
        }

        $decoded = json_decode($contents, true);
        if (! is_array($decoded)) {
            return null;
        }

        if (isset($decoded['private_key']) && is_string($decoded['private_key'])) {
            $decoded['private_key'] = $this->normalizePrivateKey($decoded['private_key']);
        }

        return $decoded;
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

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
