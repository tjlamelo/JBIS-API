<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Export\Contracts;

use App\Core\Domain\Shared\Export\DTOs\ExportDefinitionDto;
use App\Core\Domain\Shared\Export\DTOs\ExportResultDto;
use App\Core\Domain\Shared\Export\Enums\ExportFormat;

/**
 * Driver d'écriture d'un export dans un format donné (XLSX, CSV, PDF…).
 *
 * Un driver est totalement indépendant des sources : il consomme un
 * ExportDefinitionDto déjà résolu (les lignes/colonnes lui sont fournies via le service)
 * et produit un fichier sur disque, renvoyé sous la forme d'un ExportResultDto.
 */
interface ExportDriverInterface
{
    public function format(): ExportFormat;

    public function supports(ExportFormat $format): bool;

    /**
     * @param  iterable<int, ResolvedSheet>  $resolvedSheets  Itérable de feuilles résolues (en mémoire ou streaming)
     */
    public function export(ExportDefinitionDto $definition, iterable $resolvedSheets): ExportResultDto;
}
