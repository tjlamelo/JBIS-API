<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Export\Providers;

use App\Core\Domain\Shared\Export\Drivers\CsvExportDriver;
use App\Core\Domain\Shared\Export\Drivers\PdfExportDriver;
use App\Core\Domain\Shared\Export\Drivers\XlsxExportDriver;
use App\Core\Domain\Shared\Export\Registry\ExportDriverRegistry;
use App\Core\Domain\Shared\Export\Registry\ExportSourceRegistry;
use App\Core\Domain\Shared\Export\Sources\ApplicationExportSource;
use App\Core\Domain\Shared\Export\Sources\OfferExportSource;
use App\Core\Domain\Shared\Export\Sources\ProgramExportSource;
use App\Core\Domain\Shared\Export\Sources\UserDocumentExportSource;
use App\Core\Domain\Shared\Export\Sources\UserExportSource;
use App\Core\Domain\Shared\Export\Support\FieldPathResolver;
use App\Core\Domain\Shared\Export\Support\FilenameBuilder;
use App\Core\Domain\Shared\Export\Support\HtmlTemplateRenderer;
use App\Core\Domain\Shared\Export\Support\ValueFormatter;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\ServiceProvider;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

/**
 * Provider du module Export.
 *
 *  - Singletons : registries, helpers, services
 *  - Sources : enregistrées au boot (extensibles : ajoutez votre source ici
 *    ou via `app(ExportSourceRegistry::class)->register(...)`).
 *  - Drivers : CSV toujours actif ; XLSX/PDF activés si la dépendance est présente.
 *  - Vues : namespace « exports » résolu vers le dossier Templates du module.
 */
final class ExportServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(FieldPathResolver::class);
        $this->app->singleton(ValueFormatter::class);
        $this->app->singleton(FilenameBuilder::class);
        $this->app->singleton(HtmlTemplateRenderer::class);

        $this->app->singleton(ExportSourceRegistry::class, function (): ExportSourceRegistry {
            $registry = new ExportSourceRegistry;
            $registry->register(new UserExportSource);
            $registry->register(new ApplicationExportSource);
            $registry->register(new OfferExportSource);
            $registry->register(new ProgramExportSource);
            $registry->register(new UserDocumentExportSource);

            return $registry;
        });

        $this->app->singleton(ExportDriverRegistry::class, function ($app): ExportDriverRegistry {
            $registry = new ExportDriverRegistry;

            // CSV : toujours disponible (aucune dépendance)
            $registry->register($app->make(CsvExportDriver::class));

            // XLSX : seulement si phpoffice/phpspreadsheet est installé
            if (class_exists(Spreadsheet::class)) {
                $registry->register($app->make(XlsxExportDriver::class));
            }

            // PDF : seulement si barryvdh/laravel-dompdf est installé
            if (class_exists(Pdf::class)) {
                $registry->register($app->make(PdfExportDriver::class));
            }

            return $registry;
        });
    }

    public function boot(): void
    {
        $this->loadViewsFrom(
            __DIR__.'/../Templates',
            'exports'
        );
    }
}
