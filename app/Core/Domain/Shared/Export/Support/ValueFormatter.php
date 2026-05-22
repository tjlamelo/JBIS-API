<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Export\Support;

use App\Core\Domain\Shared\Export\DTOs\ExportFieldDto;
use App\Core\Domain\Shared\Export\Enums\ExportFieldType;
use BackedEnum;
use Carbon\CarbonInterface;
use DateTimeInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Stringable;
use UnitEnum;

/**
 * Applique le formatage final d'une valeur en fonction du type du champ.
 *
 * Aucune dépendance vis-à-vis du driver (CSV/XLSX/PDF) : la valeur
 * renvoyée est toujours un scalaire (string|int|float|bool|null), prêt
 * à être consommé par n'importe quel writer.
 */
final class ValueFormatter
{
    public function format(mixed $value, ExportFieldDto $field): mixed
    {
        $value = $value ?? $field->default;

        if ($value === null) {
            return null;
        }

        return match ($field->type) {
            ExportFieldType::String => $this->toString($value),
            ExportFieldType::Integer => $this->toInt($value),
            ExportFieldType::Float => $this->toFloat($value),
            ExportFieldType::Boolean => $this->toBool($value),
            ExportFieldType::Date => $this->toDate($value, $field->format ?? 'Y-m-d'),
            ExportFieldType::DateTime => $this->toDate($value, $field->format ?? 'Y-m-d H:i:s'),
            ExportFieldType::Time => $this->toDate($value, $field->format ?? 'H:i:s'),
            ExportFieldType::Enum => $this->toEnumValue($value),
            ExportFieldType::Translatable => $this->toTranslatable($value, $field->locale),
            ExportFieldType::Array => $this->toJoinedArray($value, $field->format ?? ', '),
            ExportFieldType::Count => $this->toCount($value),
            ExportFieldType::Currency => $this->toCurrency($value, $field->format ?? 'XAF'),
            ExportFieldType::Json => $this->toJson($value),
        };
    }

    private function toString(mixed $v): ?string
    {
        if (is_array($v) || $v instanceof Collection) {
            return $this->toJoinedArray($v, ', ');
        }

        if ($v instanceof DateTimeInterface) {
            return $v->format('Y-m-d H:i:s');
        }

        if ($v instanceof UnitEnum) {
            return $this->toEnumValue($v);
        }

        if (is_bool($v)) {
            return $v ? 'true' : 'false';
        }

        if ($v instanceof Stringable || is_scalar($v)) {
            return (string) $v;
        }

        return null;
    }

    private function toInt(mixed $v): int
    {
        if (is_bool($v)) {
            return $v ? 1 : 0;
        }

        return (int) $v;
    }

    private function toFloat(mixed $v): float
    {
        return (float) $v;
    }

    private function toBool(mixed $v): string
    {
        $bool = filter_var($v, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        if ($bool === null) {
            $bool = (bool) $v;
        }

        return $bool ? 'Oui' : 'Non';
    }

    private function toDate(mixed $v, string $format): ?string
    {
        try {
            if ($v instanceof CarbonInterface || $v instanceof DateTimeInterface) {
                $date = Carbon::instance($v instanceof CarbonInterface ? $v : Carbon::parse($v->format(DATE_ATOM)));
            } else {
                $date = Carbon::parse((string) $v);
            }

            return $date->format($format);
        } catch (\Throwable) {
            return null;
        }
    }

    private function toEnumValue(mixed $v): ?string
    {
        if ($v instanceof BackedEnum) {
            return (string) $v->value;
        }

        if ($v instanceof UnitEnum) {
            return $v->name;
        }

        return $this->toString($v);
    }

    private function toTranslatable(mixed $v, ?string $locale): ?string
    {
        if (is_string($v)) {
            return $v;
        }

        if (is_array($v)) {
            $locale ??= app()->getLocale();

            return (string) ($v[$locale]
                ?? $v[config('app.fallback_locale', 'en')]
                ?? array_values($v)[0]
                ?? '');
        }

        return $this->toString($v);
    }

    private function toJoinedArray(mixed $v, string $glue): ?string
    {
        if ($v instanceof Collection) {
            $v = $v->all();
        }

        if (! is_array($v)) {
            return $this->toString($v);
        }

        $flat = array_map(fn ($item) => $this->toString($item), $v);
        $flat = array_filter($flat, static fn ($item) => $item !== null && $item !== '');

        return implode($glue, $flat);
    }

    private function toCount(mixed $v): int
    {
        if ($v instanceof Collection) {
            return $v->count();
        }

        if (is_array($v)) {
            return count($v);
        }

        if (is_numeric($v)) {
            return (int) $v;
        }

        return 0;
    }

    private function toCurrency(mixed $v, string $currency): string
    {
        $amount = is_numeric($v) ? (float) $v : 0.0;
        $formatted = number_format($amount, 2, '.', ' ');

        return $formatted.' '.strtoupper($currency);
    }

    private function toJson(mixed $v): string
    {
        try {
            return (string) json_encode($v, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        } catch (\Throwable) {
            return '';
        }
    }
}
