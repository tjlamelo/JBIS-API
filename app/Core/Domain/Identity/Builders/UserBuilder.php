<?php

namespace App\Core\Domain\Identity\Builders;

use Illuminate\Database\Eloquent\Builder;

class UserBuilder extends Builder
{
    public function findByLogin(string $login): self
    {
        $normalized = trim($login);
        if ($normalized === '') {
            return $this->whereKey(-1);
        }

        if (filter_var($normalized, FILTER_VALIDATE_EMAIL)) {
            return $this->where('email', $normalized);
        }

        $phoneCandidates = $this->buildPhoneCandidates($normalized);

        if ($phoneCandidates === []) {
            return $this->whereKey(-1);
        }

        return $this->where(function (Builder $query) use ($phoneCandidates): void {
            $query->whereIn('phone_number1', $phoneCandidates)
                ->orWhereIn(
                    $query->getQuery()->raw("REGEXP_REPLACE(phone_number1, '[^0-9]+', '')"),
                    $phoneCandidates
                );
        });
    }

    /**
     * @return array<int, string>
     */
    private function buildPhoneCandidates(string $login): array
    {
        $digitsOnly = preg_replace('/\D+/', '', $login) ?? '';
        $candidates = [$login];

        if ($digitsOnly !== '') {
            $candidates[] = $digitsOnly;
            $candidates[] = '+'.$digitsOnly;

            if (str_starts_with($digitsOnly, '237')) {
                $local = substr($digitsOnly, 3);
                if ($local !== '') {
                    $candidates[] = $local;
                    $candidates[] = '0'.$local;
                }
            } elseif (str_starts_with($digitsOnly, '0')) {
                $withoutZero = ltrim($digitsOnly, '0');
                if ($withoutZero !== '') {
                    $candidates[] = $withoutZero;
                    $candidates[] = '237'.$withoutZero;
                    $candidates[] = '+237'.$withoutZero;
                }
            } elseif (strlen($digitsOnly) <= 9) {
                $candidates[] = '237'.$digitsOnly;
                $candidates[] = '+237'.$digitsOnly;
                $candidates[] = '0'.$digitsOnly;
            }
        }

        return array_values(array_unique(array_filter($candidates)));
    }

    public function withValidEmail(): self
    {
        return $this->whereNotNull('email')->where('email', '!=', '');
    }

    /**
     * @param array<int, int> $userIds
     */
    public function forUserIds(array $userIds): self
    {
        if ($userIds === []) {
            return $this->whereKey(-1);
        }

        return $this->whereIn('id', $userIds);
    }

    /**
     * @param array<int, string> $roles
     */
    public function forRoles(array $roles): self
    {
        if ($roles === []) {
            return $this;
        }

        return $this->whereHas('roles', function (Builder $roleQuery) use ($roles): void {
            $roleQuery->whereIn('name', $roles);
        });
    }

    /**
     * @param array<int, int> $agencyIds
     */
    public function forAgencies(array $agencyIds): self
    {
        if ($agencyIds === []) {
            return $this;
        }

        return $this->whereHas('profile', function (Builder $profileQuery) use ($agencyIds): void {
            $profileQuery->whereIn('agencies_id', $agencyIds);
        });
    }

    public function uniqueByEmail(): self
    {
        return $this->whereIn('id', function ($query): void {
            $query->selectRaw('MIN(id)')
                ->from('users')
                ->whereNotNull('email')
                ->where('email', '!=', '')
                ->groupBy('email');
        });
    }
}
