<?php

namespace App\Core\Domain\Identity\Actions;

use App\Core\Domain\Identity\Models\User;

class ResendEmailVerificationAction
{
    public function execute(User $user): string
    {
        if ($user->hasVerifiedEmail()) {
            return __('Email deja verifie.');
        }

        $user->sendEmailVerificationNotification();

        return __('Lien de verification envoye.');
    }
}
