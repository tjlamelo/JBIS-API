<?php

declare(strict_types=1);

namespace App\Core\Application\Api\Support;

final class EnumOptionPresenter
{
    /**
     * @param  class-string<\BackedEnum&\App\Core\Domain\Shared\Support\Contracts\LocalizableBackedEnum>  $enumClass
     * @return array{value: string, label: string}|null
     */
    public static function present(?string $value, string $enumClass, ?string $locale = null): ?array
    {
        if ($value === null || $value === '') {
            return null;
        }

        $case = $enumClass::tryFrom($value);
        if ($case === null) {
            return [
                'value' => $value,
                'label' => $value,
            ];
        }

        return [
            'value' => $case->value,
            'label' => $case->label($locale),
        ];
    }
}
