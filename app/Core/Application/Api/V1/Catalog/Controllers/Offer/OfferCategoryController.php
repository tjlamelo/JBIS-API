<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Catalog\Controllers\Offer;

use App\Http\Controllers\Controller;
use App\Core\Domain\Catalog\Models\OfferCategory; // Ajuste selon ton namespace réel
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OfferCategoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $search = $request->query('search');

        $categories = OfferCategory::query()
            ->where('is_active', true)
            ->when($search, function ($query, $search) {
                // Recherche dans le JSON (Spatie) pour FR et EN
                $query->where('name->fr', 'like', "%{$search}%")
                      ->orWhere('name->en', 'like', "%{$search}%");
            })
            ->select(['id', 'name'])
            ->paginate(15);

        return response()->json($categories);
    }
    // App\Core\Application\Api\V1\Catalog\Controllers\OfferCategoryController.php

public function search(Request $request): JsonResponse
{
    $search = $request->query('q');

    $categories = OfferCategory::query()
        ->where('is_active', true)
        ->when($search, function ($query, $search) {
            // Recherche dans le JSON multilingue
            return $query->where('name->fr', 'like', "%{$search}%")
                         ->orWhere('name->en', 'like', "%{$search}%");
        })
        ->limit(10) // On limite pour l'UX
        ->get();

    return response()->json([
        'data' => $categories->map(fn($cat) => [
            'value' => (string) $cat->id,
            'label' => $cat->name, // Laravel gère déjà le cast JSON
            'icon'  => $cat->icon
        ])
    ]);
}
}