<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Shared\Controllers;

use App\Core\Application\Api\Responses\BaseResponse;
use App\Core\Domain\Shared\Support\EnumCatalog;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class MetaEnumController extends Controller
{
    public function __construct(
        private readonly EnumCatalog $enumCatalog,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $keys = array_values(array_filter(array_map(
            static fn (string $key): string => trim($key),
            explode(',', (string) $request->query('keys', '')),
        )));

        if ($keys === []) {
            $keys = array_keys(EnumCatalog::GROUPS);
        }

        $unknown = array_values(array_diff($keys, array_keys(EnumCatalog::GROUPS)));
        if ($unknown !== []) {
            return BaseResponse::unprocessableEntity(
                message: 'Unknown enum group(s): '.implode(', ', $unknown),
            )->toJsonResponse();
        }

        return BaseResponse::ok([
            'enums' => $this->enumCatalog->resolve($keys, bilingual: true),
        ])->toJsonResponse();
    }
}
