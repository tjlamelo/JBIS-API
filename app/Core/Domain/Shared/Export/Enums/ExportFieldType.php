<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Export\Enums;

/**
 * Types logiques d'un champ d'export.
 *
 * Ces types pilotent le formatage de la valeur (cf. ValueFormatter)
 * et n'ont aucune dépendance vis-à-vis du driver utilisé (CSV, XLSX, PDF).
 */
enum ExportFieldType: string
{
    case String = 'string';
    case Integer = 'integer';
    case Float = 'float';
    case Boolean = 'boolean';
    case Date = 'date';
    case DateTime = 'datetime';
    case Time = 'time';
    case Enum = 'enum';
    case Translatable = 'translatable';
    case Array = 'array';
    case Count = 'count';
    case Currency = 'currency';
    case Json = 'json';

    public static function fromLoose(?string $value): self
    {
        if ($value === null || $value === '') {
            return self::String;
        }

        return self::tryFrom(strtolower(trim($value))) ?? self::String;
    }
}
