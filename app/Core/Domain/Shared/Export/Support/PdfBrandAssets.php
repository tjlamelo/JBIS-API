<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Export\Support;

/**
 * Images de marque JBIS pour les PDF (en-tête, pied, filigrane).
 * Utilise des data-URI pour le service screenshot (Puppeteer).
 */
final class PdfBrandAssets
{
    /** @var array{header: string, footer: string, watermark: string}|null */
    private ?array $cache = null;

    public function headerUri(): string
    {
        return $this->resolve('header');
    }

    public function footerUri(): string
    {
        return $this->resolve('footer');
    }

    public function watermarkUri(): string
    {
        return $this->resolve('watermark');
    }

    private function resolve(string $key): string
    {
        return $this->all()[$key] ?? '';
    }

    /**
     * @return array{header: string, footer: string, watermark: string}
     */
    private function all(): array
    {
        if ($this->cache !== null) {
            return $this->cache;
        }

        /** @var array{header?: string, footer?: string, watermark?: string} $brand */
        $brand = config('export-pdf.brand', []);

        $this->cache = [
            'header' => $this->toDataUri((string) ($brand['header'] ?? 'assets/img/entete.png')),
            'footer' => $this->toDataUri((string) ($brand['footer'] ?? 'assets/img/pied.png')),
            'watermark' => $this->toDataUri((string) ($brand['watermark'] ?? 'assets/img/logo-jbis.png')),
        ];

        return $this->cache;
    }

    private function toDataUri(string $relativePath): string
    {
        $path = public_path(ltrim($relativePath, '/'));
        if (! is_file($path)) {
            return '';
        }

        $mime = mime_content_type($path) ?: 'image/png';
        $data = base64_encode((string) file_get_contents($path));

        return "data:{$mime};base64,{$data}";
    }
}
