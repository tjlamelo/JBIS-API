<?php

declare(strict_types=1);

namespace App\Core\Domain\Catalog\DTOs;

use App\Core\Domain\Shared\Interfaces\IDto;
use Illuminate\Http\Request;

readonly class CompanyDto implements IDto
{
    public function __construct(
       
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            
        );
    }

    public function toArray(): array
    {
        return array_filter(get_object_vars($this), static fn($value) => $value !== null);
    }
}