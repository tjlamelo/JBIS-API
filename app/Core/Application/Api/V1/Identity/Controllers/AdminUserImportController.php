<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Identity\Controllers;

use App\Core\Application\Api\Responses\BaseResponse;
use App\Core\Application\Api\V1\Identity\Requests\ImportAdminUsersRequest;
use App\Core\Application\Api\V1\Identity\Resources\AdminUserResource;
use App\Core\Domain\Identity\Import\AdminUserImportService;
use App\Core\Domain\Identity\Import\AdminUserImportTemplateGenerator;
use App\Core\Domain\Identity\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class AdminUserImportController extends Controller
{
    public function __construct(
        private readonly AdminUserImportService $importService,
        private readonly AdminUserImportTemplateGenerator $templateGenerator,
    ) {}

    public function import(ImportAdminUsersRequest $request): JsonResponse
    {
        $this->authorize('create', User::class);

        $uploaded = $request->file('file');
        $commit = $request->boolean('commit');

        $storedPath = $uploaded->storeAs(
            'imports/admin-users',
            uniqid('users_', true).'.xlsx',
        );

        $absolutePath = Storage::path($storedPath);

        try {
            $result = $this->importService->import($absolutePath, $commit);
        } finally {
            Storage::delete($storedPath);
        }

        if (! $result->success) {
            return BaseResponse::unprocessableEntity(
                data: ['import' => $result->toArray()],
                message: __('Le fichier d\'import contient des erreurs.'),
            )->toJsonResponse();
        }

        $payload = $result->toArray();

        if ($commit && $result->createdUserIds !== []) {
            $users = User::query()
                ->whereIn('id', $result->createdUserIds)
                ->with(['roles:id,name', 'profile.approver:id,name', 'trades:id,name,slug,category_id', 'trades.category:id,name,slug'])
                ->get();

            $payload['users'] = AdminUserResource::collection($users);
        }

        return BaseResponse::ok([
            'message' => $commit
                ? __('Utilisateurs importés avec succès.')
                : __('Validation réussie — aucune donnée écrite (dry-run).'),
            'import' => $payload,
        ])->toJsonResponse();
    }

    public function template(): BinaryFileResponse
    {
        $this->authorize('create', User::class);

        $path = $this->templateGenerator->defaultPath();
        $this->templateGenerator->generate($path);

        return response()->download(
            $path,
            'admin-users-import-template.xlsx',
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
        );
    }
}
