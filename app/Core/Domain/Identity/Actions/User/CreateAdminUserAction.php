<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Actions\User;

use App\Core\Domain\Communication\Actions\NotifyAdminCreatedAccountAction;
use App\Core\Domain\Communication\Actions\NotifyStaffWelcomeAction;
use App\Core\Domain\Identity\Actions\Settings\EnsureUserSettingsAction;
use App\Core\Domain\Identity\DTOs\AdminUserWriteDto;
use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Models\UserProfile;
use App\Core\Domain\Identity\Support\ApplicationRole;
use App\Core\Domain\Identity\Support\PlaceholderUserEmail;
use Illuminate\Support\Facades\DB;

final class CreateAdminUserAction
{
    public function __construct(
        private readonly NotifyStaffWelcomeAction $notifyStaffWelcome,
        private readonly NotifyAdminCreatedAccountAction $notifyAdminCreatedAccount,
        private readonly EnsureUserSettingsAction $ensureUserSettings,
    ) {}

    /**
     * @return array{user: User, plain_password: string, email_is_placeholder: bool, account_email_sent: bool}
     */
    public function execute(AdminUserWriteDto $dto): array
    {
        $plainPassword = $dto->resolvedPassword();
        $email = $dto->email !== null && trim($dto->email) !== '' ? strtolower(trim($dto->email)) : null;
        $emailIsPlaceholder = false;

        if ($email === null) {
            $email = PlaceholderUserEmail::generate($dto->firstName, $dto->lastName);
            $emailIsPlaceholder = true;
        }

        $resolvedDto = AdminUserWriteDto::fromArray([
            ...$this->dtoToArray($dto),
            'email' => $email,
            'password' => $plainPassword,
            'email_is_placeholder' => $emailIsPlaceholder,
            'send_account_email' => $dto->sendAccountEmail,
        ]);

        $sendAccountEmail = $resolvedDto->sendAccountEmail === true && ! $emailIsPlaceholder;

        $user = DB::transaction(function () use ($resolvedDto, $emailIsPlaceholder, $sendAccountEmail): User {
            $attributes = $resolvedDto->toUserAttributes();
            $attributes['active'] = $resolvedDto->active ?? true;
            $attributes['must_change_password'] = $sendAccountEmail;

            if (! $emailIsPlaceholder) {
                $attributes['email_verified_at'] = now();
            }

            /** @var User $user */
            $user = User::query()->create($attributes);

            $roles = $resolvedDto->roles ?? [ApplicationRole::CANDIDATE];
            if ($roles !== []) {
                $user->syncRoles($roles);
            }

            if ($resolvedDto->hasProfileData()) {
                UserProfile::query()->updateOrCreate(
                    ['user_id' => $user->id],
                    $resolvedDto->toProfileAttributes(),
                );
            }

            $this->ensureUserSettings->execute($user);

            return $user->load(['roles:id,name', 'profile.approver:id,name', 'trades:id,name,slug,category_id', 'trades.category:id,name,slug']);
        });

        $this->notifyStaffWelcome->ifBecameStaff($user, [], $resolvedDto->roles ?? [ApplicationRole::CANDIDATE]);

        if ($sendAccountEmail) {
            $this->notifyAdminCreatedAccount->execute($user, $plainPassword);
        }

        return [
            'user' => $user,
            'plain_password' => $plainPassword,
            'email_is_placeholder' => $emailIsPlaceholder,
            'account_email_sent' => $sendAccountEmail && $user->canReceiveEmail(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function dtoToArray(AdminUserWriteDto $dto): array
    {
        return [
            'name' => $dto->name,
            'email' => $dto->email,
            'phone_number1' => $dto->phoneNumber1,
            'password' => $dto->password,
            'active' => $dto->active,
            'roles' => $dto->roles,
            'first_name' => $dto->firstName,
            'last_name' => $dto->lastName,
            'civility' => $dto->civility,
            'date_of_birth' => $dto->dateOfBirth,
            'place_of_birth' => $dto->placeOfBirth,
            'nationality_country_id' => $dto->nationalityCountryId,
            'residence_city' => $dto->residenceCity,
            'career_intent' => $dto->careerIntent,
            'profile_type' => $dto->profileType,
            'highest_education_level_id' => $dto->highestEducationLevelId,
            'gender' => $dto->gender,
            'marital_status' => $dto->maritalStatus,
            'number_of_children' => $dto->numberOfChildren,
            'email_is_placeholder' => $dto->emailIsPlaceholder,
            'send_account_email' => $dto->sendAccountEmail,
        ];
    }
}
