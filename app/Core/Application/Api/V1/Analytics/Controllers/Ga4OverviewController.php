<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Analytics\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Ga4OverviewController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $from = (string) $request->query('from', now()->subDays(29)->toDateString());
        $to = (string) $request->query('to', now()->toDateString());

        $rows = DB::table('ga4_daily_overview')
            ->whereBetween('date', [$from, $to])
            ->orderBy('date')
            ->get([
                'date',
                'active_users',
                'new_users',
                'total_users',
                'sessions',
                'engaged_sessions',
                'engagement_rate',
                'page_views',
                'avg_session_duration',
            ]);

        return response()->json([
            'from' => $from,
            'to' => $to,
            'items' => $rows,
        ]);
    }
}
