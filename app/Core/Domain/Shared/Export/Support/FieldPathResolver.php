<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Export\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

/**
 * Résout un chemin (dot-path) sur un modèle Eloquent, un tableau ou un objet.
 *
 * Exemples :
 *   - "email"                     → $model->email
 *   - "profile.first_name"        → $model->profile?->first_name
 *   - "applications.0.status"     → première candidature
 *   - "documents.*.type"          → ["passport","cv",...] (collection mappée)
 *   - "documents"                 → la relation/collection elle-même (utile avec type=count)
 *
 * La résolution est non-stricte : tout segment manquant renvoie null sans casser.
 */
final class FieldPathResolver
{
    /**
     * Résout un chemin sur n'importe quelle structure.
     */
    public function resolve(mixed $subject, string $path): mixed
    {
        if ($path === '') {
            return $subject;
        }

        $segments = explode('.', $path);
        $current = $subject;

        foreach ($segments as $segment) {
            if ($current === null) {
                return null;
            }

            if ($segment === '*') {
                $current = $this->toIterable($current);
                $rest = implode('.', array_slice($segments, array_search('*', $segments, true) + 1));

                $mapped = [];
                foreach ($current as $item) {
                    $mapped[] = $rest === '' ? $item : $this->resolve($item, $rest);
                }

                return $mapped;
            }

            $current = $this->readSegment($current, $segment);
        }

        return $current;
    }

    private function readSegment(mixed $current, string $segment): mixed
    {
        if ($current instanceof Model) {
            // Accès direct attribut/cast/accessor
            if (array_key_exists($segment, $current->getAttributes())
                || $current->hasGetMutator($segment)
                || method_exists($current, $segment)
                || $current->relationLoaded($segment)
            ) {
                return $current->{$segment};
            }

            return $current->{$segment} ?? null;
        }

        if ($current instanceof Collection) {
            if (is_numeric($segment)) {
                return $current->get((int) $segment);
            }

            return $current->pluck($segment)->all();
        }

        if (is_array($current)) {
            if (is_numeric($segment) && array_key_exists((int) $segment, $current)) {
                return $current[(int) $segment];
            }

            return Arr::get($current, $segment);
        }

        if (is_object($current)) {
            return $current->{$segment} ?? null;
        }

        return null;
    }

    /**
     * @return iterable<int|string, mixed>
     */
    private function toIterable(mixed $value): iterable
    {
        if ($value instanceof Collection) {
            return $value->all();
        }

        if (is_iterable($value)) {
            return $value;
        }

        return [];
    }
}
