<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Core\Domain\Workflow\Import\ProcessFlowImportCoordinator;
use App\Core\Domain\Workflow\Import\ProcessFlowImportTemplateGenerator;
use Illuminate\Console\Command;

final class ProcessFlowImportCommand extends Command
{
    protected $signature = 'process-flow:import
        {file? : Chemin vers le fichier Excel (.xlsx) ou JSON}
        {--commit : Écrire en base (sinon dry-run)}
        {--format= : excel|json — déduit de l\'extension si omis}
        {--generate-template : Génère le modèle Excel et quitte}';

    protected $description = 'Importer un parcours procédural (ProcessFlow) depuis Excel ou JSON';

    public function handle(
        ProcessFlowImportCoordinator $coordinator,
        ProcessFlowImportTemplateGenerator $templateGenerator,
    ): int {
        if ($this->option('generate-template')) {
            $path = $templateGenerator->generate($templateGenerator->defaultPath());
            $this->info("Modèle généré : {$path}");

            return self::SUCCESS;
        }

        $file = (string) $this->argument('file');
        if ($file === '') {
            $this->error('Argument file requis (ou utilisez --generate-template).');

            return self::FAILURE;
        }

        if (! is_file($file)) {
            $this->error("Fichier introuvable : {$file}");

            return self::FAILURE;
        }

        $format = (string) ($this->option('format') ?: $this->guessFormat($file));
        $commit = (bool) $this->option('commit');

        $result = $coordinator->importFromFile($file, $format, $commit);

        if (! $result->success) {
            $this->error($commit ? 'Import échoué.' : 'Validation échouée (dry-run).');
            foreach ($result->issues as $issue) {
                $this->line("  [{$issue->path}] {$issue->field} — {$issue->message}");
            }

            return self::FAILURE;
        }

        $mode = $commit ? 'commit' : 'dry-run';
        $this->info($commit ? 'Import réussi.' : 'Validation réussie (dry-run, rien n\'a été écrit).');

        foreach ($result->summaries as $summary) {
            $this->line(sprintf(
                '  • %s → groupe %s, version %d (%d sections, %d étapes, %d documents) [%s]',
                $summary->flowKey,
                $summary->flowGroupId,
                $summary->version,
                $summary->sectionsCount,
                $summary->stepsCount,
                $summary->documentsCount,
                $mode,
            ));
        }

        if ($commit && $result->createdFlowIds !== []) {
            $this->line('IDs créés : '.implode(', ', $result->createdFlowIds));
        }

        return self::SUCCESS;
    }

    private function guessFormat(string $file): string
    {
        return str_ends_with(strtolower($file), '.json') ? 'json' : 'excel';
    }
}
