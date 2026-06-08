<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Export\Support;

/**
 * Marges et zones réservées pour les PDF JBIS (style Word + bandeaux entête/pied).
 */
final class PdfLayoutMetrics
{
    public const A4_WIDTH_MM = 210.0;

    /** Marge de page Word JBIS (2,5 cm). */
    public const JBIS_PAGE_MARGIN_MM = 25.0;

    /** En-tête : 1,25 cm du haut, hauteur 3,04 cm, largeur 18,19 cm. */
    public const JBIS_HEADER_OFFSET_TOP_MM = 12.5;

    public const JBIS_HEADER_HEIGHT_MM = 30.4;

    public const JBIS_HEADER_WIDTH_MM = 181.9;

    /** Pied : 1,25 cm du bas, hauteur 2,27 cm, largeur 17,37 cm. */
    public const JBIS_FOOTER_OFFSET_BOTTOM_MM = 12.5;

    public const JBIS_FOOTER_HEIGHT_MM = 22.7;

    public const JBIS_FOOTER_WIDTH_MM = 173.7;

    /** @deprecated Utiliser JBIS_PAGE_MARGIN_MM */
    public const WORD_MARGIN_SIDE_MM = self::JBIS_PAGE_MARGIN_MM;

    /** Petit espace entre bandeau et corps du texte (pas la marge latérale 2,5 cm). */
    public const JBIS_CONTENT_GAP_MM = 5.0;

    /** @deprecated */
    public const WORD_CONTENT_GAP_MM = self::JBIS_CONTENT_GAP_MM;

    /**
     * @param  array<string, float|int>|null  $overrides
     * @return array<string, float>
     */
    public static function resolve(?array $overrides = null): array
    {
        /** @var array<string, float|int> $config */
        $config = config('export-pdf.layout', []);
        if ($overrides !== null) {
            $config = array_merge($config, $overrides);
        }

        /** @var array{header?: string, footer?: string} $brand */
        $brand = config('export-pdf.brand', []);

        $headerHeight = isset($config['header_height_mm'])
            ? (float) $config['header_height_mm']
            : self::bandHeightMm((string) ($brand['header'] ?? 'assets/img/entete.png'), self::JBIS_HEADER_HEIGHT_MM);
        $footerHeight = isset($config['footer_height_mm'])
            ? (float) $config['footer_height_mm']
            : self::bandHeightMm((string) ($brand['footer'] ?? 'assets/img/pied.png'), self::JBIS_FOOTER_HEIGHT_MM);

        $headerOffsetTop = (float) ($config['header_offset_top_mm'] ?? self::JBIS_HEADER_OFFSET_TOP_MM);
        $footerOffsetBottom = (float) ($config['footer_offset_bottom_mm'] ?? self::JBIS_FOOTER_OFFSET_BOTTOM_MM);
        $headerWidth = (float) ($config['header_width_mm'] ?? self::JBIS_HEADER_WIDTH_MM);
        $footerWidth = (float) ($config['footer_width_mm'] ?? self::JBIS_FOOTER_WIDTH_MM);

        $gapTop = (float) ($config['content_gap_top'] ?? self::JBIS_CONTENT_GAP_MM);
        $gapBottom = (float) ($config['content_gap_bottom'] ?? self::JBIS_CONTENT_GAP_MM);
        $marginLeft = (float) ($config['margin_left'] ?? self::JBIS_PAGE_MARGIN_MM);
        $marginRight = (float) ($config['margin_right'] ?? self::JBIS_PAGE_MARGIN_MM);

        return [
            'margin_top' => $headerOffsetTop + $headerHeight + $gapTop,
            'margin_bottom' => $footerOffsetBottom + $footerHeight + $gapBottom,
            'margin_left' => $marginLeft,
            'margin_right' => $marginRight,
            'header_height' => $headerHeight,
            'footer_height' => $footerHeight,
            'header_offset_top' => $headerOffsetTop,
            'footer_offset_bottom' => $footerOffsetBottom,
            'header_width' => $headerWidth,
            'footer_width' => $footerWidth,
            'content_gap_top' => $gapTop,
            'content_gap_bottom' => $gapBottom,
            'watermark_opacity' => (float) ($config['watermark_opacity'] ?? 0.06),
            'watermark_width_percent' => (float) ($config['watermark_width_percent'] ?? 42),
        ];
    }

    /**
     * @param  array<string, float|int>|null  $overrides
     * @return array{top: int, right: int, bottom: int, left: int}
     */
  /**
     * Marges nulles côté Puppeteer : le positionnement est géré par @page dans le HTML.
     *
     * @param  array<string, float|int>|null  $overrides
     * @return array{top: int, right: int, bottom: int, left: int}
     */
    public static function printMarginsMm(?array $overrides = null): array
    {
        return [
            'top' => 0,
            'right' => 0,
            'bottom' => 0,
            'left' => 0,
        ];
    }

    /**
     * @param  array<string, float|int>|null  $overrides
     * @return array{
     *     headerImage: string,
     *     footerImage: string,
     *     headerHeightMm: float,
     *     footerHeightMm: float,
     *     headerOffsetTopMm: float,
     *     footerOffsetBottomMm: float,
     *     headerWidthMm: float,
     *     footerWidthMm: float
     * }|null
     */
    public static function headerFooterOptions(PdfBrandAssets $brand, ?array $overrides = null): ?array
    {
        $layout = self::resolve($overrides);
        $headerImage = $brand->headerUri();
        $footerImage = $brand->footerUri();

        if ($headerImage === '' && $footerImage === '') {
            return null;
        }

        return [
            'headerImage' => $headerImage,
            'footerImage' => $footerImage,
            'headerHeightMm' => $layout['header_height'],
            'footerHeightMm' => $layout['footer_height'],
            'headerOffsetTopMm' => $layout['header_offset_top'],
            'footerOffsetBottomMm' => $layout['footer_offset_bottom'],
            'headerWidthMm' => $layout['header_width'],
            'footerWidthMm' => $layout['footer_width'],
        ];
    }

    private static function bandHeightMm(string $relativePath, float $fallback): float
    {
        $path = public_path(ltrim($relativePath, '/'));
        if (! is_file($path)) {
            return $fallback;
        }

        $size = @getimagesize($path);
        if ($size === false || (int) $size[0] <= 0) {
            return $fallback;
        }

        return round(((float) $size[1] / (float) $size[0]) * self::A4_WIDTH_MM, 1);
    }
}
