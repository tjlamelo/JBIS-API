<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Pdf\Support;

use App\Core\Domain\Shared\Pdf\Sources\PdfSource;
use Throwable;

/**
 * Matérialise N `PdfSource` en chemins filesystem locaux, exécute un callback
 * avec ces chemins, puis nettoie systématiquement (try/finally).
 *
 *     $materializer->withMaterialized(
 *         [ArchiveSource::of($a), UserDocumentSideSource::front($doc)],
 *         function (array $paths) use ($processor) {
 *             return $processor->merge($paths, storage_path('app/out'));
 *         }
 *     );
 */
final class PdfSourceMaterializer
{
    /**
     * @template TResult
     *
     * @param  list<PdfSource>  $sources
     * @param  callable(list<string>):TResult  $callback
     * @return TResult
     */
    public function withMaterialized(array $sources, callable $callback)
    {
        $paths = [];
        $primed = [];

        try {
            foreach ($sources as $source) {
                $primed[] = $source;
                $paths[] = $source->materialize();
            }

            return $callback($paths);
        } finally {
            foreach ($primed as $source) {
                try {
                    $source->cleanup();
                } catch (Throwable) {
                    // cleanup best-effort : un tmp orphelin ne doit pas masquer
                    // la vraie erreur métier.
                }
            }
        }
    }
}
