<?php

namespace App\Core\Domain\Communication\Contracts;

use App\Core\Domain\Communication\DTOs\SmsDispatchResultDto;

interface SmsProvider
{
    public function send(string $phoneNumber, string $message, ?string $senderId = null): SmsDispatchResultDto;
}
