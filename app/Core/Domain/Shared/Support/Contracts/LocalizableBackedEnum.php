<?php

declare(strict_types=1);

namespace App\Core\Domain\Shared\Support\Contracts;

interface LocalizableBackedEnum
{
    public static function translationKey(): string;

    public function label(?string $locale = null): string;

    public function description(?string $locale = null): ?string;

    /**
     * @return list<array{value: string, label: string}>
     */
    public static function options(?string $locale = null): array;

    /**
     * @return list<array{value: string, label: array{fr: string, en: string}, description?: array{fr: string, en: string}}>
     */
    public static function bilingualOptions(): array;
}
