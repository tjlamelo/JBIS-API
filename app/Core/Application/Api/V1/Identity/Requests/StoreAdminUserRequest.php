<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Identity\Requests;

use App\Core\Domain\Identity\Enums\CareerIntent;
use App\Core\Domain\Identity\Enums\Civility;
use App\Core\Domain\Identity\Enums\ProfileType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Validator;

final class StoreAdminUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $firstName = trim((string) $this->input('first_name', ''));
        $lastName = trim((string) $this->input('last_name', ''));
        $name = trim((string) $this->input('name', ''));

        if ($name === '' && ($firstName !== '' || $lastName !== '')) {
            $this->merge([
                'name' => trim($firstName.' '.$lastName),
            ]);
        }

        $email = $this->input('email');
        if (! is_string($email) || trim($email) === '') {
            $this->merge(['email' => null]);
        } else {
            $this->merge(['email' => trim($email)]);
        }

        $gender = $this->input('gender');
        $civility = $this->input('civility');
        if (is_string($gender) && $gender !== '') {
            $this->merge([
                'civility' => Civility::normalize(
                    is_string($civility) ? $civility : null,
                    $gender,
                ),
            ]);
        }
    }

    public function rules(): array
    {
        $gender = $this->input('gender');
        $genderValue = is_string($gender) ? $gender : null;
        $passwordProvided = filled($this->input('password'));

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')],
            'phone_number1' => ['nullable', 'string', 'max:20', Rule::unique('users', 'phone_number1')],
            'password' => $passwordProvided
                ? ['required', 'confirmed', Password::defaults()]
                : ['nullable', 'string'],
            'password_confirmation' => $passwordProvided ? ['required', 'string'] : ['nullable', 'string'],
            'active' => ['sometimes', 'boolean'],
            'roles' => ['sometimes', 'array', 'min:1'],
            'roles.*' => ['string', Rule::exists('roles', 'name')],
            'first_name' => ['required', 'string', 'max:50'],
            'last_name' => ['required', 'string', 'max:50'],
            'civility' => ['nullable', 'string', Rule::in(Civility::valuesForGender($genderValue))],
            'date_of_birth' => [
                'nullable',
                'date',
                'before_or_equal:'.now()->subYears(18)->toDateString(),
                'after:1940-01-01',
            ],
            'place_of_birth' => ['nullable', 'string', 'max:25'],
            'nationality_country_id' => ['nullable', 'integer', 'exists:countries,id'],
            'residence_city' => ['nullable', 'string', 'max:120'],
            'career_intent' => ['nullable', 'string', Rule::in(CareerIntent::values())],
            'profile_type' => ['nullable', 'string', Rule::in(ProfileType::values())],
            'highest_education_level_id' => ['nullable', 'integer', 'exists:education_levels,id'],
            'gender' => ['nullable', Rule::in(['M', 'F'])],
            'marital_status' => ['nullable', Rule::in(['SINGLE', 'MARRIED', 'DIVORCED', 'WIDOWED'])],
            'number_of_children' => ['nullable', 'integer', 'min:0', 'max:20'],
            'send_account_email' => ['sometimes', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $gender = $this->input('gender');
            $civility = $this->input('civility');

            if (! is_string($civility) || $civility === '') {
                return;
            }

            if (! Civility::isAllowedForGender($civility, is_string($gender) ? $gender : null)) {
                $validator->errors()->add(
                    'civility',
                    __('La civilité ne correspond pas au genre sélectionné.'),
                );
            }
        });
    }
}
