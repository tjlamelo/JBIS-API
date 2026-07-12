<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Support;

use App\Core\Domain\Identity\Models\User;
use Illuminate\Support\Str;

final class PlaceholderUserEmail
{
    public static function domain(): string
    {
        return (string) config('identity.placeholder_email_domain', 'jbis.cm');
    }

    public static function generate(?string $firstName, ?string $lastName): string
    {
        $first = self::slugPart($firstName) ?: 'prenom';
        $last = self::slugPart($lastName) ?: 'nom';
        $domain = self::domain();
        $base = $first.'.'.$last;
        $email = $base.'@'.$domain;

        if (! User::query()->where('email', $email)->exists()) {
            return $email;
        }

        $i = 2;
        do {
            $email = $base.$i.'@'.$domain;
            $i++;
        } while (User::query()->where('email', $email)->exists());

        return $email;
    }

    private static function slugPart(?string $value): string
    {
        $slug = Str::slug((string) $value, '');

        return strtolower($slug);
    }
}
