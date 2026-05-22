<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Screenshot\Services;

use App\Core\Domain\Shared\Screenshot\Exceptions\ScreenshotServiceException;
use Illuminate\Support\Facades\Http;

final class ScreenshotPdfClient
{
    /**
     * @param  array{top?: int, right?: int, bottom?: int, left?: int}  $margins
     */
    public function htmlToPdf(string $html, string $paper = 'a4', array $margins = []): string
    {
        $baseUrl = (string) config('screenshot-service.url', '');
        $token = (string) config('screenshot-service.token', '');

        if ($baseUrl === '' || $token === '') {
            throw ScreenshotServiceException::notConfigured();
        }

        $endpoint = $baseUrl.'/v1/pdf/from-html';
        $timeout = (int) config('screenshot-service.timeout', 120);

        try {
            $response = Http::timeout($timeout)
                ->withToken($token)
                ->withHeaders(['X-Internal-Token' => $token])
                ->accept('application/pdf')
                ->post($endpoint, [
                    'html' => $html,
                    'paper' => $paper,
                    'margins' => $margins,
                ]);
        } catch (\Throwable $e) {
            throw ScreenshotServiceException::unreachable($e->getMessage());
        }

        if (! $response->successful()) {
            $detail = $response->json('message') ?? $response->json('error') ?? $response->body();

            throw ScreenshotServiceException::requestFailed($response->status(), is_string($detail) ? $detail : '');
        }

        $body = $response->body();
        if ($body === '') {
            throw ScreenshotServiceException::requestFailed($response->status(), 'Réponse PDF vide.');
        }

        return $body;
    }
}
