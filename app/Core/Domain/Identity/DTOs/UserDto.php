<?php
declare(strict_types=1);

namespace App\Core\Domain\Identity\DTOs;

use App\Core\Domain\Shared\Interfaces\IDto;
use Illuminate\Http\Request;

readonly class UserDto implements IDto
{
    public function __construct(
        public ?int $id = null,
        public ?string $name = null,
        public ?string $email = null,
        public ?string $password = null,
        public ?bool $active = null,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            id: $request->filled('id') ? (int) $request->input('id') : null,
            name: $request->input('name'),
            email: $request->input('email'),
            password: $request->input('password'),
            active: $request->filled('active') ? $request->boolean('active') : null,
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            name: $data['name'] ?? null,
            email: $data['email'] ?? null,
            password: $data['password'] ?? null,
            active: isset($data['active']) ? (bool) $data['active'] : null,
        );
    }

    public function toArray(): array
    {
        return array_filter(get_object_vars($this), static fn($value) => $value !== null);
    }
}