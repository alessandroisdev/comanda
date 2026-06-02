<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class MenuCategoryController extends Controller
{
    /**
     * Retorna a listagem de categorias em formato JSON com cache e otimização.
     */
    public function index(Request $request)
    {
        $companyId = $request->get('company_id', 1);
        $cacheKey = "menu_categories_json:{$companyId}";

        try {
            $categories = Cache::remember($cacheKey, 600, function () use ($companyId) {
                return Category::where('company_id', $companyId)
                    ->where('status', 'active')
                    ->orderBy('sort_order')
                    ->get();
            });
        } catch (\Throwable $e) {
            $categories = Category::where('company_id', $companyId)
                ->where('status', 'active')
                ->orderBy('sort_order')
                ->get();
        }

        return response()->json([
            'success' => true,
            'data' => $categories,
        ])->header('Cache-Control', 'max-age=60, public');
    }
}
