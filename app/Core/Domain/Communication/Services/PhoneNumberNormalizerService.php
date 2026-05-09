<?php

namespace App\Core\Domain\Communication\Services;

use App\Core\Domain\Communication\Exceptions\InvalidPhoneNumberException;

class PhoneNumberNormalizerService
{
    public function normalize(string $phoneNumber): string
    {
        $digits = preg_replace('/\D+/', '', $phoneNumber) ?? '';
        if ($digits === '') {
            throw new InvalidPhoneNumberException('Numero invalide.');
        }

        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        }

        if (str_starts_with($digits, '0')) {
            $digits = '237'.substr($digits, 1);
        } elseif (! str_starts_with($digits, '237') && strlen($digits) <= 9) {
            $digits = '237'.$digits;
        }

        if (! str_starts_with($digits, '237') || strlen($digits) < 11 || strlen($digits) > 12) {
            throw new InvalidPhoneNumberException('Numero invalide.');
        }

        return '+'.$digits;
    }
}
