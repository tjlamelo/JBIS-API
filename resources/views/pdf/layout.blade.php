@php
    /** @var \App\Core\Domain\Shared\Export\Support\PdfBrandAssets|null $pdfBrand */
    $pdfBrand = $pdfBrand ?? app(\App\Core\Domain\Shared\Export\Support\PdfBrandAssets::class);
    /** @var array<string, float> $pdfLayout */
    $pdfLayout = $pdfLayout ?? \App\Core\Domain\Shared\Export\Support\PdfLayoutMetrics::resolve();
    $marginTop = (float) ($pdfLayout['margin_top'] ?? 48);
    $marginBottom = (float) ($pdfLayout['margin_bottom'] ?? 40);
    $marginLeft = (float) ($pdfLayout['margin_left'] ?? 25);
    $marginRight = (float) ($pdfLayout['margin_right'] ?? 25);
    $headerHeight = (float) ($pdfLayout['header_height'] ?? 30.4);
    $footerHeight = (float) ($pdfLayout['footer_height'] ?? 22.7);
    $headerOffsetTop = (float) ($pdfLayout['header_offset_top'] ?? 12.5);
    $footerOffsetBottom = (float) ($pdfLayout['footer_offset_bottom'] ?? 12.5);
    $headerWidth = (float) ($pdfLayout['header_width'] ?? 181.9);
    $footerWidth = (float) ($pdfLayout['footer_width'] ?? 173.7);
    $watermarkOpacity = (float) ($pdfLayout['watermark_opacity'] ?? 0.06);
    $watermarkWidthPercent = (float) ($pdfLayout['watermark_width_percent'] ?? 42);
    $pageTitle = trim($__env->yieldContent('title')) ?: (config('app.name', 'JBIS') . ' — Document');
@endphp
<!DOCTYPE html>
<html lang="{{ $locale ?? 'fr' }}">
<head>
    <meta charset="UTF-8">
    <title>{{ $pageTitle }}</title>
    <style>
        @page {
            size: A4;
            margin: {{ $marginTop }}mm {{ $marginRight }}mm {{ $marginBottom }}mm {{ $marginLeft }}mm;
        }
        * { box-sizing: border-box; }
        html, body {
            margin: 0;
            padding: 0;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        body {
            font-family: Calibri, DejaVu Sans, Arial, sans-serif;
            color: #000;
            font-size: 11pt;
            line-height: 1.4;
            background: #fff;
        }
        .jbis-pdf-watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: {{ $watermarkWidthPercent }}%;
            max-width: 340px;
            opacity: {{ $watermarkOpacity }};
            z-index: 0;
            pointer-events: none;
        }
        .jbis-pdf-watermark img {
            width: 100%;
            height: auto;
            display: block;
        }
        .jbis-pdf-page-header {
            position: fixed;
            top: {{ $headerOffsetTop }}mm;
            left: 50%;
            transform: translateX(-50%);
            width: {{ $headerWidth }}mm;
            height: {{ $headerHeight }}mm;
            z-index: 50;
            overflow: hidden;
            line-height: 0;
        }
        .jbis-pdf-page-header img {
            width: 100%;
            height: 100%;
            object-fit: fill;
            display: block;
        }
        .jbis-pdf-page-footer {
            position: fixed;
            bottom: {{ $footerOffsetBottom }}mm;
            left: 50%;
            transform: translateX(-50%);
            width: {{ $footerWidth }}mm;
            height: {{ $footerHeight }}mm;
            z-index: 50;
            overflow: hidden;
            line-height: 0;
        }
        .jbis-pdf-page-footer img {
            width: 100%;
            height: 100%;
            object-fit: fill;
            display: block;
        }
        .jbis-pdf-content {
            position: relative;
            z-index: 1;
            background: transparent;
            margin: 0;
            padding: 0;
        }
        /* Aperçu écran (atelier) : simule la zone @page hors impression */
        @media screen {
            .jbis-pdf-content {
                padding: {{ $marginTop }}mm {{ $marginRight }}mm {{ $marginBottom }}mm {{ $marginLeft }}mm;
                min-height: 297mm;
            }
        }
        .page-break-inside-avoid {
            break-inside: avoid;
            page-break-inside: avoid;
        }
    </style>
    @stack('head')
</head>
<body>
    @if ($pdfBrand->watermarkUri() !== '')
        <div class="jbis-pdf-watermark" aria-hidden="true">
            <img src="{{ $pdfBrand->watermarkUri() }}" alt="">
        </div>
    @endif

    @if ($pdfBrand->headerUri() !== '')
        <header class="jbis-pdf-page-header">
            <img src="{{ $pdfBrand->headerUri() }}" alt="JBIS">
        </header>
    @endif

    @if ($pdfBrand->footerUri() !== '')
        <footer class="jbis-pdf-page-footer">
            <img src="{{ $pdfBrand->footerUri() }}" alt="">
        </footer>
    @endif

    <main class="jbis-pdf-content">
        @yield('content')
    </main>
</body>
</html>
