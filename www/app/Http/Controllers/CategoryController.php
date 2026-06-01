<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Category\CreateCategoryAction;
use App\Actions\Category\DeleteCategoryAction;
use App\Actions\Category\UpdateCategoryAction;
use App\DataTables\CategoriesDataTable;
use App\DTOs\Category\CreateCategoryDTO;
use App\DTOs\Category\UpdateCategoryDTO;
use App\Http\Requests\Category\CreateCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Models\Category;
use App\Models\Company;
use App\Models\Employee;
use App\Services\CategoryService;
use App\Services\DataTables\DataTableQueryService;
use App\Services\DataTables\DataTableResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function __construct(
        private readonly CategoryService $service,
        private readonly DataTableQueryService $dataTableService
    ) {}

    /**
     * Exibe a listagem principal das categorias.
     */
    public function index(): View
    {
        Gate::authorize('viewAny', Category::class);

        return view('admin.categories.index');
    }

    /**
     * Endpoint para alimentar o DataTables Server-Side de categorias.
     */
    public function datatable(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Category::class);

        $provider = new CategoriesDataTable;

        $user = $request->user();
        $result = $this->dataTableService->process($request, $provider, function ($query) use ($user) {
            if ($user instanceof Employee) {
                $query->where('categories.company_id', $user->company_id);
            }
        });

        return DataTableResponseFactory::create($result);
    }

    /**
     * Exibe o formulário de cadastro de categorias.
     */
    public function create(): View
    {
        Gate::authorize('create', Category::class);

        $companies = Company::orderBy('trade_name')->get();

        return view('admin.categories.create', compact('companies'));
    }

    /**
     * Armazena uma nova categoria no banco.
     */
    public function store(CreateCategoryRequest $request, CreateCategoryAction $action)
    {
        Gate::authorize('create', Category::class);

        $dto = CreateCategoryDTO::fromArray($request->validated());
        $category = $action->execute($dto);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('categories.messages.create_success'),
                'category_uuid' => $category->uuid,
            ], 201);
        }

        return redirect()->route('admin.categories.index')
            ->with('success', __('categories.messages.create_success'));
    }

    /**
     * Exibe os detalhes de uma categoria.
     */
    public function show(string $uuid): View
    {
        $category = $this->service->findByUuid($uuid);
        Gate::authorize('view', $category);

        return view('admin.categories.show', compact('category'));
    }

    /**
     * Exibe o formulário de edição de categorias.
     */
    public function edit(string $uuid): View
    {
        $category = $this->service->findByUuid($uuid);
        Gate::authorize('update', $category);

        $companies = Company::orderBy('trade_name')->get();

        return view('admin.categories.edit', compact('category', 'companies'));
    }

    /**
     * Atualiza os dados de uma categoria no banco.
     */
    public function update(UpdateCategoryRequest $request, string $uuid, UpdateCategoryAction $action)
    {
        $category = $this->service->findByUuid($uuid);
        Gate::authorize('update', $category);

        $dto = UpdateCategoryDTO::fromArray($request->validated());
        $updatedCategory = $action->execute($category, $dto);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('categories.messages.update_success'),
                'category_uuid' => $updatedCategory->uuid,
            ]);
        }

        return redirect()->route('admin.categories.index')
            ->with('success', __('categories.messages.update_success'));
    }

    /**
     * Remove uma categoria de forma definitiva ou lógica.
     */
    public function destroy(string $uuid, DeleteCategoryAction $action): JsonResponse
    {
        $category = $this->service->findByUuid($uuid);
        Gate::authorize('delete', $category);

        $action->execute($category);

        return response()->json([
            'success' => true,
            'message' => __('categories.messages.delete_success'),
        ]);
    }
}
