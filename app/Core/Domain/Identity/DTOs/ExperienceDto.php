<?php 
declare(strict_types=1);

namespace App\Core\User\Dto;

use App\Core\Shared\Interfaces\IDto;
use Illuminate\Http\Request;

class ExperienceDto implements IDto
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $user_id,
        public readonly string $offer_title,
        public readonly string $company_name,
        public readonly string $start_date,
        public readonly ?string $end_date,
        public readonly ?string $responsibilities,
        public readonly ?string $achievements,
        public ?bool $is_approved = null,
        public ?int $approved_by = null,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            id: $request->input('id'),
            user_id: (int) $request->input('user_id'),
            offer_title: (string) $request->input('offer_title'),
            company_name: (string) $request->input('company_name'),
            start_date: (string) $request->input('start_date'),
            end_date: $request->input('end_date'),
            responsibilities: $request->input('responsibilities'),
            achievements: $request->input('achievements'),
            is_approved: $request->boolean('is_approved'),
            approved_by: $request->filled('approved_by') ? (int) $request->input('approved_by') : null,
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            user_id: (int) $data['user_id'],
            offer_title: (string) $data['offer_title'],
            company_name: (string) $data['company_name'],
            start_date: (string) $data['start_date'],
            end_date: $data['end_date'] ?? null,
            responsibilities: $data['responsibilities'] ?? null,
            achievements: $data['achievements'] ?? null,
            is_approved: $data['is_approved'] ?? null,
            approved_by: $data['approved_by'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id'              => $this->id,
            'user_id'         => $this->user_id,
            'offer_title'     => $this->offer_title,
            'company_name'    => $this->company_name,
            'start_date'      => $this->start_date,
            'end_date'        => $this->end_date,
            'responsibilities'=> $this->responsibilities,
            'achievements'    => $this->achievements,
            'is_approved'     => $this->is_approved,
            'approved_by'     => $this->approved_by,
        ];
    }
}
