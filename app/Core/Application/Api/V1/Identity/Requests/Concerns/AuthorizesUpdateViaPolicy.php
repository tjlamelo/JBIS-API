<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Identity\Requests\Concerns;

trait AuthorizesUpdateViaPolicy
{
    abstract protected function routeParameter(): string;

    public function authorize(): bool
    {
        $model = $this->route($this->routeParameter());

        return $model !== null && ($this->user()?->can('update', $model) ?? false);
    }
}
