<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Catalog\Controllers\Program;

use App\Core\Application\Api\Responses\BaseResponse;
use App\Core\Application\Api\V1\Catalog\Queries\Program\ProgramIndexQuery;
use App\Core\Application\Api\V1\Catalog\Resources\Program\ProgramResource;
use App\Core\Domain\Catalog\Models\Program;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicProgramController extends Controller
{
    public function index(ProgramIndexQuery $query, Request $request): JsonResponse
    {
        $programs = $query->active()
            ->validDates()
            ->withCount(['offers' => fn ($q) => $q->published()])
            ->paginate((int) $request->integer('per_page', 12))
            ->appends($request->query());

        return BaseResponse::ok([
            'programs' => ProgramResource::collection($programs),
            'meta' => [
                'current_page' => $programs->currentPage(),
                'last_page' => $programs->lastPage(),
                'per_page' => $programs->perPage(),
                'total' => $programs->total(),
            ],
        ])->toJsonResponse();
    }

    public function show(string $slug): JsonResponse
    {
        $program = Program::query()
            ->active()
            ->validDates()
            ->with([
                'requiredDocuments',
                'geographicZone',
                'offers' => fn ($q) => $q->published(),
            ])
            ->where('slug->'.app()->getLocale(), $slug)
            ->orWhere('slug->fr', $slug)
            ->orWhere('slug->en', $slug)
            ->first();

        if (! $program) {
            return BaseResponse::notFound([
                'message' => __('Ce programme est introuvable ou n\'est plus disponible.'),
            ])->toJsonResponse();
        }

        return BaseResponse::ok([
            'program' => new ProgramResource($program),
        ])->toJsonResponse();
    }
}
