<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Public\Controllers;

use App\Core\Domain\Communication\Models\DiscoverySource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class DiscoverySourceController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $items = DiscoverySource::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('label')
            ->get(['id', 'key', 'label']);

        return response()->json([
            'items' => $items,
        ]);
    }
}
