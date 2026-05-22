<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Pdf\Exceptions;

use RuntimeException;

/**
 * Exception racine du module PDF.
 *
 * Toutes les erreurs émises par les implémentations de
 * `PdfProcessorInterface` héritent de cette classe pour permettre un
 * `try { } catch (PdfException $e) { }` côté appelant.
 */
class PdfException extends RuntimeException {}
