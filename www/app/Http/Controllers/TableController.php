<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Table\ChangeTableStatusAction;
use App\Actions\Table\CreateTableAction;
use App\Actions\Table\DeleteTableAction;
use App\Actions\Table\UpdateTableAction;
use App\DataTables\TablesDataTable;
use App\DTOs\Table\CreateTableDTO;
use App\DTOs\Table\UpdateTableDTO;
use App\Enums\TableStatusEnum;
use App\Models\Table;
use App\Services\DataTables\DataTableQueryService;
use App\Services\DataTables\DataTableResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class TableController extends Controller
{
    public function __construct(
        private readonly DataTableQueryService $dataTableService
    ) {}

    public function index(): View
    {
        Gate::authorize('viewAny', Table::class);

        return view('admin.tables.index');
    }

    public function datatable(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Table::class);

        $provider = new TablesDataTable;
        $result = $this->dataTableService->process($request, $provider);

        return DataTableResponseFactory::create($result);
    }

    public function create(): View
    {
        Gate::authorize('create', Table::class);

        return view('admin.tables.create');
    }

    public function store(Request $request, CreateTableAction $action)
    {
        Gate::authorize('create', Table::class);

        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'unit_id' => 'required|exists:company_units,id',
            'code' => 'required|string|max:50',
            'name' => 'required|string|max:100',
            'capacity' => 'required|integer|min:1',
            'sector' => 'required|string|max:100',
            'status' => 'required|string|in:available,occupied,reserved,blocked,cleaning',
            'sort_order' => 'nullable|integer',
        ]);

        $dto = CreateTableDTO::fromArray($validated);
        $table = $action->execute($dto);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'table_uuid' => $table->uuid,
            ], 201);
        }

        return redirect()->route('admin.tables.index')
            ->with('success', 'Mesa criada com sucesso.');
    }

    public function show(string $uuid): View
    {
        /** @var Table $table */
        $table = Table::where('uuid', $uuid)->firstOrFail();
        Gate::authorize('view', $table);

        return view('admin.tables.show', compact('table'));
    }

    public function edit(string $uuid): View
    {
        /** @var Table $table */
        $table = Table::where('uuid', $uuid)->firstOrFail();
        Gate::authorize('update', $table);

        return view('admin.tables.edit', compact('table'));
    }

    public function update(Request $request, string $uuid, UpdateTableAction $action)
    {
        /** @var Table $table */
        $table = Table::where('uuid', $uuid)->firstOrFail();
        Gate::authorize('update', $table);

        $validated = $request->validate([
            'code' => 'required|string|max:50',
            'name' => 'required|string|max:100',
            'capacity' => 'required|integer|min:1',
            'sector' => 'required|string|max:100',
            'sort_order' => 'nullable|integer',
        ]);

        $dto = UpdateTableDTO::fromArray($validated);
        $action->execute($table, $dto);

        return redirect()->route('admin.tables.index')
            ->with('success', 'Mesa atualizada com sucesso.');
    }

    public function destroy(string $uuid, DeleteTableAction $action): JsonResponse
    {
        /** @var Table $table */
        $table = Table::where('uuid', $uuid)->firstOrFail();
        Gate::authorize('delete', $table);

        $action->execute($table);

        return response()->json([
            'success' => true,
            'message' => 'Mesa excluída com sucesso.',
        ]);
    }

    public function changeStatus(Request $request, string $uuid, ChangeTableStatusAction $action): JsonResponse
    {
        /** @var Table $table */
        $table = Table::where('uuid', $uuid)->firstOrFail();
        Gate::authorize('update', $table);

        $request->validate([
            'status' => 'required|string|in:available,occupied,reserved,blocked,cleaning',
        ]);

        $status = TableStatusEnum::from($request->input('status'));
        $action->execute($table, $status);

        return response()->json([
            'success' => true,
            'message' => 'Status da mesa alterado com sucesso.',
        ]);
    }
}
