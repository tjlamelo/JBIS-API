<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Identity\Controllers;

use App\Core\Application\Api\Responses\BaseResponse;
use App\Core\Application\Api\V1\Identity\Requests\PublishLegalDocumentRequest;
use App\Core\Application\Api\V1\Identity\Resources\LegalDocumentResource;
use App\Core\Domain\Identity\Actions\Legal\PublishLegalDocumentAction;
use App\Core\Domain\Identity\Models\LegalDocument;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class AdminLegalDocumentController extends Controller
{
    public function __construct(
        private readonly PublishLegalDocumentAction $publishLegalDocument,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorizeAdmin($request);

        $documents = LegalDocument::query()
            ->orderByDesc('effective_at')
            ->paginate((int) $request->integer('per_page', 20));

        return BaseResponse::ok([
            'documents' => LegalDocumentResource::collection($documents->items()),
            'meta' => [
                'current_page' => $documents->currentPage(),
                'last_page' => $documents->lastPage(),
                'total' => $documents->total(),
            ],
        ])->toJsonResponse();
    }

    public function store(PublishLegalDocumentRequest $request): JsonResponse
    {
        $data = $request->validated();

        $document = $this->publishLegalDocument->execute(
            type: (string) $data['type'],
            version: (string) $data['version'],
            title: $data['title'],
            content: $data['content'],
            summary: $data['summary'] ?? null,
            requiresReacceptance: (bool) ($data['requires_reacceptance'] ?? true),
            publisher: $request->user(),
        );

        return BaseResponse::created([
            'message' => __('Document publié. Les utilisateurs devront accepter cette version si requis.'),
            'document' => new LegalDocumentResource($document),
        ])->toJsonResponse();
    }

    private function authorizeAdmin(Request $request): void
    {
        if (! ($request->user()?->can('admin.access') ?? false)) {
            abort(403);
        }
    }
}
