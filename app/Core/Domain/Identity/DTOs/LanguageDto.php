<?php
declare(strict_types=1);

namespace App\Core\User\Dto;

use App\Core\Shared\Interfaces\IDto;
use Illuminate\Http\Request;

class LanguageDto implements IDto
{
    public function __construct(
        public readonly int $language_id,
        public readonly string $proficiency_level,
        public ?bool $is_approved = null,
        public ?int $approved_by = null,
    ) {
    }

    /**
     * Crée le DTO à partir d'une requête HTTP
     */
    public static function fromRequest(Request $request): self
    {
        return new self(
            (int) $request->input('language_id', 0),
            $request->input('proficiency_level', ''),
            $request->boolean('is_approved'),
            $request->filled('approved_by') ? (int) $request->input('approved_by') : null
        );
    }

    /**
     * Crée le DTO à partir d'un tableau
     */
    public static function fromArray(array $data): self
    {
        return new self(
            (int) ($data['language_id'] ?? 0),
            $data['proficiency_level'] ?? '',
            $data['is_approved'] ?? null,
            $data['approved_by'] ?? null
        );
    }

    /**
     * Retourne les données du DTO sous forme de tableau
     */
    public function toArray(): array
    {
        return [
            'language_id' => $this->language_id,
            'proficiency_level' => $this->proficiency_level,
            'is_approved' => $this->is_approved,
            'approved_by' => $this->approved_by,
        ];
    }
}
