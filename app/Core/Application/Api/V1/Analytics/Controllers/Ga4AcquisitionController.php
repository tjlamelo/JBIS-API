<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Analytics\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Ga4AcquisitionController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $from = (string) $request->query('from', now()->subDays(29)->toDateString());
        $to = (string) $request->query('to', now()->toDateString());
        $limit = (int) $request->query('limit', 50);

        $rows = DB::table('ga4_daily_acquisition')
            ->select([
                'source',
                'medium',
                'campaign',
                DB::raw('SUM(sessions) as sessions'),
                DB::raw('SUM(active_users) as active_users'),
            ])
            ->whereBetween('date', [$from, $to])
            ->groupBy('source', 'medium', 'campaign')
            ->orderByDesc('sessions')
            ->limit(max(1, min(200, $limit)))
            ->get();

        return response()->json([
            'from' => $from,
            'to' => $to,
            'items' => $rows,
        ]);
    }
}
