<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Identity\Requests;

use App\Core\Domain\Identity\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateAdminUserProfileWizardStepRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->route('user');

        return $user instanceof User && $this->user()?->can('moderateProfile', $user) === true;
    }

    public function rules(): array
    {
        $step = (string) $this->route('step');
        /** @var User|null $targetUser */
        $targetUser = $this->route('user');
        $profileId = $targetUser?->profile?->id;

        return match ($step) {
            'personal' => [
                'first_name' => ['nullable', 'string', 'max:50'],
                'last_name' => ['nullable', 'string', 'max:50'],
                'date_of_birth' => ['nullable', 'date'],
                'place_of_birth' => ['nullable', 'string', 'max:25'],
                'nationality_country_id' => ['nullable', 'integer', 'exists:countries,id'],
                'residence_city_id' => ['nullable', 'integer', 'exists:cities,id'],
                'gender' => ['nullable', Rule::in(['M', 'F'])],
                'marital_status' => ['nullable', Rule::in(['SINGLE', 'MARRIED', 'DIVORCED', 'WIDOWED'])],
                'number_of_children' => ['nullable', 'integer', 'min:0', 'max:20'],
            ],
            'contact' => [
                'address' => ['nullable', 'string', 'max:50'],
                'phone_number2' => [
                    'nullable',
                    'string',
                    'max:20',
                    Rule::unique('user_profiles', 'phone_number2')->ignore($profileId),
                ],
                'phone_number3' => [
                    'nullable',
                    'string',
                    'max:20',
                    Rule::unique('user_profiles', 'phone_number3')->ignore($profileId),
                ],
                'email_institutional' => ['nullable', 'email', 'max:100'],
            ],
            'professional' => [
                'total_years_of_experience' => ['nullable', 'integer', 'min:0', 'max:80'],
                'matricule' => ['nullable', 'string', 'max:50'],
                'agency_id' => ['nullable', 'integer', 'exists:agencies,id'],
                'bio' => ['nullable', 'string', 'max:5000'],
            ],
            'documents' => [
                'pictures' => ['nullable', 'array', 'max:3'],
                'pictures.*' => ['nullable'],
                'pictures.*.public_url' => ['sometimes', 'nullable', 'string', 'max:2048'],
                'pictures.*.cloudinary_id' => ['sometimes', 'nullable', 'string', 'max:255'],
                'pictures.*.local_optimized_path' => ['sometimes', 'nullable', 'string', 'max:500'],
                'pictures.*.local_raw_path' => ['sometimes', 'nullable', 'string', 'max:500'],
            ],
            default => [],
        };
    }
}
