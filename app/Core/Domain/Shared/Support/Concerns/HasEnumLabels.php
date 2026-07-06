<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Support\Concerns;

trait HasEnumLabels
{
    abstract public static function translationKey(): string;

    public function label(?string $locale = null): string
    {
        $key = 'enums.'.static::translationKey().'.'.$this->value.'.label';
        $translated = __($key, locale: $locale);

        if ($translated !== $key) {
            return $translated;
        }

        $fallbackKey = 'enums.'.static::translationKey().'.'.$this->value;

        return (string) __($fallbackKey, locale: $locale);
    }

    public function description(?string $locale = null): ?string
    {
        $key = 'enums.'.static::translationKey().'.'.$this->value.'.description';
        $translated = __($key, locale: $locale);

        return $translated === $key ? null : $translated;
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public static function options(?string $locale = null): array
    {
        return array_map(
            static fn (self $case): array => [
                'value' => $case->value,
                'label' => $case->label($locale),
            ],
            self::cases(),
        );
    }

    /**
     * @return list<array{value: string, label: array{fr: string, en: string}, description?: array{fr: string, en: string}}>
     */
    public static function bilingualOptions(): array
    {
        return array_map(static function (self $case): array {
            $option = [
                'value' => $case->value,
                'label' => [
                    'fr' => $case->label('fr'),
                    'en' => $case->label('en'),
                ],
            ];

            $descriptionFr = $case->description('fr');
            $descriptionEn = $case->description('en');

            if ($descriptionFr !== null || $descriptionEn !== null) {
                $option['description'] = [
                    'fr' => $descriptionFr ?? $descriptionEn ?? '',
                    'en' => $descriptionEn ?? $descriptionFr ?? '',
                ];
            }

            return $option;
        }, self::cases());
    }
}
