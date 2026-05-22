<?php

declare(strict_types=1);

namespace App\Core\Domain\Catalog\Mappers\Program;

use App\Core\Domain\Catalog\Models\Program;

final class ProgramRelationSync
{
    /**
     * @param  list<array{required_document_id?: int, is_mandatory?: bool, sort_order?: int}>  $rows
     */
    public function syncRequiredDocuments(Program $program, array $rows): void
    {
        if ($rows === []) {
            $program->requiredDocuments()->sync([]);

            return;
        }

        $sync = collect($rows)
            ->mapWithKeys(function (array $item, int $index): array {
                $id = (int) ($item['required_document_id'] ?? 0);
                if ($id <= 0) {
                    return [];
                }

                return [
                    $id => [
                        'is_mandatory' => (bool) ($item['is_mandatory'] ?? true),
                        'sort_order' => (int) ($item['sort_order'] ?? $index + 1),
                    ],
                ];
            })
            ->all();

        $program->requiredDocuments()->sync($sync);
    }

    /**
     * @param  list<array{language_id?: int, is_mandatory?: bool}>  $rows
     */
    public function syncLanguages(Program $program, array $rows): void
    {
        if ($rows === []) {
            $program->languages()->sync([]);

            return;
        }

        $sync = collect($rows)
            ->mapWithKeys(function (array $item): array {
                $id = (int) ($item['language_id'] ?? 0);
                if ($id <= 0) {
                    return [];
                }

                return [
                    $id => [
                        'is_mandatory' => (bool) ($item['is_mandatory'] ?? true),
                    ],
                ];
            })
            ->all();

        $program->languages()->sync($sync);
    }
}
