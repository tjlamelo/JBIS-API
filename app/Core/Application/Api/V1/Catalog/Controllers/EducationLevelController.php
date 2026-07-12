<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Catalog\Controllers;

use App\Core\Domain\Catalog\Models\EducationLevel;
use App\Core\Domain\Shared\Support\TranslatableCatalogSearch;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EducationLevelController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $search = $request->query('search');

        $items = EducationLevel::query()
            ->when($search, function ($query, $search): void {
                TranslatableCatalogSearch::apply($query, 'name', (string) $search, ['slug']);
            })
            ->select(['id', 'name', 'slug'])
            ->orderByRaw("FIELD(slug, 'none', 'cep', 'bepc', 'cap', 'bac', 'bts', 'dut', 'deug', 'bachelor', 'licence_pro', 'master', 'master_pro', 'master_rech', 'mba', 'doctorate', 'postdoc', 'hdr')")
            ->paginate(20);

        return response()->json($items);
    }
}
