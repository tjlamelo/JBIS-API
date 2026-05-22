<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Actions\User;

use App\Core\Domain\Identity\Actions\ForgotPasswordAction;
use App\Core\Domain\Identity\Models\User;

final class SendAdminUserPasswordResetAction
{
    public function __construct(
        private readonly ForgotPasswordAction $forgotPasswordAction,
    ) {}

    public function execute(User $user): string
    {
        return $this->forgotPasswordAction->execute($user->email);
    }
}
