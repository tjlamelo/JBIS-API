<?php

declare(strict_types=1);

namespace App\Core\Domain\Communication\Support;

/**
 * Branding e-mails : produit applicatif MyJob Best + identité JBIS.
 */
final class MailBranding
{
    public static function productName(): string
    {
        return (string) config('branding.product_name', 'MyJob Best');
    }

    public static function companyName(): string
    {
        return (string) config('branding.company_name', 'Job Best International Services');
    }

    public static function logoUrl(): string
    {
        $configured = config('branding.logo_url');
        if (is_string($configured) && $configured !== '') {
            return $configured;
        }

        $appUrl = rtrim((string) config('app.url', ''), '/');

        return $appUrl.'/assets/img/logo-jbis.png';
    }

    /**
     * @return array{brandName: string, companyName: string, logoUrl: string}
     */
    public static function viewData(): array
    {
        return [
            'brandName' => self::productName(),
            'companyName' => self::companyName(),
            'logoUrl' => self::logoUrl(),
        ];
    }
}
