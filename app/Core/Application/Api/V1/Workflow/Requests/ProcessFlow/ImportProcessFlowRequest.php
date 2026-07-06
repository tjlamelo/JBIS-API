<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Workflow\Requests\ProcessFlow;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class ImportProcessFlowRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'max:10240'],
            'format' => ['sometimes', Rule::in(['excel', 'xlsx', 'json'])],
            'commit' => ['sometimes', 'boolean'],
        ];
    }

    public function resolvedFormat(): string
    {
        $explicit = strtolower((string) $this->input('format', ''));
        if (in_array($explicit, ['excel', 'xlsx'], true)) {
            return 'excel';
        }
        if ($explicit === 'json') {
            return 'json';
        }

        $name = strtolower((string) $this->file('file')?->getClientOriginalExtension());

        return $name === 'json' ? 'json' : 'excel';
    }
}
