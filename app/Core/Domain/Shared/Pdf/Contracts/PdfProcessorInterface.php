<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Pdf\Contracts;

use App\Core\Domain\Shared\Pdf\DTOs\PdfTaskResult;
use App\Core\Domain\Shared\Pdf\Enums\CompressLevel;

/**
 * Contrat d'abstraction du traitement PDF.
 *
 * Toute implémentation (par défaut : iLovePDF) doit pouvoir produire un
 * `PdfTaskResult` à partir d'un ou plusieurs fichiers d'entrée. L'écriture
 * de la sortie reste sous le contrôle de l'appelant via `$outputDir`.
 */
interface PdfProcessorInterface
{
    /**
     * Compresse un ou plusieurs PDF.
     *
     * @param  list<string>  $files  chemins absolus des PDF sources
     */
    public function compress(
        array $files,
        string $outputDir,
        ?CompressLevel $level = null,
        ?string $outputFilename = null,
    ): PdfTaskResult;

    /**
     * Fusionne plusieurs PDF en un seul fichier.
     *
     * @param  list<string>  $files  dans l'ordre de fusion souhaité
     */
    public function merge(
        array $files,
        string $outputDir,
        ?string $outputFilename = null,
    ): PdfTaskResult;

    /**
     * Découpe un PDF selon des plages de pages (ex : "1-3,5,8-10").
     */
    public function split(
        string $file,
        string $outputDir,
        string $ranges,
        ?string $outputFilename = null,
    ): PdfTaskResult;

    /**
     * Protège un PDF par mot de passe.
     */
    public function protect(
        string $file,
        string $outputDir,
        string $password,
        ?string $outputFilename = null,
    ): PdfTaskResult;

    /**
     * Retire le mot de passe d'un PDF protégé.
     */
    public function unlock(
        string $file,
        string $outputDir,
        string $password,
        ?string $outputFilename = null,
    ): PdfTaskResult;

    /**
     * Applique un filigrane texte sur un PDF.
     *
     * @param  list<string>  $files
     */
    public function watermark(
        array $files,
        string $outputDir,
        string $text,
        ?string $outputFilename = null,
    ): PdfTaskResult;

    /**
     * Convertit des images (jpg/png/...) en un PDF.
     *
     * @param  list<string>  $images
     */
    public function imagesToPdf(
        array $images,
        string $outputDir,
        ?string $outputFilename = null,
    ): PdfTaskResult;

    /**
     * Convertit un PDF en images JPG (1 fichier par page → ZIP).
     */
    public function pdfToImages(
        string $file,
        string $outputDir,
        ?string $outputFilename = null,
    ): PdfTaskResult;

    /**
     * Convertit un fichier office (docx, xlsx, pptx, ...) en PDF.
     */
    public function officeToPdf(
        string $file,
        string $outputDir,
        ?string $outputFilename = null,
    ): PdfTaskResult;

    /**
     * Exécute un OCR sur un PDF.
     *
     * @param  list<string>  $files
     */
    public function ocr(
        array $files,
        string $outputDir,
        ?string $outputFilename = null,
    ): PdfTaskResult;
}
