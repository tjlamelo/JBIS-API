<?php

declare(strict_types=1);

namespace App\Core\Domain\Analytics\Services;

use Illuminate\Support\Facades\Http;

class Ga4Client
{
    private const SCOPE = 'https://www.googleapis.com/auth/analytics.readonly';

    private static ?string $cachedAccessToken = null;

    private static int $cachedAccessTokenExpiresAt = 0;

    public function __construct(
        private readonly int $propertyId,
        private readonly array $serviceAccount,
    ) {}

    /**
     * @param  array{startDate:string,endDate:string}  $dateRange
     * @param  string[]  $metrics
     * @param  string[]  $dimensions
     */
    public function runReport(array $dateRange, array $metrics, array $dimensions = [], int $limit = 100): array
    {
        $url = sprintf('https://analyticsdata.googleapis.com/v1beta/properties/%d:runReport', $this->propertyId);

        $payload = [
            'dateRanges' => [
                [
                    'startDate' => $dateRange['startDate'],
                    'endDate' => $dateRange['endDate'],
                ],
            ],
            'metrics' => array_map(fn (string $name) => ['name' => $name], $metrics),
            'dimensions' => array_map(fn (string $name) => ['name' => $name], $dimensions),
            'limit' => $limit,
        ];

        $response = Http::withToken($this->getAccessToken())
            ->timeout(20)
            ->retry(2, 200)
            ->acceptJson()
            ->post($url, $payload);

        if (! $response->successful()) {
            $status = $response->status();
            $body = $response->json();
            throw new \RuntimeException(
                'GA4 runReport failed: '.$status.' '.json_encode($body).
                ' (propertyId='.$this->propertyId.')'
            );
        }

        return (array) $response->json();
    }

    private function getAccessToken(): string
    {
        $now = time();
        if (self::$cachedAccessToken && self::$cachedAccessTokenExpiresAt > ($now + 30)) {
            return self::$cachedAccessToken;
        }

        $tokenUri = $this->serviceAccount['token_uri'] ?? 'https://oauth2.googleapis.com/token';

        $iat = $now;
        $exp = $now + 3600;

        $assertion = $this->buildJwtAssertion([
            'iss' => (string) $this->serviceAccount['client_email'],
            'scope' => self::SCOPE,
            'aud' => (string) $tokenUri,
            'iat' => $iat,
            'exp' => $exp,
        ]);

        $response = Http::asForm()->post($tokenUri, [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $assertion,
        ]);

        if (! $response->successful()) {
            $status = $response->status();
            $body = $response->json();
            throw new \RuntimeException(
                'GA4 OAuth token request failed: '.$status.' '.json_encode($body).
                ' (client_email='.(string) ($this->serviceAccount['client_email'] ?? '').')'
            );
        }

        $data = (array) $response->json();
        $accessToken = (string) ($data['access_token'] ?? '');
        $expiresIn = (int) ($data['expires_in'] ?? 3600);

        if ($accessToken === '') {
            throw new \RuntimeException('GA4 OAuth token response missing access_token');
        }

        self::$cachedAccessToken = $accessToken;
        self::$cachedAccessTokenExpiresAt = $now + $expiresIn;

        return $accessToken;
    }

    private function buildJwtAssertion(array $claims): string
    {
        $header = ['alg' => 'RS256', 'typ' => 'JWT'];

        $segments = [
            $this->base64UrlEncode(json_encode($header, JSON_UNESCAPED_SLASHES)),
            $this->base64UrlEncode(json_encode($claims, JSON_UNESCAPED_SLASHES)),
        ];

        $signingInput = implode('.', $segments);

        $privateKey = (string) ($this->serviceAccount['private_key'] ?? '');
        if ($privateKey === '') {
            throw new \RuntimeException('GA4 service account private_key missing');
        }

        $signature = '';
        $ok = openssl_sign($signingInput, $signature, $privateKey, OPENSSL_ALGO_SHA256);
        if (! $ok) {
            throw new \RuntimeException('GA4 JWT signing failed');
        }

        $segments[] = $this->base64UrlEncode($signature);

        return implode('.', $segments);
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
