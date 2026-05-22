<?php

namespace App\Actions\Fortify;

use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Support\ApplicationRole;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     *
     * @throws ValidationException
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],
            'phone_number1' => ['nullable', 'string', 'max:20', Rule::unique(User::class, 'phone_number1')],
            'password' => $this->passwordRules(),
        ])->validate();

        $user = User::create([
            'name' => $input['name'],
            'user_name' => $input['name'],
            'email' => $input['email'],
            'phone_number1' => $input['phone_number1'] ?? null,
            'password' => Hash::make($input['password']),
            'active' => true,
        ]);

        $user->assignRole(ApplicationRole::CANDIDATE);

        return $user;
    }
}
