<?php

declare(strict_types=1);

use App\Core\Domain\Shared\Export\Support\PdfLayoutMetrics;

return [
    'paper' => env('EXPORT_PDF_PAPER', 'a4'),
    'margins' => PdfLayoutMetrics::printMarginsMm(),
    'brand' => [
        'header' => 'assets/img/entete.png',
        'footer' => 'assets/img/pied.png',
        'watermark' => 'assets/img/logo-jbis.png',
    ],
    /**
     * Layout Word JBIS (mm). Le texte commence après l'en-tête et s'arrête avant le pied.
     * margin_left/right = 2,5 cm · content_gap = espace court sous/sur le bandeau (pas 2,5 cm).
     */
    'layout' => [
        'margin_left' => PdfLayoutMetrics::JBIS_PAGE_MARGIN_MM,
        'margin_right' => PdfLayoutMetrics::JBIS_PAGE_MARGIN_MM,
        'content_gap_top' => PdfLayoutMetrics::JBIS_CONTENT_GAP_MM,
        'content_gap_bottom' => PdfLayoutMetrics::JBIS_CONTENT_GAP_MM,
        'header_offset_top_mm' => PdfLayoutMetrics::JBIS_HEADER_OFFSET_TOP_MM,
        'header_height_mm' => PdfLayoutMetrics::JBIS_HEADER_HEIGHT_MM,
        'header_width_mm' => PdfLayoutMetrics::JBIS_HEADER_WIDTH_MM,
        'footer_offset_bottom_mm' => PdfLayoutMetrics::JBIS_FOOTER_OFFSET_BOTTOM_MM,
        'footer_height_mm' => PdfLayoutMetrics::JBIS_FOOTER_HEIGHT_MM,
        'footer_width_mm' => PdfLayoutMetrics::JBIS_FOOTER_WIDTH_MM,
        'watermark_opacity' => 0.06,
        'watermark_width_percent' => 42,
    ],
    'candidate_template' => 'exports::pdf.candidate-dossier',
];
