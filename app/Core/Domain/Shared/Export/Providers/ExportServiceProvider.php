<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Export\Providers;

use App\Core\Domain\Shared\Export\Drivers\CsvExportDriver;
use App\Core\Domain\Shared\Export\Drivers\PdfExportDriver;
use App\Core\Domain\Shared\Export\Drivers\XlsxExportDriver;
use App\Core\Domain\Shared\Export\Registry\ExportDriverRegistry;
use App\Core\Domain\Shared\Export\Registry\ExportSourceRegistry;
use App\Core\Domain\Shared\Export\Sources\ApplicationExportSource;
use App\Core\Domain\Shared\Export\Sources\AppointmentExportSource;
use App\Core\Domain\Shared\Export\Sources\CertificationExportSource;
use App\Core\Domain\Shared\Export\Sources\EducationExportSource;
use App\Core\Domain\Shared\Export\Sources\ExperienceExportSource;
use App\Core\Domain\Shared\Export\Sources\InterestExportSource;
use App\Core\Domain\Shared\Export\Sources\InternshipExportSource;
use App\Core\Domain\Shared\Export\Sources\InterviewExportSource;
use App\Core\Domain\Shared\Export\Sources\LanguageExportSource;
use App\Core\Domain\Shared\Export\Sources\OfferExportSource;
use App\Core\Domain\Shared\Export\Sources\ProgramExportSource;
use App\Core\Domain\Shared\Export\Sources\UserConsentExportSource;
use App\Core\Domain\Shared\Export\Sources\UserDocumentExportSource;
use App\Core\Domain\Shared\Export\Sources\UserExportSource;
use App\Core\Domain\Shared\Export\Sources\UserNoteExportSource;
use App\Core\Domain\Shared\Export\Sources\UserPreferredCountryExportSource;
use App\Core\Domain\Shared\Export\Sources\UserSkillExportSource;
use App\Core\Domain\Shared\Export\Sources\UserTrainingExportSource;
use App\Core\Domain\Shared\Export\Sources\UserVisaHistoryExportSource;
use App\Core\Domain\Shared\Export\Support\FieldPathResolver;
use App\Core\Domain\Shared\Export\Support\FilenameBuilder;
use App\Core\Domain\Shared\Export\Support\HtmlTemplateRenderer;
use App\Core\Domain\Shared\Export\Support\PdfBrandAssets;
use App\Core\Domain\Shared\Export\Support\PdfLayoutMetrics;
use App\Core\Domain\Shared\Export\Support\ValueFormatter;
use Illuminate\Support\Facades\View;
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
        $this->app->singleton(PdfBrandAssets::class);

        $this->app->singleton(ExportSourceRegistry::class, function ($app): ExportSourceRegistry {
            $registry = new ExportSourceRegistry;
            $registry->register($app->make(UserExportSource::class));
            $registry->register(new ApplicationExportSource);
            $registry->register(new OfferExportSource);
            $registry->register(new ProgramExportSource);
            $registry->register(new UserDocumentExportSource);
            $registry->register(new ExperienceExportSource);
            $registry->register(new EducationExportSource);
            $registry->register(new CertificationExportSource);
            $registry->register(new LanguageExportSource);
            $registry->register(new UserSkillExportSource);
            $registry->register(new UserTrainingExportSource);
            $registry->register(new InternshipExportSource);
            $registry->register(new InterestExportSource);
            $registry->register(new UserNoteExportSource);
            $registry->register(new AppointmentExportSource);
            $registry->register(new UserConsentExportSource);
            $registry->register(new UserVisaHistoryExportSource);
            $registry->register(new UserPreferredCountryExportSource);
            $registry->register(new InterviewExportSource);

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

            // PDF : jbis-screenshot (Puppeteer)
            $registry->register($app->make(PdfExportDriver::class));

            return $registry;
        });
    }

    public function boot(): void
    {
        $this->loadViewsFrom(
            __DIR__.'/../Templates',
            'exports'
        );

        View::composer(['pdf.*', 'exports::pdf.*', 'workflow.pdf.*'], function ($view): void {
            $view->with('pdfBrand', app(PdfBrandAssets::class));
            $view->with('pdfLayout', PdfLayoutMetrics::resolve());
        });
    }
}
