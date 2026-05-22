<?php

declare(strict_types=1);

namespace App\Core\Domain\Catalog\Mappers\Shared;

use Illuminate\Database\Eloquent\Model;

final class CatalogTranslatableNameSlugMapper
{
    /**
     * @param  array<string, string>  $name
     */
    public function applyNameAndSlug(
        Model $model,
        array $providedKeys,
        array $name,
        string $slug,
        bool $isCreate,
    ): void {
        $has = static fn (string $k): bool => in_array($k, $providedKeys, true);

        if ($isCreate || $has('name')) {
            if ($name !== []) {
                $model->setTranslations('name', $name);
            }
        }

        if ($isCreate || $has('slug')) {
            $model->slug = $slug;
        }
    }
}
