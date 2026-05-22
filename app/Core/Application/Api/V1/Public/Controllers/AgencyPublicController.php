<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Public\Controllers;

use App\Core\Domain\Catalog\Models\Agency;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class AgencyPublicController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $items = Agency::query()
            ->where('is_active', true)
            ->orderBy('id')
            ->get(['id', 'name', 'address', 'phones', 'email']);

        return response()->json([
            'items' => $items,
        ]);
    }
}
