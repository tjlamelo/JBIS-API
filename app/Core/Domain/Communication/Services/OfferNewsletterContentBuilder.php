<?php

declare(strict_types=1);

namespace App\Core\Domain\Communication\Services;

use App\Core\Application\Api\Support\TranslatableColumnResolver;
use App\Core\Domain\Catalog\Models\Offer;
use App\Core\Domain\Catalog\States\OfferStatus;
use App\Core\Domain\Communication\Enums\NewsletterScope;
use App\Core\Domain\Location\Models\Country;
use Illuminate\Support\Collection;

final class OfferNewsletterContentBuilder
{
    /**
     * @return array{
     *   national: list<array<string, mixed>>,
     *   international: list<array<string, mixed>>,
     *   has_national: bool,
     *   has_international: bool
     * }
     */
    public function build(NewsletterScope $scope, string $locale): array
    {
        $limit = (int) config('services.newsletter.max_offers_per_section', 8);
        $cameroonId = $this->cameroonCountryId();

        $national = in_array($scope, [NewsletterScope::National, NewsletterScope::Both], true)
            ? $this->fetchOffers($locale, $cameroonId, true, $limit)
            : collect();

        $international = in_array($scope, [NewsletterScope::International, NewsletterScope::Both], true)
            ? $this->fetchOffers($locale, $cameroonId, false, $limit)
            : collect();

        return [
            'national' => $national->values()->all(),
            'international' => $international->values()->all(),
            'has_national' => $national->isNotEmpty(),
            'has_international' => $international->isNotEmpty(),
        ];
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function fetchOffers(string $locale, ?int $cameroonId, bool $national, int $limit): Collection
    {
        $since = now()->subDays((int) config('services.newsletter.offer_lookback_days', 14));

        $query = Offer::query()
            ->with(['company:id,name', 'country:id,name,code', 'city:id,name'])
            ->where('status', OfferStatus::Published)
            ->where(function ($q): void {
                $q->whereNull('expiration_date')->orWhere('expiration_date', '>=', now());
            })
            ->where('published_at', '>=', $since)
            ->latest('published_at')
            ->limit($limit);

        if ($national) {
            if ($cameroonId === null) {
                return collect();
            }
            $query->where('country_id', $cameroonId);
        } elseif ($cameroonId !== null) {
            $query->whereNotNull('country_id')->where('country_id', '!=', $cameroonId);
        } else {
            $query->whereNotNull('country_id');
        }

        return $query->get()->map(fn (Offer $offer) => $this->mapOffer($offer, $locale));
    }

    /**
     * @return array<string, mixed>
     */
    private function mapOffer(Offer $offer, string $locale): array
    {
        $slug = TranslatableColumnResolver::resolve($offer->slug, $locale);
        $frontend = rtrim((string) config('services.newsletter.frontend_url', env('FRONTEND_URL', 'http://localhost:3000')), '/');

        return [
            'id' => $offer->id,
            'title' => TranslatableColumnResolver::resolve($offer->title, $locale),
            'slug' => $slug,
            'url' => $slug !== '' ? "{$frontend}/offer/{$slug}" : "{$frontend}/offer",
            'company' => $offer->is_company_public ? ($offer->company?->name ?? null) : null,
            'country' => $offer->country
                ? TranslatableColumnResolver::resolve($offer->country->name, $locale)
                : null,
            'city' => $offer->city
                ? TranslatableColumnResolver::resolve($offer->city->name, $locale)
                : null,
            'published_at' => $offer->published_at?->format('d/m/Y'),
        ];
    }

    private function cameroonCountryId(): ?int
    {
        $code = (string) config('services.newsletter.cameroon_country_code', 'CM');

        return Country::query()->where('code', $code)->value('id');
    }
}
