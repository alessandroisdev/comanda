<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Product\CreateProductAction;
use App\Actions\Product\DeleteProductAction;
use App\Actions\Product\UpdateProductAction;
use App\DataTables\ProductsDataTable;
use App\DTOs\Product\CreateProductDTO;
use App\DTOs\Product\UpdateProductDTO;
use App\Http\Requests\Product\CreateProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Models\Category;
use App\Models\Company;
use App\Models\Employee;
use App\Models\Product;
use App\Services\DataTables\DataTableQueryService;
use App\Services\DataTables\DataTableResponseFactory;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(
        private readonly ProductService $service,
        private readonly DataTableQueryService $dataTableService
    ) {}

    /**
     * Exibe a listagem principal dos produtos.
     */
    public function index(): View
    {
        Gate::authorize('viewAny', Product::class);

        return view('admin.products.index');
    }

    /**
     * Endpoint para alimentar o DataTables Server-Side de produtos.
     */
    public function datatable(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Product::class);

        $provider = new ProductsDataTable;

        $user = $request->user();
        $result = $this->dataTableService->process($request, $provider, function ($query) use ($user) {
            if ($user instanceof Employee) {
                $query->where('products.company_id', $user->company_id);
            }
        });

        return DataTableResponseFactory::create($result);
    }

    /**
     * Exibe o formulário de cadastro de produtos.
     */
    public function create(): View
    {
        Gate::authorize('create', Product::class);

        $companies = Company::orderBy('trade_name')->get();
        $categories = Category::orderBy('name')->get();

        return view('admin.products.create', compact('companies', 'categories'));
    }

    /**
     * Armazena um novo produto no banco.
     */
    public function store(CreateProductRequest $request, CreateProductAction $action)
    {
        Gate::authorize('create', Product::class);

        $dto = CreateProductDTO::fromArray($request->validated());
        $product = $action->execute($dto);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('products.messages.create_success'),
                'product_uuid' => $product->uuid,
            ], 201);
        }

        return redirect()->route('admin.products.index')
            ->with('success', __('products.messages.create_success'));
    }

    /**
     * Exibe os detalhes de um produto.
     */
    public function show(string $uuid): View
    {
        $product = $this->service->findByUuid($uuid);
        Gate::authorize('view', $product);

        return view('admin.products.show', compact('product'));
    }

    /**
     * Exibe o formulário de edição de produtos.
     */
    public function edit(string $uuid): View
    {
        $product = $this->service->findByUuid($uuid);
        Gate::authorize('update', $product);

        $companies = Company::orderBy('trade_name')->get();
        // Carrega categorias apenas da empresa do produto para segurança e consistência
        $categories = Category::where('company_id', $product->company_id)->orderBy('name')->get();

        return view('admin.products.edit', compact('product', 'companies', 'categories'));
    }

    /**
     * Atualiza os dados de um produto no banco.
     */
    public function update(UpdateProductRequest $request, string $uuid, UpdateProductAction $action)
    {
        $product = $this->service->findByUuid($uuid);
        Gate::authorize('update', $product);

        $dto = UpdateProductDTO::fromArray($request->validated());
        $updatedProduct = $action->execute($product, $dto);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('products.messages.update_success'),
                'product_uuid' => $updatedProduct->uuid,
            ]);
        }

        return redirect()->route('admin.products.index')
            ->with('success', __('products.messages.update_success'));
    }

    /**
     * Remove um produto.
     */
    public function destroy(string $uuid, DeleteProductAction $action): JsonResponse
    {
        $product = $this->service->findByUuid($uuid);
        Gate::authorize('delete', $product);

        $action->execute($product);

        return response()->json([
            'success' => true,
            'message' => __('products.messages.delete_success'),
        ]);
    }
}
