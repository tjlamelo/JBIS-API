<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Export\Contracts;

use App\Core\Domain\Shared\Export\DTOs\ExportFieldDto;
use App\Core\Domain\Shared\Export\DTOs\ExportSheetDto;

/**
 * Représentation streaming d'une feuille résolue par le service :
 *
 *  - $sheet   : la définition d'origine (nom, source, filtres)
 *  - $fields  : la liste finale des champs (ordonnés, validés)
 *  - $rows()  : un Generator qui émet des lignes (array<string,mixed>)
 *               où la clé est la clé du champ et la valeur, sa valeur formatée.
 *
 * L'usage d'un Generator permet aux drivers d'écrire en streaming sans
 * jamais charger l'ensemble des données en mémoire.
 */
final class ResolvedSheet
{
    /**
     * @param  array<int, ExportFieldDto>  $fields
     * @param  \Closure(): \Generator<int, array<string,mixed>>  $rowsFactory
     */
    public function __construct(
        public readonly ExportSheetDto $sheet,
        public readonly array $fields,
        private readonly \Closure $rowsFactory,
    ) {}

    /**
     * @return \Generator<int, array<string,mixed>>
     */
    public function rows(): \Generator
    {
        return ($this->rowsFactory)();
    }

    /**
     * @return array<int, string>
     */
    public function headers(): array
    {
        return array_map(static fn (ExportFieldDto $f) => $f->label, $this->fields);
    }

    /**
     * @return array<int, string>
     */
    public function fieldKeys(): array
    {
        return array_map(static fn (ExportFieldDto $f) => $f->key, $this->fields);
    }
}
