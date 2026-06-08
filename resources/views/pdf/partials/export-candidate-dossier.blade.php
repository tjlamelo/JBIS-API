@php
    $locale = str_starts_with(strtolower((string) ($meta['locale'] ?? 'fr')), 'en') ? 'en' : 'fr';
    $title = $meta['title'] ?? $definition->fileName;
    $subtitle = $meta['subtitle'] ?? null;
    $generatedLabel = $generatedAt->format('d/m/Y H:i');
    $sectionsCount = count($sheets);
    $totalRows = array_sum(array_map(static fn (array $s): int => count($s['rows']), $sheets));
    $formatCell = static function (mixed $value) use ($locale): string {
        if ($value === null || $value === '') {
            return '—';
        }
        if (is_bool($value)) {
            return $value ? ($locale === 'fr' ? 'Oui' : 'Yes') : ($locale === 'fr' ? 'Non' : 'No');
        }
        if (is_scalar($value)) {
            return (string) $value;
        }

        return json_encode($value, JSON_UNESCAPED_UNICODE) ?: '—';
    };
@endphp

<div class="jbis-doc">
    <p class="jbis-doc-eyebrow">JBIS — {{ $locale === 'fr' ? 'Dossier candidat' : 'Candidate file' }}</p>
    <h1 class="jbis-doc-title">{{ $title }}</h1>
    @if ($subtitle)
        <p class="jbis-doc-subtitle">{{ $subtitle }}</p>
    @endif
    <p class="jbis-doc-meta">
        {{ $locale === 'fr' ? 'Généré le' : 'Generated on' }} {{ $generatedLabel }}
        · {{ $sectionsCount }} {{ $locale === 'fr' ? 'section(s)' : 'section(s)' }}
        · {{ $totalRows }} {{ $locale === 'fr' ? 'enregistrement(s)' : 'record(s)' }}
        · {{ $locale === 'fr' ? 'Document confidentiel' : 'Confidential document' }}
    </p>

    @foreach ($sheets as $index => $sheet)
        @php $rowCount = count($sheet['rows']); @endphp
        <section class="jbis-section">
            <h2 class="jbis-section-title">
                {{ $sheet['name'] }}
                <span class="jbis-section-meta">
                    ({{ $rowCount }} {{ $locale === 'fr' ? 'ligne(s)' : 'row(s)' }})
                </span>
            </h2>

            @if ($rowCount === 0)
                <p class="jbis-empty">{{ $locale === 'fr' ? 'Aucune donnée à afficher.' : 'No data to display.' }}</p>
            @elseif ($rowCount === 1)
                <table class="jbis-table jbis-kv">
                    @foreach (array_chunk($sheet['headers'], 2, true) as $pair)
                        <tr>
                            @foreach ($pair as $colIndex => $header)
                                @php $value = array_values($sheet['rows'][0])[$colIndex] ?? null; @endphp
                                <td>
                                    <p class="jbis-field-label">{{ $header }}</p>
                                    <p class="jbis-field-value">{{ $formatCell($value) }}</p>
                                </td>
                            @endforeach
                            @if (count($pair) === 1)
                                <td></td>
                            @endif
                        </tr>
                    @endforeach
                </table>
            @else
                <table class="jbis-table">
                    <thead>
                        <tr>
                            @foreach ($sheet['headers'] as $header)
                                <th>{{ $header }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($sheet['rows'] as $row)
                            <tr>
                                @foreach (array_values($row) as $value)
                                    <td>{{ $formatCell($value) }}</td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </section>
    @endforeach
</div>
