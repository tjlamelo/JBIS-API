<?php

declare(strict_types=1);

namespace App\Core\Domain\Analytics\Services;

class Ga4ClientFactory
{
    public function make(): Ga4Client
    {
        $propertyId = (int) (config('services.ga4.property_id') ?? 0);
        if ($propertyId <= 0) {
            throw new \RuntimeException('GA4 property_id is not configured');
        }

        $serviceAccount = $this->loadServiceAccount();

        return new Ga4Client($propertyId, $serviceAccount);
    }

    private function loadServiceAccount(): array
    {
        $json = (string) (config('services.ga4.service_account_json') ?? '');
        if ($json === '') {
            throw new \RuntimeException('GA4 service_account_json is not configured');
        }

        $decoded = json_decode($json, true);
        if (! is_array($decoded)) {
            throw new \RuntimeException('GA4 service_account_json is invalid JSON');
        }

        return $decoded;
    }
}
