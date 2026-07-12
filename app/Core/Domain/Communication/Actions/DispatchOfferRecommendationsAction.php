<?php

declare(strict_types=1);

namespace App\Core\Domain\Communication\Actions;

use App\Core\Domain\Catalog\Models\Offer;
use App\Core\Domain\Communication\Enums\InAppNotificationType;
use App\Core\Domain\Communication\Jobs\GenerateOfferRecommendationsForUserJob;
use App\Core\Domain\Communication\Services\InAppNotificationService;
use App\Core\Domain\Communication\Support\LocalizedCopy;
use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Support\ApplicationRole;
use App\Core\Domain\Shared\Ai\Intel\OfferProfileMatchingService;
use Illuminate\Support\Facades\Log;
use Throwable;

final class DispatchOfferRecommendationsAction
{
    private const OFFER_LIMIT = 40;

    private const TOP_N = 5;

    private const MIN_SCORE = 0.45;

    public function __construct(
        private readonly OfferProfileMatchingService $matching,
        private readonly InAppNotificationService $notifications,
    ) {}

    /**
     * @return array{queued: int, processed: int, notified: int, skipped: int, failed: int}
     */
    public function execute(?int $limitUsers = null, bool $sync = false): array
    {
        $stats = ['queued' => 0, 'processed' => 0, 'notified' => 0, 'skipped' => 0, 'failed' => 0];

        $query = User::query()
            ->role(ApplicationRole::CANDIDATE)
            ->whereNotNull('email_verified_at')
            ->whereHas('profile')
            ->orderBy('id');

        if ($limitUsers !== null) {
            $query->limit($limitUsers);
        }

        $users = $query->get(['id']);

        foreach ($users as $user) {
            if ($sync) {
                $result = $this->recommendForUserId((int) $user->id);
                $stats['processed']++;
                $stats[$result]++;
            } else {
                GenerateOfferRecommendationsForUserJob::dispatch((int) $user->id);
                $stats['queued']++;
            }
        }

        return $stats;
    }

    /**
     * @return 'notified'|'skipped'|'failed'
     */
    public function recommendForUserId(int $userId): string
    {
        $user = User::query()->with(['profile', 'educations', 'experiences', 'languages.language', 'certifications', 'settings'])->find($userId);
        if ($user === null) {
            return 'skipped';
        }

        $offers = Offer::query()
            ->published()
            ->notExpired()
            ->with('trade:id,name')
            ->orderByDesc('published_at')
            ->limit(self::OFFER_LIMIT)
            ->get(['id', 'trade_id', 'description', 'published_at']);

        if ($offers->isEmpty()) {
            return 'skipped';
        }

        /** @var list<array{id:int,title:string,description:string}> $summaries */
        $summaries = $offers->map(static function (Offer $offer): array {
            $titles = $offer->resolvedTitleTranslations();
            $title = (string) ($titles['fr'] ?? $titles['en'] ?? ('Offre #'.$offer->id));
            $description = (string) ($offer->getTranslation('description', 'fr')
                ?: $offer->getTranslation('description', 'en')
                ?: '');

            return [
                'id' => (int) $offer->id,
                'title' => mb_substr($title, 0, 180),
                'description' => mb_substr(strip_tags($description), 0, 400),
            ];
        })->values()->all();

        try {
            $result = $this->matching->recommendForUser($user, $summaries);
        } catch (Throwable $exception) {
            Log::warning('Offer profile matching failed', [
                'user_id' => $userId,
                'message' => $exception->getMessage(),
            ]);

            return 'failed';
        }

        $recommendations = $result['recommendations'] ?? $result['offers'] ?? [];
        if (! is_array($recommendations) || $recommendations === []) {
            return 'skipped';
        }

        $ranked = collect($recommendations)
            ->map(static function ($row): ?array {
                if (! is_array($row)) {
                    return null;
                }
                $id = (int) ($row['offer_id'] ?? $row['id'] ?? 0);
                $score = (float) ($row['fit_score'] ?? $row['score'] ?? 0);

                return $id > 0 ? [
                    'offer_id' => $id,
                    'fit_score' => $score,
                    'reason' => $row['rationale'] ?? $row['reason'] ?? null,
                ] : null;
            })
            ->filter()
            ->filter(static fn (array $row): bool => $row['fit_score'] >= self::MIN_SCORE)
            ->sortByDesc('fit_score')
            ->take(self::TOP_N)
            ->values()
            ->all();

        if ($ranked === []) {
            return 'skipped';
        }

        $count = count($ranked);
        $week = now('Africa/Douala')->format('o-\WW');
        $locale = LocalizedCopy::userLocale($user);
        $title = $count === 1
            ? LocalizedCopy::line('notifications.offer_recommendation.title_one', $locale)
            : LocalizedCopy::line('notifications.offer_recommendation.title_many', $locale, ['count' => $count]);
        $body = LocalizedCopy::line('notifications.offer_recommendation.body', $locale);

        $this->notifications->notify(
            $user,
            InAppNotificationType::OfferRecommendation,
            $title,
            $body,
            [
                'recommendations' => $ranked,
                'source' => 'offer_profile_matching',
                'locale' => $locale,
            ],
            "offer_recommendation:{$week}",
            '/offers',
        );

        return 'notified';
    }
}
