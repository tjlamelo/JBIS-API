<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Identity\Controllers;

use App\Core\Application\Api\Responses\BaseResponse;
use App\Core\Application\Api\V1\Identity\Requests\Archive\StoreArchiveRequest;
use App\Core\Application\Api\V1\Identity\Resources\ArchiveResource;
use App\Core\Application\Api\V1\Identity\Support\ScopesUserOwnedIndex;
use App\Core\Domain\Identity\Models\Archive;
use App\Core\Domain\Identity\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class ArchiveController extends Controller
{
    use ScopesUserOwnedIndex;

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Archive::class);

        $query = Archive::query();
        $this->scopeIndexToUser($request, $query, 'archive');

        $items = $query->latest()->paginate((int) $request->integer('per_page', 20));

        return BaseResponse::ok([
            'data' => ArchiveResource::collection($items->items()),
            'meta' => [
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'per_page' => $items->perPage(),
                'total' => $items->total(),
            ],
        ])->toJsonResponse();
    }

    public function store(StoreArchiveRequest $request): JsonResponse
    {
        $targetUser = User::query()->findOrFail($request->targetUserId());
        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension() ?: 'bin');
        $storedName = sprintf(
            'archives/%d/%s.%s',
            $targetUser->id,
            Str::uuid()->toString(),
            $extension,
        );

        $disk = (string) $request->input('disk', 'local');
        Storage::disk($disk)->putFileAs(
            dirname($storedName),
            $file,
            basename($storedName),
        );

        $archive = Archive::query()->create([
            'user_id' => $targetUser->id,
            'original_name' => $file->getClientOriginalName(),
            'stored_name' => $storedName,
            'file_type' => (string) $request->input('file_type', $extension),
            'extension' => $extension,
            'mime_type' => $file->getMimeType() ?? 'application/octet-stream',
            'size' => $file->getSize() ?: 0,
            'category' => $request->input('category'),
            'description' => $request->input('description'),
            'disk' => $disk,
            'is_public' => $request->boolean('is_public'),
        ]);

        return BaseResponse::created([
            'message' => __('Archive enregistrée.'),
            'archive' => new ArchiveResource($archive),
        ])->toJsonResponse();
    }

    public function destroy(Request $request, Archive $archive): JsonResponse
    {
        $this->authorize('delete', $archive);

        if ($archive->stored_name) {
            Storage::disk($archive->disk)->delete($archive->stored_name);
        }

        $archive->delete();

        return BaseResponse::ok(['message' => __('Archive supprimée.')])->toJsonResponse();
    }

    public function download(Request $request, Archive $archive): StreamedResponse|JsonResponse
    {
        $this->authorize('view', $archive);

        if (! $archive->stored_name || ! Storage::disk($archive->disk)->exists($archive->stored_name)) {
            return BaseResponse::notFound(__('Fichier introuvable.'))->toJsonResponse();
        }

        return Storage::disk($archive->disk)->download($archive->stored_name, $archive->original_name);
    }
}
