<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Pdf\Providers;

use App\Core\Domain\Shared\Pdf\Contracts\PdfProcessorInterface;
use App\Core\Domain\Shared\Pdf\Services\IlovepdfProcessor;
use App\Core\Domain\Shared\Pdf\Services\PdfDocumentService;
use App\Core\Domain\Shared\Pdf\Support\PdfSourceMaterializer;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Support\ServiceProvider;

/**
 * Provider du module PDF.
 *
 *  - Publie la config `config/ilovepdf.php`.
 *  - Lie le contrat `PdfProcessorInterface` à l'implémentation iLovePDF.
 *  - Implémentation marquée en singleton (sans état, mais chaque appel
 *    instancie une nouvelle task iLovePDF en interne).
 */
final class PdfServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            base_path('config/ilovepdf.php'),
            'ilovepdf'
        );

        $this->app->singleton(PdfProcessorInterface::class, function ($app): PdfProcessorInterface {
            /** @var Config $config */
            $config = $app->make('config');

            return new IlovepdfProcessor(
                publicKey: $config->get('ilovepdf.public_key'),
                secretKey: $config->get('ilovepdf.secret_key'),
                defaults: (array) $config->get('ilovepdf.defaults', []),
                defaultCompressionLevel: (string) $config->get('ilovepdf.compression_level', 'recommended'),
            );
        });

        $this->app->alias(PdfProcessorInterface::class, 'pdf.processor');

        $this->app->singleton(PdfSourceMaterializer::class);

        $this->app->singleton(PdfDocumentService::class, function ($app): PdfDocumentService {
            /** @var Config $config */
            $config = $app->make('config');

            return new PdfDocumentService(
                processor: $app->make(PdfProcessorInterface::class),
                materializer: $app->make(PdfSourceMaterializer::class),
                defaultDisk: (string) $config->get('ilovepdf.documents.disk', 'jbis_assets'),
                defaultFolder: (string) $config->get('ilovepdf.documents.folder', 'documents/processed'),
            );
        });

        $this->app->alias(PdfDocumentService::class, 'pdf.documents');
    }

    public function boot(): void
    {
        $this->publishes([
            base_path('config/ilovepdf.php') => config_path('ilovepdf.php'),
        ], 'ilovepdf-config');
    }

    public function provides(): array
    {
        return [
            PdfProcessorInterface::class,
            PdfDocumentService::class,
            PdfSourceMaterializer::class,
            'pdf.processor',
            'pdf.documents',
        ];
    }
}
