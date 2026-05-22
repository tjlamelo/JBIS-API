<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Identity\Requests\Concerns;

use App\Core\Domain\Identity\Models\User;

trait AuthorizesStoreViaPolicy
{
    /** @return class-string */
    abstract protected function policyModel(): string;

    public function targetUserId(): int
    {
        $auth = $this->user();

        return $this->filled('user_id')
            ? (int) $this->integer('user_id')
            : (int) $auth?->id;
    }

    public function authorize(): bool
    {
        $auth = $this->user();
        if ($auth === null) {
            return false;
        }

        $target = User::query()->find($this->targetUserId());
        if ($target === null) {
            return false;
        }

        return $auth->can('store', [$this->policyModel(), $target]);
    }
}
