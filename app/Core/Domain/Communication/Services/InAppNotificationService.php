<?php

declare(strict_types=1);

namespace App\Core\Domain\Communication\Services;

use App\Core\Domain\Communication\Enums\InAppNotificationType;
use App\Core\Domain\Communication\Models\UserNotification;
use App\Core\Domain\Identity\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;

final class InAppNotificationService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function notify(
        User|int $user,
        InAppNotificationType|string $type,
        string $title,
        string $body,
        array $data = [],
        ?string $dedupeKey = null,
        ?string $actionUrl = null,
    ): ?UserNotification {
        $userId = $user instanceof User ? $user->id : $user;
        $typeValue = $type instanceof InAppNotificationType ? $type->value : $type;
        $key = $dedupeKey ?? sprintf('%s:%s', $typeValue, now()->toDateString());

        try {
            return UserNotification::query()->create([
                'user_id' => $userId,
                'type' => $typeValue,
                'title' => $title,
                'body' => $body,
                'data' => $data === [] ? null : $data,
                'dedupe_key' => $key,
                'action_url' => $actionUrl,
            ]);
        } catch (QueryException $exception) {
            if ($this->isUniqueViolation($exception)) {
                return UserNotification::query()
                    ->where('user_id', $userId)
                    ->where('dedupe_key', $key)
                    ->first();
            }

            throw $exception;
        }
    }

    /**
     * @param  Collection<int, User|int>|iterable<User|int>  $users
     * @param  array<string, mixed>  $data
     * @return array{created: int, skipped: int}
     */
    public function notifyMany(
        iterable $users,
        InAppNotificationType|string $type,
        string $title,
        string $body,
        array $data = [],
        ?string $dedupeKey = null,
        ?string $actionUrl = null,
    ): array {
        $created = 0;
        $skipped = 0;

        foreach ($users as $user) {
            $before = UserNotification::query()
                ->where('user_id', $user instanceof User ? $user->id : $user)
                ->where('dedupe_key', $dedupeKey ?? sprintf(
                    '%s:%s',
                    $type instanceof InAppNotificationType ? $type->value : $type,
                    now()->toDateString()
                ))
                ->exists();

            $notification = $this->notify($user, $type, $title, $body, $data, $dedupeKey, $actionUrl);

            if ($notification === null || $before) {
                $skipped++;
            } else {
                $created++;
            }
        }

        return compact('created', 'skipped');
    }

    private function isUniqueViolation(QueryException $exception): bool
    {
        $sqlState = (string) ($exception->errorInfo[0] ?? '');
        $driverCode = (int) ($exception->errorInfo[1] ?? 0);

        return $sqlState === '23000' || $driverCode === 1062 || str_contains(strtolower($exception->getMessage()), 'unique');
    }
}
