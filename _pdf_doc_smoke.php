<?php

declare(strict_types=1);

require __DIR__.'/vendor/autoload.php';

$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

use App\Core\Domain\Shared\Pdf\Services\PdfDocumentService;
use App\Core\Domain\Shared\Pdf\Sources\LocalPathSource;
use App\Core\Domain\Shared\Pdf\Sources\StorageDiskSource;
use App\Core\Domain\Shared\Pdf\Support\PdfSourceMaterializer;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Storage;

echo "=== PdfDocumentService smoke test ===\n";

$service = app(PdfDocumentService::class);
echo 'Resolved PdfDocumentService    : '.$service::class."\n";
echo 'Alias pdf.documents resolved   : '.(app('pdf.documents') === $service ? 'yes' : 'NO')."\n";

$materializer = app(PdfSourceMaterializer::class);
echo 'Resolved PdfSourceMaterializer : '.$materializer::class."\n";

// Verify disque jbis_assets is usable
$disk = Storage::disk('jbis_assets');
echo 'Disk jbis_assets root          : '.($disk->path('') ?: '[none]')."\n";

// Materializer dry-run on a local fake file (no iLovePDF call)
$tmpFile = sys_get_temp_dir().DIRECTORY_SEPARATOR.'jbis_pdf_test_'.uniqid().'.pdf';
file_put_contents($tmpFile, "%PDF-1.4 fake\n");

$source = new LocalPathSource($tmpFile);
$mat = $source->materialize();
echo 'LocalPathSource path           : '.$mat['path']."\n";
echo 'LocalPathSource cleanup        : '.($mat['cleanup'] === null ? 'none (expected)' : 'set')."\n";
echo 'LocalPathSource name           : '.$source->name()."\n";
echo 'LocalPathSource extension      : '.$source->extension()."\n";

// Test StorageDiskSource if we can write
try {
    $disk->put('tmp/pdfsmoke.pdf', "%PDF-1.4 fake\n");
    $diskSource = StorageDiskSource::onAssets('tmp/pdfsmoke.pdf');
    $diskMat = $diskSource->materialize();
    echo 'StorageDiskSource path         : '.$diskMat['path']."\n";
    echo 'StorageDiskSource cleanup      : '.($diskMat['cleanup'] === null ? 'none (direct local path)' : 'tmp copy')."\n";
    $disk->delete('tmp/pdfsmoke.pdf');
} catch (Throwable $e) {
    echo 'StorageDiskSource             : skipped ('.$e->getMessage().")\n";
}

// Materializer with one source
$result = $materializer->withMaterialized([$source], function (array $paths): string {
    return implode(',', $paths);
});
echo 'Materializer callback result   : '.$result."\n";

@unlink($tmpFile);

echo "\nOK\n";
