<?php

namespace App\Core\Domain\Identity\Actions;

use App\Core\Domain\Identity\Models\User;
use Illuminate\Auth\Events\Verified;

class VerifyEmailAction
{
    public function execute(int $id, string $hash): bool
    {
        $user = User::query()->find($id);
        if (! $user || ! hash_equals($hash, sha1($user->getEmailForVerification()))) {
            return false;
        }

        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            event(new Verified($user));
        }

        return true;
    }
}
