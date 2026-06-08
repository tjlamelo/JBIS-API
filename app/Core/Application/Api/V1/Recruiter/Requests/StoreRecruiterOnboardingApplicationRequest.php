<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Recruiter\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

final class StoreRecruiterOnboardingApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) config('services.recruiter.onboarding_enabled', true);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $user = $this->user();
        $emailUnique = Rule::unique('users', 'email');

        return [
            'company_name' => ['required', 'string', 'max:255'],
            'legal_form' => ['nullable', 'string', 'max:120'],
            'registration_number' => ['nullable', 'string', 'max:120'],
            'contact_name' => ['required', 'string', 'max:255'],
            'contact_email' => ['required', 'email', 'max:255', $user ? 'prohibited' : $emailUnique],
            'contact_phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:500'],
            'website' => ['nullable', 'url', 'max:255'],
            'activity_description' => ['nullable', 'string', 'max:5000'],
            'desired_slug' => ['nullable', 'string', 'max:80', 'alpha_dash', Rule::unique('recruiter_organizations', 'slug')],
            'password' => $user ? ['prohibited'] : ['required', 'confirmed', Password::defaults()],
            'documents' => ['nullable', 'array', 'max:10'],
            'documents.*' => ['file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,doc,docx'],
            'document_types' => ['nullable', 'array'],
            'document_types.*' => ['string', 'max:64'],
        ];
    }
}
