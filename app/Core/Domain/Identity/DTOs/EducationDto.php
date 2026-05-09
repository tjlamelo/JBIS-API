<?php
declare(strict_types=1);

namespace App\Core\User\Dto;

use App\Core\Shared\Interfaces\IDto;
use Illuminate\Http\Request;

class EducationDto implements IDto
{
    public function __construct(
        public readonly string $degree,
        public readonly string $institution_name,
        public readonly string $start_date,
        public readonly ?string $end_date,
        public readonly ?string $field_of_study,
        public readonly ?string $grade,
        public ?bool $is_approved = null,
        public ?int $approved_by = null,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            degree: $request->input('degree'),
            institution_name: $request->input('institution_name'),
            start_date: $request->input('start_date'),
            end_date: $request->input('end_date'),
            field_of_study: $request->input('field_of_study'),
            grade: $request->input('grade'),
            is_approved: $request->boolean('is_approved'),
            approved_by: $request->filled('approved_by') ? (int) $request->input('approved_by') : null,
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            degree: $data['degree'] ?? '',
            institution_name: $data['institution_name'] ?? '',
            start_date: $data['start_date'] ?? '',
            end_date: $data['end_date'] ?? null,
            field_of_study: $data['field_of_study'] ?? null,
            grade: $data['grade'] ?? null,
            is_approved: $data['is_approved'] ?? null,
            approved_by: $data['approved_by'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'degree'           => $this->degree,
            'institution_name' => $this->institution_name,
            'start_date'       => $this->start_date,
            'end_date'         => $this->end_date,
            'field_of_study'   => $this->field_of_study,
            'grade'            => $this->grade,
            'is_approved'      => $this->is_approved,
            'approved_by'      => $this->approved_by,
        ];
    }
}
