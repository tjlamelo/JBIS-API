<?php

declare(strict_types=1);

namespace App\Core\Domain\Catalog\DTOs;

use App\Core\Domain\Shared\Interfaces\IDto;
use Illuminate\Http\Request;

readonly class ProgramDto implements IDto
{
    public function __construct(
        public array $name,
        public ?array $description,
        public ?array $slug,
        public ?int $geographic_zone_id,
        public float $procedure_cost,
        public string $currency,
        public ?int $procedure_duration,
        public string $duration_unit,
        public ?string $required_age,
        public ?string $language,
        public ?string $image,
        public ?array $meta,
        public string $status,
        public ?string $start_date,
        public ?string $end_date,
        public ?string $published_at,
        public ?int $user_id,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            name: $request->input('name'),
            description: $request->input('description'),
            slug: $request->input('slug'),
            geographic_zone_id: $request->integer('geographic_zone_id') ?: null,
            procedure_cost: (float) $request->input('procedure_cost', 0),
            currency: $request->input('currency', 'XAF'),
            procedure_duration: $request->integer('procedure_duration') ?: null,
            duration_unit: $request->input('duration_unit', 'months'),
            required_age: $request->input('required_age'),
            language: $request->input('language'),
            image: $request->input('image'),
            meta: $request->input('meta'),
            status: $request->input('status', 'active'),
            start_date: $request->input('start_date'),
            end_date: $request->input('end_date'),
            published_at: $request->input('published_at'),
            user_id: $request->user()?->id,
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'] ?? [],
            description: $data['description'] ?? null,
            slug: $data['slug'] ?? null,
            geographic_zone_id: $data['geographic_zone_id'] ?? null,
            procedure_cost: (float) ($data['procedure_cost'] ?? 0),
            currency: $data['currency'] ?? 'XAF',
            procedure_duration: $data['procedure_duration'] ?? null,
            duration_unit: $data['duration_unit'] ?? 'months',
            required_age: $data['required_age'] ?? null,
            language: $data['language'] ?? null,
            image: $data['image'] ?? null,
            meta: $data['meta'] ?? null,
            status: $data['status'] ?? 'active',
            start_date: $data['start_date'] ?? null,
            end_date: $data['end_date'] ?? null,
            published_at: $data['published_at'] ?? null,
            user_id: $data['user_id'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter(get_object_vars($this), static fn($value) => $value !== null);
    }
}