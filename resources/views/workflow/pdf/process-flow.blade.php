<!DOCTYPE html>
<html lang="{{ $locale }}">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }} — Process Flow JBIS</title>
    <!-- Tailwind CSS v3 + police Inter -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Personnalisations complémentaires pour l'impression / PDF -->
    <style>
        /* Overrides pour le rendu PDF (évite les coupures maladroites) */
        body {
            font-feature-settings: "cv02", "cv03", "cv04", "cv11";
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        .page-break-inside-avoid {
            break-inside: avoid;
            page-break-inside: avoid;
        }
        .step-card {
            transition: all 0.1s ease;
        }
        /* Effet de grille discrète en arrière‑plan (visible uniquement à l'écran, mais classe pour PDF) */
        .bg-grid {
            background-image: radial-gradient(#e2e8f0 0.5px, transparent 0.5px);
            background-size: 16px 16px;
        }
        @media print {
            .bg-grid {
                background-image: none;
            }
            .shadow-lg {
                box-shadow: none;
            }
        }
    </style>
</head>
<body class="bg-gray-50 font-sans antialiased">

<div class="max-w-4xl mx-auto my-8 bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100">
    <!-- En‑tête avec bandeau décoratif -->
    <div class="h-1.5 bg-gradient-to-r from-blue-900 via-teal-600 to-blue-900"></div>

    <div class="px-8 py-6 md:px-10 md:py-8">
        <!-- Header principal -->
        <div class="flex flex-wrap justify-between items-start gap-4 border-b border-gray-200 pb-5 mb-6">
            <div>
                <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-blue-800">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    <span>JBIS — Process Flow</span>
                </div>
                <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight text-gray-900 mt-2">{{ $title }}</h1>
            </div>
            <div class="text-right text-sm text-gray-500 space-y-1 bg-gray-50 px-4 py-2 rounded-xl">
                <div>{{ $locale === 'fr' ? 'Généré le' : 'Generated on' }} <span class="font-mono text-gray-800">{{ $generatedAt }}</span></div>
                <div>{{ $locale === 'fr' ? 'Version' : 'Version' }} <span class="font-semibold">{{ $version }}</span></div>
                <span class="inline-block mt-1 px-3 py-0.5 text-xs font-bold rounded-full bg-blue-100 text-blue-800 border border-blue-200">{{ $statusLabel }}</span>
            </div>
        </div>

        <!-- Cartes de contexte -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
            <div class="bg-gradient-to-br from-gray-50 to-white border border-gray-200 rounded-xl p-4 shadow-sm">
                <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">{{ $locale === 'fr' ? 'Programme' : 'Program' }}</div>
                <div class="text-lg font-bold text-gray-800 mt-1">{{ $programLabel ?? '—' }}</div>
            </div>
            <div class="bg-gradient-to-br from-gray-50 to-white border border-gray-200 rounded-xl p-4 shadow-sm">
                <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">{{ $locale === 'fr' ? 'Offre' : 'Offer' }}</div>
                <div class="text-lg font-bold text-gray-800 mt-1">{{ $offerLabel ?? '—' }}</div>
            </div>
            <div class="bg-gradient-to-br from-gray-50 to-white border border-gray-200 rounded-xl p-4 shadow-sm">
                <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">{{ $locale === 'fr' ? 'Pays' : 'Country' }}</div>
                <div class="text-lg font-bold text-gray-800 mt-1">{{ $countryLabel ?? '—' }}</div>
            </div>
        </div>

        @if($fileOpeningFeeLabel || $totalProcedureFeesLabel)
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-8">
                @if($fileOpeningFeeLabel)
                    <div class="border border-amber-200 bg-amber-50 rounded-xl p-4">
                        <div class="text-xs font-medium text-amber-800 uppercase tracking-wide">
                            {{ $locale === 'fr' ? 'Frais d\'ouverture de dossier' : 'File opening fee' }}
                        </div>
                        <div class="text-xl font-bold text-amber-900 mt-1">{{ $fileOpeningFeeLabel }}</div>
                        <p class="text-xs text-amber-700 mt-1">
                            {{ $locale === 'fr' ? 'Hors frais de procédure' : 'Not included in procedure fees' }}
                        </p>
                    </div>
                @endif
                @if($totalProcedureFeesLabel)
                    <div class="border border-emerald-200 bg-emerald-50 rounded-xl p-4">
                        <div class="text-xs font-medium text-emerald-800 uppercase tracking-wide">
                            {{ $locale === 'fr' ? 'Frais de procédure' : 'Procedure fees' }}
                        </div>
                        <div class="text-xl font-bold text-emerald-900 mt-1">{{ $totalProcedureFeesLabel }}</div>
                    </div>
                @endif
            </div>
        @endif

        <!-- Sections dynamiques -->
        @foreach ($sections as $section)
            <div class="mb-10 page-break-inside-avoid">
                <!-- En‑tête de section avec pastille colorée -->
                <div class="flex items-start gap-3 mb-4">
                    <div class="w-1.5 h-8 rounded-full" style="background-color: {{ $section['color'] }}; box-shadow: 0 2px 4px rgba(0,0,0,0.05);"></div>
                    <div class="flex-1">
                        <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-gray-500">
                            <span class="font-mono font-semibold">{{ $locale === 'fr' ? 'Section' : 'Section' }} {{ $section['order'] }}</span>
                            @if($section['icon'])
                                <span class="bg-gray-100 px-2 py-0.5 rounded-full text-gray-700">{{ $section['icon'] }}</span>
                            @endif
                            <span class="font-mono text-gray-400">{{ $section['key'] }}</span>
                        </div>
                        <h2 class="text-xl font-bold text-gray-900 mt-1">{{ $section['title'] }}</h2>
                        @if($section['description'])
                            <p class="text-sm text-gray-600 mt-1 leading-relaxed">{{ $section['description'] }}</p>
                        @endif
                        @if($section['visible_after'])
                            <p class="text-xs text-amber-700 bg-amber-50 inline-block px-2 py-0.5 rounded-md mt-2">
                                ⏱ {{ $locale === 'fr' ? 'Visible après :' : 'Visible after:' }} {{ $section['visible_after'] }}
                            </p>
                        @endif
                    </div>
                </div>

                <!-- Liste des steps (cards) -->
                <div class="space-y-3 ml-2">
                    @foreach ($section['steps'] as $step)
                        <div class="step-card bg-white border border-gray-200 rounded-xl p-4 shadow-sm hover:shadow-md transition-all duration-200">
                            <div class="flex flex-wrap justify-between items-start gap-2">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="font-bold text-gray-900 text-base">{{ $step['order'] }}. {{ $step['title'] }}</span>
                                        <span class="text-xs font-mono uppercase tracking-wide px-2 py-0.5 rounded-full bg-gray-100 text-gray-700">{{ $step['step_type_label'] }}</span>
                                    </div>
                                    @if($step['description'])
                                        <p class="text-sm text-gray-600 mt-1.5 leading-relaxed">{{ $step['description'] }}</p>
                                    @endif
                                    @if($step['amount'])
                                        <p class="text-sm font-semibold text-teal-700 bg-teal-50 inline-block px-2 py-0.5 rounded-md mt-2">
                                            💰 {{ $step['amount'] }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                            @if($step['requires_documents'] && count($step['document_labels']) > 0)
                                <div class="flex flex-wrap gap-1.5 mt-3 pt-1 border-t border-gray-100">
                                    <svg class="w-4 h-4 text-amber-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    @foreach ($step['document_labels'] as $label)
                                        <span class="text-xs font-medium bg-amber-50 text-amber-800 px-2 py-0.5 rounded-md border border-amber-200">{{ $label }}</span>
                                    @endforeach
                                </div>
                            @elseif($step['requires_documents'])
                                <div class="mt-3">
                                    <span class="text-xs font-medium bg-gray-100 text-gray-700 px-2 py-0.5 rounded-md">📄 {{ $locale === 'fr' ? 'Documents requis' : 'Documents required' }}</span>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach

        <!-- Pied de page avec compteur et décor -->
        <div class="mt-10 pt-4 border-t border-gray-200 flex justify-between items-center text-xs text-gray-400">
            <div class="flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                <span>{{ $stepsCount }} {{ $locale === 'fr' ? 'étape(s)' : 'step(s)' }}</span>
            </div>
            <div class="font-mono">Process Flow JBIS · {{ $generatedAt }}</div>
            <div class="opacity-50">JBIS</div>
        </div>
    </div>
</div>

</body>
</html>