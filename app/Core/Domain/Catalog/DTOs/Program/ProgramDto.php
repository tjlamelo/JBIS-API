<?php

declare(strict_types=1);

namespace App\Core\Domain\Catalog\DTOs\Program;

use App\Core\Domain\Catalog\States\ProgramStatus;
use App\Core\Domain\Shared\Interfaces\IDto;
use Illuminate\Http\Request;

readonly class ProgramDto implements IDto
{
    /**
     * @param  list<string>  $provided_keys
     * @param  array<string, string>  $name
     * @param  array<string, string>|null  $description
     * @param  array<string, string>|null  $slug
     * @param  list<array{required_document_id?: int, is_mandatory?: bool, sort_order?: int}>  $required_documents
     * @param  list<array{language_id?: int, is_mandatory?: bool}>  $language_requirements
     */
    public function __construct(
        public array $provided_keys,
        public array $name,
        public ?array $description = null,
        public ?array $slug = null,
        public ?int $geographic_zone_id = null,
        public ?int $procedure_duration = null,
        public string $duration_unit = 'months',
        public ?int $age_min = null,
        public ?int $age_max = null,
        public bool $is_featured = false,
        public bool $is_urgent = false,
        public int $views_count = 0,
        public ?array $image_media = null,
        public string $status = 'PUBLISHED',
        public ?string $start_date = null,
        public ?string $end_date = null,
        public ?string $published_at = null,
        public ?int $user_id = null,
        public array $required_documents = [],
        public array $language_requirements = [],
    ) {}

    public static function fromRequest(Request $request): self
    {
        $data = $request->all();
        $data['provided_keys'] = array_keys($data);
        $data['user_id'] = $request->user()?->id;

        return self::fromArray($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $providedKeys = $data['provided_keys'] ?? array_keys($data);

        return new self(
            provided_keys: array_values($providedKeys),
            name: $data['name'] ?? [],
            description: $data['description'] ?? null,
            slug: $data['slug'] ?? null,
            geographic_zone_id: array_key_exists('geographic_zone_id', $data)
                ? (($data['geographic_zone_id'] ?? null) !== null && $data['geographic_zone_id'] !== ''
                    ? (int) $data['geographic_zone_id']
                    : null)
                : null,
            procedure_duration: array_key_exists('procedure_duration', $data) && $data['procedure_duration'] !== null && $data['procedure_duration'] !== ''
                ? (int) $data['procedure_duration']
                : null,
            duration_unit: (string) ($data['duration_unit'] ?? 'months'),
            age_min: array_key_exists('age_min', $data) && $data['age_min'] !== null && $data['age_min'] !== ''
                ? (int) $data['age_min']
                : null,
            age_max: array_key_exists('age_max', $data) && $data['age_max'] !== null && $data['age_max'] !== ''
                ? (int) $data['age_max']
                : null,
            is_featured: (bool) ($data['is_featured'] ?? false),
            is_urgent: (bool) ($data['is_urgent'] ?? false),
            views_count: max(0, (int) ($data['views_count'] ?? 0)),
            image_media: $data['image_media'] ?? null,
            status: (string) ($data['status'] ?? ProgramStatus::Published->value),
            start_date: $data['start_date'] ?? null,
            end_date: $data['end_date'] ?? null,
            published_at: $data['published_at'] ?? null,
            user_id: isset($data['user_id']) ? (int) $data['user_id'] : null,
            required_documents: is_array($data['required_documents'] ?? null) ? $data['required_documents'] : [],
            language_requirements: is_array($data['language_requirements'] ?? null) ? $data['language_requirements'] : [],
        );
    }

    public function toArray(): array
    {
        $vars = get_object_vars($this);
        unset($vars['provided_keys']);

        if ($this->provided_keys === []) {
            return array_filter($vars, static fn ($value) => $value !== null);
        }

        return array_intersect_key($vars, array_flip($this->provided_keys));
    }

    public function has(string $key): bool
    {
        return in_array($key, $this->provided_keys, true);
    }
}
