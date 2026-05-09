<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Catalog\Controllers;

use App\Http\Controllers\Controller;
use App\Core\Domain\Catalog\Models\Company; 
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $search = $request->query('search');

        $companies = Company::query()
            ->where('is_approved', true)
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->select(['id', 'name', 'logo'])
            ->paginate(15);

        return response()->json($companies);
    }
}