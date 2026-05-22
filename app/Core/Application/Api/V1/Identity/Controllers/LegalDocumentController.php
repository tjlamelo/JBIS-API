<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Identity\Controllers;

use App\Core\Application\Api\Responses\BaseResponse;
use App\Core\Application\Api\V1\Identity\Resources\LegalDocumentResource;
use App\Core\Domain\Identity\Models\LegalDocument;
use App\Core\Domain\Identity\Support\ConsentType;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

final class LegalDocumentController extends Controller
{
    public function current(Request $request): JsonResponse
    {
        $type = $request->query('type');

        $query = LegalDocument::query()->where('is_current', true);

        if (is_string($type) && $type !== '') {
            $request->validate(['type' => ['string', Rule::in(ConsentType::ALL)]]);
            $query->where('type', $type);
        }

        $documents = $query->orderBy('type')->get();

        return BaseResponse::ok([
            'documents' => LegalDocumentResource::collection($documents),
        ])->toJsonResponse();
    }

    public function show(string $type, string $version): JsonResponse
    {
        $document = LegalDocument::query()
            ->where('type', $type)
            ->where('version', $version)
            ->first();

        if (! $document) {
            return BaseResponse::notFound([
                'message' => __('Document introuvable.'),
            ])->toJsonResponse();
        }

        return BaseResponse::ok([
            'document' => new LegalDocumentResource($document),
        ])->toJsonResponse();
    }
}
