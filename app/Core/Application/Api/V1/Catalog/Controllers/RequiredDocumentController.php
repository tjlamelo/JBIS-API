<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Catalog\Controllers;

use App\Core\Application\Api\Support\TranslatableColumnResolver;
use App\Core\Domain\Candidacy\Models\RequiredDocument;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RequiredDocumentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $search = $request->query('search');

        $items = RequiredDocument::query()
            ->when($search, function ($query, $search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('name->fr', 'like', "%{$search}%")
                        ->orWhere('name->en', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%")
                        ->orWhere('type', 'like', "%{$search}%");
                });
            })
            ->select(['id', 'name', 'slug', 'type', 'description'])
            ->orderBy('name->fr')
            ->orderByDesc('id')
            ->paginate($request->integer('per_page', 50));

        $items->getCollection()->transform(static fn (RequiredDocument $doc): array => [
            'id' => $doc->id,
            'name' => [
                'fr' => TranslatableColumnResolver::resolve($doc->name, 'fr'),
                'en' => TranslatableColumnResolver::resolve($doc->name, 'en'),
            ],
            'slug' => $doc->slug,
            'type' => $doc->type,
            'description' => TranslatableColumnResolver::resolve($doc->description),
        ]);

        return response()->json($items);
    }
}
