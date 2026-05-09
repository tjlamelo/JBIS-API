<?php
declare(strict_types=1);

namespace App\Core\User\Dto;

use App\Core\Shared\Interfaces\IDto;
use Illuminate\Http\Request;

class UserUpdateDto implements IDto
{
 
    public string $user_name;
    public string $email;

    public function __construct( string $user_name, string $email)
    {
     
        $this->user_name = $user_name;
        $this->email = $email;
    }

    public static function fromRequest(Request $request): self
    {
        return new self(
         
            user_name: $request->input('user_name'),
            email: $request->input('email')
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
           
            user_name: $data['user_name'] ?? '',
            email: $data['email'] ?? ''
        );
    }

    public function toArray(): array
    {
        return array_filter([
            
            'user_name' => $this->user_name,
            'email'     => $this->email,
        ], fn ($value) => !is_null($value));
    }
}
