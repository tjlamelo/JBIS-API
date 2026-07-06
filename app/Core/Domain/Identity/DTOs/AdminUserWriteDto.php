<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\DTOs;

readonly class AdminUserWriteDto
{
    /**
     * @param  list<string>|null  $roles
     */
    public function __construct(
        public ?string $name = null,
        public ?string $email = null,
        public ?string $phoneNumber1 = null,
        public ?string $password = null,
        public ?bool $active = null,
        public ?array $roles = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: isset($data['name']) ? (string) $data['name'] : null,
            email: isset($data['email']) ? (string) $data['email'] : null,
            phoneNumber1: array_key_exists('phone_number1', $data)
                ? ($data['phone_number1'] !== null ? (string) $data['phone_number1'] : null)
                : null,
            password: isset($data['password']) ? (string) $data['password'] : null,
            active: array_key_exists('active', $data) ? (bool) $data['active'] : null,
            roles: isset($data['roles']) && is_array($data['roles'])
                ? array_values(array_map('strval', $data['roles']))
                : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toUserAttributes(): array
    {
        $attributes = [];

        if ($this->name !== null) {
            $attributes['name'] = $this->name;
        }
        if ($this->email !== null) {
            $attributes['email'] = $this->email;
        }
        if ($this->phoneNumber1 !== null) {
            $attributes['phone_number1'] = $this->phoneNumber1;
        }
        if ($this->password !== null && $this->password !== '') {
            $attributes['password'] = $this->password;
        }
        if ($this->active !== null) {
            $attributes['active'] = $this->active;
        }

        return $attributes;
    }
}
