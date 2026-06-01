<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class MenuProductController extends Controller
{
    /**
     * Retorna a listagem de produtos com busca e filtros operacionais em JSON.
     */
    public function index(Request $request)
    {
        $companyId = $request->get('company_id', 1);
        $categoryId = $request->get('category_id');
        $search = $request->get('q');

        $query = Product::where('company_id', $companyId)
            ->where('status', 'active');

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        $products = $query->orderBy('sort_order')->get();

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }

    /**
     * Detalha um produto específico com combos e adicionais públicos.
     */
    public function show(string $uuid)
    {
        $product = Cache::remember("product_json:{$uuid}", 600, function () use ($uuid) {
            return Product::where('uuid', $uuid)
                ->where('status', 'active')
                ->firstOrFail();
        });

        return response()->json([
            'success' => true,
            'data' => $product,
        ]);
    }
}
