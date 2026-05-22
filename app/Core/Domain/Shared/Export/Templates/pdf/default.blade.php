<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>{{ $meta['title'] ?? $definition->fileName }}</title>
    <style>
        @page { margin: 24px 28px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #111827; }
        .header { border-bottom: 1px solid #e5e7eb; padding-bottom: 8px; margin-bottom: 16px; }
        .title { font-size: 16px; font-weight: bold; margin: 0; }
        .subtitle { color: #6b7280; font-size: 10px; margin: 2px 0 0; }
        .meta { color: #6b7280; font-size: 9px; margin-top: 4px; }
        h2.sheet-title { font-size: 12px; margin: 16px 0 6px; padding: 4px 8px; background: #f3f4f6; border-left: 3px solid #4f46e5; }
        table { width: 100%; border-collapse: collapse; table-layout: auto; }
        thead th {
            background: #e5e7eb; color: #111827; font-weight: bold;
            text-align: left; padding: 4px 6px; border: 1px solid #d1d5db; font-size: 9.5px;
        }
        tbody td {
            border: 1px solid #e5e7eb; padding: 3px 6px; vertical-align: top;
            word-wrap: break-word; font-size: 9px;
        }
        tbody tr:nth-child(even) td { background: #fafafa; }
        .empty { color: #9ca3af; font-style: italic; padding: 8px; }
        .footer { position: fixed; bottom: -10px; left: 0; right: 0; text-align: center; color: #9ca3af; font-size: 8px; }
    </style>
</head>
<body>
    <div class="header">
        <p class="title">{{ $meta['title'] ?? $definition->fileName }}</p>
        @if (! empty($meta['subtitle']))
            <p class="subtitle">{{ $meta['subtitle'] }}</p>
        @endif
        <p class="meta">Généré le {{ $generatedAt->format('d/m/Y H:i') }}</p>
    </div>

    @foreach ($sheets as $sheet)
        <h2 class="sheet-title">{{ $sheet['name'] }}</h2>

        @if (empty($sheet['rows']))
            <p class="empty">Aucune donnée à afficher.</p>
        @else
            <table>
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
                            @foreach ($row as $value)
                                <td>{{ is_scalar($value) ? $value : json_encode($value, JSON_UNESCAPED_UNICODE) }}</td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    @endforeach

    <div class="footer">{{ config('app.name', 'JBIS') }} — Export confidentiel</div>
</body>
</html>
