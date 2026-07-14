<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Actions;

use App\Core\Domain\Identity\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

final class CompleteRequiredPasswordChangeAction
{
    /**
     * @param  array{current_password: string, password: string, password_confirmation: string}  $input
     *
     * @throws ValidationException
     */
    public function execute(User $user, array $input): void
    {
        if (! $user->must_change_password) {
            throw ValidationException::withMessages([
                'password' => [__('Aucun changement de mot de passe n\'est requis pour ce compte.')],
            ]);
        }

        if (! Hash::check($input['current_password'], (string) $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => [__('Le mot de passe actuel est incorrect.')],
            ]);
        }

        if ($input['current_password'] === $input['password']) {
            throw ValidationException::withMessages([
                'password' => [__('Choisissez un mot de passe différent du mot de passe temporaire.')],
            ]);
        }

        $user->forceFill([
            'password' => $input['password'],
            'must_change_password' => false,
        ])->save();

        $user->tokens()->delete();
    }
}
