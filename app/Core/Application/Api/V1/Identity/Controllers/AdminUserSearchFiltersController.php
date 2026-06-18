<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Identity\Controllers;

use App\Core\Application\Api\Responses\BaseResponse;
use App\Core\Domain\Identity\Models\User;
use App\Core\Domain\Identity\Support\AdminUserSearchFilterSchema;
use App\Core\Infrastructure\Cache\AppCache;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

final class AdminUserSearchFiltersController extends Controller
{
    public function __construct(
        private readonly AdminUserSearchFilterSchema $schema,
        private readonly AppCache $cache,
    ) {}

    public function __invoke(): JsonResponse
    {
        $this->authorize('viewAny', User::class);

        $locale = app()->getLocale();

        $options = $this->cache->remember(
            $this->cache->referenceKey('admin_user_search_options_v2', $locale),
            3600,
            fn () => $this->schema->optionLists($locale),
        );

        return BaseResponse::ok([
            'filters' => $this->schema->filterDefinitions(),
            'options' => $options,
            'sort' => $this->schema->sortOptions(),
        ])->toJsonResponse();
    }
}
