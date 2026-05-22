<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Export\Support;

use App\Core\Domain\Shared\Export\Contracts\ResolvedSheet;
use App\Core\Domain\Shared\Export\DTOs\ExportDefinitionDto;
use Illuminate\Support\Carbon;

/**
 * Rendu d'un template HTML fourni par le front.
 *
 * Le front conçoit le template (en-tête, pied, logo, signatures, CSS…)
 * et le module y injecte uniquement les données. Aucune logique côté
 * client : on évite ainsi tout moteur de templates (Blade/Twig) avec
 * exécution arbitraire.
 *
 * --- SYNTAXE PRISE EN CHARGE (placeholders) ---
 *
 *  • Variables scalaires (meta) :
 *      {{ title }}, {{ subtitle }}, {{ generated_at }}
 *      {{ meta.client.name }}        (dot-path libre dans `meta`)
 *
 *  • Toutes les feuilles, rendues en tables stylées :
 *      {{ sheets }}
 *
 *  • Une feuille précise (par son nom) :
 *      {{ sheet:Utilisateurs }}
 *
 *  • Données brutes d'une feuille en JSON (pour les CSS/JS custom) :
 *      {{ sheet_data:Utilisateurs }}
 *
 *  • Compteurs pratiques :
 *      {{ sheet_count }}, {{ row_count:Utilisateurs }}
 *
 * Toutes les valeurs scalaires sont échappées via htmlspecialchars.
 * Les tables de feuilles produisent du HTML déjà sécurisé.
 */
final class HtmlTemplateRenderer
{
    /**
     * @param  array<int, ResolvedSheet>  $sheets
     */
    public function render(string $template, ExportDefinitionDto $definition, array $sheets): string
    {
        $context = $this->buildContext($definition, $sheets);

        return preg_replace_callback(
            '/\{\{\s*([a-z0-9_.\-: ]+?)\s*\}\}/i',
            function (array $m) use ($context, $sheets): string {
                return $this->resolvePlaceholder(trim($m[1]), $context, $sheets);
            },
            $template
        ) ?? $template;
    }

    /**
     * @param  array<int, ResolvedSheet>  $sheets
     * @return array<string,mixed>
     */
    private function buildContext(ExportDefinitionDto $definition, array $sheets): array
    {
        $generated = Carbon::now();

        return [
            'file_name' => $definition->fileName,
            'format' => $definition->format->value,
            'title' => (string) ($definition->meta['title'] ?? $definition->fileName),
            'subtitle' => (string) ($definition->meta['subtitle'] ?? ''),
            'generated_at' => $generated->format('d/m/Y H:i'),
            'generated_at_iso' => $generated->toIso8601String(),
            'sheet_count' => count($sheets),
            'meta' => $definition->meta,
            'app_name' => (string) config('app.name', 'JBIS'),
        ];
    }

    /**
     * @param  array<string,mixed>  $context
     * @param  array<int, ResolvedSheet>  $sheets
     */
    private function resolvePlaceholder(string $expression, array $context, array $sheets): string
    {
        // {{ sheets }} → toutes les feuilles
        if ($expression === 'sheets') {
            return $this->renderAllSheets($sheets);
        }

        // {{ sheet:Nom }} → une feuille précise
        if (str_starts_with($expression, 'sheet:')) {
            $name = trim(substr($expression, 6));
            $found = $this->findSheet($sheets, $name);

            return $found !== null ? $this->renderSheet($found) : '';
        }

        // {{ sheet_data:Nom }} → JSON des lignes d'une feuille
        if (str_starts_with($expression, 'sheet_data:')) {
            $name = trim(substr($expression, 11));
            $found = $this->findSheet($sheets, $name);
            if ($found === null) {
                return '[]';
            }

            $rows = iterator_to_array($found->rows(), false);

            return $this->escape((string) json_encode($rows, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        }

        // {{ row_count:Nom }} → nombre de lignes d'une feuille
        if (str_starts_with($expression, 'row_count:')) {
            $name = trim(substr($expression, 10));
            $found = $this->findSheet($sheets, $name);
            if ($found === null) {
                return '0';
            }

            return (string) iterator_count($found->rows());
        }

        // Chemin libre sur le contexte (ex. meta.client.name)
        $value = $this->resolvePath($context, $expression);

        return $this->escape($value);
    }

    /**
     * @param  array<int, ResolvedSheet>  $sheets
     */
    private function findSheet(array $sheets, string $name): ?ResolvedSheet
    {
        foreach ($sheets as $sheet) {
            if (strcasecmp($sheet->sheet->name, $name) === 0) {
                return $sheet;
            }
        }

        return null;
    }

    /**
     * @param  array<int, ResolvedSheet>  $sheets
     */
    private function renderAllSheets(array $sheets): string
    {
        $html = '';
        foreach ($sheets as $sheet) {
            $html .= $this->renderSheet($sheet);
        }

        return $html;
    }

    private function renderSheet(ResolvedSheet $sheet): string
    {
        $title = $this->escape($sheet->sheet->name);
        $headers = '';
        foreach ($sheet->headers() as $header) {
            $headers .= '<th>'.$this->escape($header).'</th>';
        }

        $body = '';
        $keys = $sheet->fieldKeys();
        $hasRows = false;
        foreach ($sheet->rows() as $row) {
            $hasRows = true;
            $body .= '<tr>';
            foreach ($keys as $k) {
                $v = $row[$k] ?? null;
                $body .= '<td>'.$this->escape($v).'</td>';
            }
            $body .= '</tr>';
        }

        if (! $hasRows) {
            return '<section class="export-sheet" data-name="'.$title.'">'
                .'<h2 class="export-sheet-title">'.$title.'</h2>'
                .'<p class="export-empty">Aucune donnée à afficher.</p>'
                .'</section>';
        }

        return '<section class="export-sheet" data-name="'.$title.'">'
            .'<h2 class="export-sheet-title">'.$title.'</h2>'
            .'<table class="export-table">'
            .'<thead><tr>'.$headers.'</tr></thead>'
            .'<tbody>'.$body.'</tbody>'
            .'</table>'
            .'</section>';
    }

    private function escape(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_array($value)) {
            return htmlspecialchars((string) json_encode($value, JSON_UNESCAPED_UNICODE), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        if (! is_scalar($value)) {
            return '';
        }

        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /**
     * Résolution dot-path sécurisée sur un tableau associatif.
     *
     * @param  array<string,mixed>  $data
     */
    private function resolvePath(array $data, string $path): mixed
    {
        if (! str_contains($path, '.')) {
            return $data[$path] ?? null;
        }

        $current = $data;
        foreach (explode('.', $path) as $segment) {
            if (is_array($current) && array_key_exists($segment, $current)) {
                $current = $current[$segment];

                continue;
            }

            return null;
        }

        return $current;
    }
}
