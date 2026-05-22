<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Pdf\Enums;

/**
 * Outils disponibles sur l'API iLovePDF.
 *
 * La valeur de chaque cas correspond à l'identifiant attendu par
 * `Ilovepdf::newTask($tool)`.
 */
enum PdfTool: string
{
    case Compress = 'compress';
    case Merge = 'merge';
    case Split = 'split';
    case Protect = 'protect';
    case Unlock = 'unlock';
    case Watermark = 'watermark';
    case PageNumber = 'pagenumber';
    case Rotate = 'rotate';
    case Repair = 'repair';
    case OfficeToPdf = 'officepdf';
    case PdfToJpg = 'pdfjpg';
    case ImageToPdf = 'imagepdf';
    case HtmlToPdf = 'htmlpdf';
    case PdfOcr = 'pdfocr';
    case PdfA = 'pdfa';
    case ValidatePdfA = 'validatepdfa';
    case Extract = 'extract';
    case Sign = 'sign';
}
