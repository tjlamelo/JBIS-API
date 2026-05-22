<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Export\Requests;

use App\Core\Domain\Shared\Export\DTOs\ExportDefinitionDto;
use App\Core\Domain\Shared\Export\Registry\ExportDriverRegistry;
use App\Core\Domain\Shared\Export\Registry\ExportSourceRegistry;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validation des requêtes POST /v1/exports.
 *
 * La règle clé : le format doit être pris en charge par un driver enregistré,
 * et chaque feuille doit cibler une source enregistrée.
 */
final class ExportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $sourceKeys = array_keys(app(ExportSourceRegistry::class)->all());
        $formats = app(ExportDriverRegistry::class)->availableFormats();

        return [
            'format' => ['required', 'string', 'in:'.implode(',', $formats)],
            'file_name' => ['nullable', 'string', 'max:120'],
            'meta' => ['nullable', 'array'],
            'meta.title' => ['nullable', 'string', 'max:200'],
            'meta.subtitle' => ['nullable', 'string', 'max:300'],
            'meta.template' => ['nullable', 'string'],
            'meta.template_html' => ['nullable', 'string'],
            'meta.orientation' => ['nullable', 'in:portrait,landscape'],
            'meta.paper' => ['nullable', 'string'],

            'sheets' => ['required', 'array', 'min:1'],
            'sheets.*.name' => ['nullable', 'string', 'max:60'],
            'sheets.*.source' => ['required', 'string', 'in:'.implode(',', $sourceKeys)],
            'sheets.*.filters' => ['sometimes', 'array'],
            'sheets.*.with' => ['sometimes', 'array'],
            'sheets.*.with.*' => ['string'],
            'sheets.*.chunk_size' => ['sometimes', 'integer', 'min:10', 'max:5000'],

            'sheets.*.fields' => ['sometimes', 'array'],
            'sheets.*.fields.*.key' => ['required_with:sheets.*.fields', 'string'],
            'sheets.*.fields.*.label' => ['sometimes', 'string'],
            'sheets.*.fields.*.path' => ['sometimes', 'nullable', 'string'],
            'sheets.*.fields.*.type' => ['sometimes', 'nullable', 'string'],
            'sheets.*.fields.*.format' => ['sometimes', 'nullable', 'string'],
            'sheets.*.fields.*.default' => ['sometimes'],
            'sheets.*.fields.*.locale' => ['sometimes', 'nullable', 'string', 'max:8'],
        ];
    }

    public function toDefinition(): ExportDefinitionDto
    {
        return ExportDefinitionDto::fromArray($this->validated());
    }
}
