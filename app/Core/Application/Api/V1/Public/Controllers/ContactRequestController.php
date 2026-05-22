<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Public\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContactRequestController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        return response()->json([
            'message' => 'This endpoint is deprecated. Use /public/appointments instead.',
        ], 410);
    }
}
