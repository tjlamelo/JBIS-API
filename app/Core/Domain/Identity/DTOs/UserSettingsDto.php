<?php
declare(strict_types=1);

namespace App\Core\User\Dto;

use App\Core\Shared\Interfaces\IDto;
use Illuminate\Http\Request;

class UserSettingsDto implements IDto
{
    public static function fromRequest(Request $request): self
    {

    }


    public static function fromArray(array $data): self
    {

    }
    public function toArray(): array
    {

    }
}