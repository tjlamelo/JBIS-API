<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Public\Controllers;

use App\Core\Domain\Communication\Support\JbisMailbox;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

final class PublicMailboxController extends Controller
{
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'data' => [
                'domain' => JbisMailbox::domain(),
                'addresses' => JbisMailbox::publicAddresses(),
            ],
        ]);
    }
}
