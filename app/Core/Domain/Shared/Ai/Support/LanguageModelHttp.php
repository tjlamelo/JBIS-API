<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Ai\Support;

use App\Core\Domain\Shared\Ai\Exceptions\LanguageModelRateLimitedException;
use App\Core\Domain\Shared\Ai\Exceptions\LanguageModelTransportException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * POST JSON avec retries sur 429 / 503 / erreurs réseau (RPM Gemini/Groq).
 */
final class LanguageModelHttp
{
    /**
     * @param  array<string, mixed>  $payload
     * @param  array<string, string>  $headers
     * @param  array<string, string>  $query
     */
    public static function postJson(
        string $url,
        array $payload,
        string $provider,
        int $timeout,
        array $headers = [],
        array $query = [],
        ?string $bearerToken = null,
    ): Response {
        $maxAttempts = max(1, (int) config('ai.http.max_attempts', 4));
        $baseDelayMs = max(100, (int) config('ai.http.base_delay_ms', 1000));
        $lastResponse = null;

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            try {
                $pending = Http::timeout($timeout)->acceptJson();

                foreach ($headers as $name => $value) {
                    $pending = $pending->withHeaders([$name => $value]);
                }

                if ($bearerToken !== null && $bearerToken !== '') {
                    $pending = $pending->withToken($bearerToken);
                }

                if ($query !== []) {
                    $pending = $pending->withQueryParameters($query);
                }

                $response = $pending->post($url, $payload);
            } catch (\Throwable $exception) {
                if ($attempt >= $maxAttempts) {
                    throw new LanguageModelTransportException(
                        sprintf('%s transport : %s', ucfirst($provider), $exception->getMessage()),
                        0,
                        $exception,
                    );
                }

                $delayMs = self::jitteredDelayMs($baseDelayMs, $attempt);
                Log::warning('[ai.http] Erreur réseau, retry', [
                    'provider' => $provider,
                    'attempt' => $attempt,
                    'delay_ms' => $delayMs,
                    'message' => $exception->getMessage(),
                ]);
                usleep($delayMs * 1000);

                continue;
            }

            $lastResponse = $response;

            if ($response->successful()) {
                return $response;
            }

            $status = $response->status();
            if (! self::isRetryableStatus($status) || $attempt >= $maxAttempts) {
                return $response;
            }

            $retryAfter = self::resolveRetryAfterSeconds($response, $baseDelayMs, $attempt);
            Log::warning('[ai.http] Rate limit / indisponible, retry', [
                'provider' => $provider,
                'status' => $status,
                'attempt' => $attempt,
                'retry_after_seconds' => $retryAfter,
            ]);
            sleep(max(1, $retryAfter));
        }

        return $lastResponse ?? Http::response(['error' => ['message' => 'Aucune réponse AI']], 503);
    }

    public static function throwIfRateLimited(Response $response, string $provider): void
    {
        if (! self::isRetryableStatus($response->status())) {
            return;
        }

        $retryAfter = self::resolveRetryAfterSeconds(
            $response,
            (int) config('ai.http.base_delay_ms', 1000),
            1,
        );

        $message = sprintf(
            '%s HTTP %d : quota / rate limit atteint%s.',
            ucfirst($provider),
            $response->status(),
            $retryAfter > 0 ? sprintf(' (retry dans ~%ds)', $retryAfter) : '',
        );

        throw new LanguageModelRateLimitedException($message, $retryAfter);
    }

    public static function isRetryableStatus(int $status): bool
    {
        return in_array($status, [408, 429, 500, 502, 503, 504], true);
    }

    public static function resolveRetryAfterSeconds(Response $response, int $baseDelayMs, int $attempt): int
    {
        $header = $response->header('Retry-After');
        if (is_string($header) && is_numeric(trim($header))) {
            return max(1, (int) $header);
        }

        /** @var array<string, mixed>|null $json */
        $json = $response->json();
        $details = $json['error']['details'] ?? null;
        if (is_array($details)) {
            foreach ($details as $detail) {
                if (! is_array($detail)) {
                    continue;
                }
                $retryDelay = $detail['retryDelay'] ?? null;
                if (is_string($retryDelay) && preg_match('/^(\d+(?:\.\d+)?)s$/', $retryDelay, $m) === 1) {
                    return max(1, (int) ceil((float) $m[1]));
                }
            }
        }

        return max(1, (int) ceil(self::jitteredDelayMs($baseDelayMs, $attempt) / 1000));
    }

    private static function jitteredDelayMs(int $baseDelayMs, int $attempt): int
    {
        $exp = $baseDelayMs * (2 ** max(0, $attempt - 1));
        $jitter = random_int(0, (int) max(1, $exp * 0.25));

        return (int) min(60_000, $exp + $jitter);
    }
}
