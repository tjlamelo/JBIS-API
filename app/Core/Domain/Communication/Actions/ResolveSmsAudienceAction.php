<?php

namespace App\Core\Domain\Communication\Actions;

use App\Core\Domain\Communication\DTOs\SmsAudienceDto;
use App\Core\Domain\Communication\Exceptions\InvalidPhoneNumberException;
use App\Core\Domain\Communication\Services\PhoneNumberNormalizerService;
use App\Core\Domain\Identity\Models\User;
use Illuminate\Support\Collection;

class ResolveSmsAudienceAction
{
    public function __construct(
        private readonly PhoneNumberNormalizerService $phoneNumberNormalizerService,
    ) {}

    /**
     * @return Collection<int, array{user_id: int|null, phone_number: string}>
     */
    public function execute(SmsAudienceDto $audience): Collection
    {
        $query = User::query();

        if ($audience->mode === 'users') {
            $query->forUserIds($audience->userIds);
        } else {
            $query
                ->forRoles($audience->roles)
                ->forAgencies($audience->agencyIds);
        }

        $userRecipients = $query->get()
            ->map(function (User $user): ?array {
                try {
                    $normalized = $this->phoneNumberNormalizerService->normalize((string) ($user->phone_number1 ?? ''));
                } catch (InvalidPhoneNumberException) {
                    return null;
                }

                return [
                    'user_id' => $user->id,
                    'phone_number' => $normalized,
                ];
            })
            ->filter()
            ->values()
            ->all();

        // Force a une Support\Collection (pas une Eloquent Collection), car on
        // manipule ici des tableaux et pas des Model -> evite "getKey() on array".
        $userRecipients = collect($userRecipients);

        $manualRecipients = collect($audience->manualNumbers)
            ->map(function (string $number): ?array {
                try {
                    $normalized = $this->phoneNumberNormalizerService->normalize($number);
                } catch (InvalidPhoneNumberException) {
                    return null;
                }

                return [
                    'user_id' => null,
                    'phone_number' => $normalized,
                ];
            })
            ->filter()
            ->values();

        return $userRecipients
            ->merge($manualRecipients)
            ->unique('phone_number')
            ->values();
    }
}
