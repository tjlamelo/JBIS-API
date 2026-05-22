<?php

declare(strict_types=1);

namespace App\Core\Domain\Analytics\Services;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class SyncGa4AnalyticsAction
{
    public function __construct(
        private readonly Ga4ClientFactory $clientFactory,
    ) {}

    public function execute(CarbonImmutable $date): void
    {
        $client = $this->clientFactory->make();

        $dateRange = [
            'startDate' => $date->toDateString(),
            'endDate' => $date->toDateString(),
        ];

        DB::transaction(function () use ($client, $dateRange, $date): void {
            $overview = $client->runReport(
                $dateRange,
                [
                    'activeUsers',
                    'newUsers',
                    'totalUsers',
                    'sessions',
                    'engagedSessions',
                    'engagementRate',
                    'screenPageViews',
                    'averageSessionDuration',
                ],
                [],
                1,
            );

            $metricValues = $this->extractFirstRowMetricValues($overview);

            $now = now();
            $overviewPayload = [
                'active_users' => (int) ($metricValues['activeUsers'] ?? 0),
                'new_users' => (int) ($metricValues['newUsers'] ?? 0),
                'total_users' => (int) ($metricValues['totalUsers'] ?? 0),
                'sessions' => (int) ($metricValues['sessions'] ?? 0),
                'engaged_sessions' => (int) ($metricValues['engagedSessions'] ?? 0),
                'engagement_rate' => (float) ($metricValues['engagementRate'] ?? 0),
                'page_views' => (int) ($metricValues['screenPageViews'] ?? 0),
                'avg_session_duration' => (float) ($metricValues['averageSessionDuration'] ?? 0),
                'updated_at' => $now,
            ];

            $existingOverview = DB::table('ga4_daily_overview')
                ->where('date', $date->toDateString())
                ->first(['id']);

            if ($existingOverview) {
                DB::table('ga4_daily_overview')
                    ->where('date', $date->toDateString())
                    ->update($overviewPayload);
            } else {
                DB::table('ga4_daily_overview')->insert([
                    'date' => $date->toDateString(),
                    ...$overviewPayload,
                    'created_at' => $now,
                ]);
            }

            $pages = $client->runReport(
                $dateRange,
                ['screenPageViews', 'activeUsers'],
                ['pagePathPlusQueryString'],
                200,
            );

            $pageRows = [];
            foreach (($pages['rows'] ?? []) as $row) {
                $pagePath = (string) (($row['dimensionValues'][0]['value'] ?? '') ?: '/');
                $pageViews = (int) (($row['metricValues'][0]['value'] ?? 0) ?: 0);
                $activeUsers = (int) (($row['metricValues'][1]['value'] ?? 0) ?: 0);

                $pageRows[] = [
                    'date' => $date->toDateString(),
                    'page_path' => mb_substr($pagePath, 0, 512),
                    'page_views' => $pageViews,
                    'active_users' => $activeUsers,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            if ($pageRows !== []) {
                DB::table('ga4_daily_pages')->upsert(
                    $pageRows,
                    ['date', 'page_path'],
                    ['page_views', 'active_users', 'updated_at'],
                );
            }

            $acquisition = $client->runReport(
                $dateRange,
                ['sessions', 'activeUsers'],
                ['sessionSource', 'sessionMedium', 'sessionCampaignName'],
                200,
            );

            $acqRows = [];
            foreach (($acquisition['rows'] ?? []) as $row) {
                $source = (string) (($row['dimensionValues'][0]['value'] ?? '') ?: '(direct)');
                $medium = (string) (($row['dimensionValues'][1]['value'] ?? '') ?: '(none)');
                $campaignRaw = (string) (($row['dimensionValues'][2]['value'] ?? '') ?: '');
                $campaign = $campaignRaw !== '' ? $campaignRaw : null;
                $sessions = (int) (($row['metricValues'][0]['value'] ?? 0) ?: 0);
                $activeUsers = (int) (($row['metricValues'][1]['value'] ?? 0) ?: 0);

                $acqRows[] = [
                    'date' => $date->toDateString(),
                    'source' => mb_substr($source, 0, 128),
                    'medium' => mb_substr($medium, 0, 128),
                    'campaign' => $campaign ? mb_substr($campaign, 0, 256) : null,
                    'sessions' => $sessions,
                    'active_users' => $activeUsers,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            if ($acqRows !== []) {
                DB::table('ga4_daily_acquisition')->upsert(
                    $acqRows,
                    ['date', 'source', 'medium', 'campaign'],
                    ['sessions', 'active_users', 'updated_at'],
                );
            }
        });
    }

    private function extractFirstRowMetricValues(array $report): array
    {
        $headers = $report['metricHeaders'] ?? [];
        $rows = $report['rows'] ?? [];
        if (! is_array($rows) || count($rows) === 0) {
            return [];
        }

        $metricValues = $rows[0]['metricValues'] ?? [];
        $map = [];

        foreach ($headers as $idx => $header) {
            $name = (string) ($header['name'] ?? '');
            if ($name === '') {
                continue;
            }
            $map[$name] = $metricValues[$idx]['value'] ?? null;
        }

        return $map;
    }
}
