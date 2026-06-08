<?php

declare(strict_types=1);

namespace App\Core\Domain\Recruiter\Support;

final class RecruiterHostBuilder
{
    public function portalHost(string $slug): string
    {
        $base = (string) config('services.cpanel.recruiter_base_domain', 'jbis.cm');
        $prefix = (string) config('services.cpanel.recruiter_portal_prefix', 'recruteur');

        return sprintf('%s.%s.%s', $slug, $prefix, $base);
    }

    public function apiHost(string $slug): string
    {
        return 'api.'.$this->portalHost($slug);
    }

    /**
     * @return array{subdomain: string, rootdomain: string}|null
     */
    public function parsePortalHost(string $fqdn): ?array
    {
        $base = strtolower((string) config('services.cpanel.recruiter_base_domain', 'jbis.cm'));
        $suffix = '.'.strtolower((string) config('services.cpanel.recruiter_portal_prefix', 'recruteur')).'.'.$base;

        if (! str_ends_with(strtolower($fqdn), $suffix)) {
            return null;
        }

        $subdomain = substr(strtolower($fqdn), 0, -strlen($suffix));
        if ($subdomain === '') {
            return null;
        }

        return [
            'subdomain' => $subdomain.'.'.config('services.cpanel.recruiter_portal_prefix', 'recruteur'),
            'rootdomain' => $base,
        ];
    }

    /**
     * @return array{subdomain: string, rootdomain: string}|null
     */
    public function parseApiHost(string $fqdn): ?array
    {
        $base = strtolower((string) config('services.cpanel.recruiter_base_domain', 'jbis.cm'));
        $suffix = '.'.strtolower((string) config('services.cpanel.recruiter_portal_prefix', 'recruteur')).'.'.$base;

        if (! str_ends_with(strtolower($fqdn), $suffix)) {
            return null;
        }

        $prefix = substr(strtolower($fqdn), 0, -strlen($suffix));
        if (! str_starts_with($prefix, 'api.')) {
            return null;
        }

        $slugPart = substr($prefix, 4);
        if ($slugPart === '') {
            return null;
        }

        return [
            'subdomain' => 'api.'.$slugPart.'.'.config('services.cpanel.recruiter_portal_prefix', 'recruteur'),
            'rootdomain' => $base,
        ];
    }
}
