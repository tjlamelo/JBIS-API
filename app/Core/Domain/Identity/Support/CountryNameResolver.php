<?php

declare(strict_types=1);

namespace App\Core\Domain\Identity\Support;

use App\Core\Domain\Location\Models\Country;
use Illuminate\Database\Eloquent\Builder;

final class CountryNameResolver
{
    /** @var array<string, string> */
    private const CITY_COUNTRY = [
        'yaoundé' => 'Cameroun',
        'yaounde' => 'Cameroun',
        'douala' => 'Cameroun',
        'bafoussam' => 'Cameroun',
        'garoua' => 'Cameroun',
        'paris' => 'France',
        'lyon' => 'France',
        'marseille' => 'France',
        'bruxelles' => 'Belgique',
        'brussels' => 'Belgium',
        'abidjan' => "Côte d'Ivoire",
        'dakar' => 'Sénégal',
        'libreville' => 'Gabon',
    ];

    /** @var array<string, string> */
    private const ALIASES = [
        'cmr' => 'CM',
        'cameroun' => 'CM',
        'cameroon' => 'CM',
        'france' => 'FR',
        'côte d\'ivoire' => 'CI',
        'cote d\'ivoire' => 'CI',
        'ivory coast' => 'CI',
        'sénégal' => 'SN',
        'senegal' => 'SN',
        'gabon' => 'GA',
        'belgique' => 'BE',
        'belgium' => 'BE',
        'uae' => 'AE',
        'united arab emirates' => 'AE',
        'emirates' => 'AE',
        'emirats' => 'AE',
        'émirats' => 'AE',
        'dubai' => 'AE',
        'abu dhabi' => 'AE',
    ];

    public function resolveId(?string $countryName): ?int
    {
        $name = trim((string) $countryName);
        if ($name === '') {
            return null;
        }

        $byCode = Country::query()
            ->where('code', strtoupper($name))
            ->value('id');

        if ($byCode !== null) {
            return (int) $byCode;
        }

        $aliasCode = self::ALIASES[strtolower($name)] ?? null;
        if ($aliasCode !== null) {
            $id = Country::query()->where('code', $aliasCode)->value('id');

            return $id !== null ? (int) $id : null;
        }

        $id = Country::query()
            ->where(function (Builder $query) use ($name): void {
                $query->where('name->fr', $name)
                    ->orWhere('name->en', $name)
                    ->orWhere('name->fr', 'like', '%'.$name.'%')
                    ->orWhere('name->en', 'like', '%'.$name.'%');
            })
            ->value('id');

        return $id !== null ? (int) $id : null;
    }

    public function inferFromCity(?string $cityName): ?string
    {
        $city = strtolower(trim((string) $cityName));
        if ($city === '') {
            return null;
        }

        return self::CITY_COUNTRY[$city] ?? null;
    }

    public function resolveNameFromCity(?string $cityName): ?string
    {
        return $this->inferFromCity($cityName);
    }
}
