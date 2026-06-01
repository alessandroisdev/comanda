<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Unit\CreateUnitAction;
use App\Actions\Unit\UpdateUnitAction;
use App\Actions\Unit\DeleteUnitAction;
use App\DataTables\UnitsDataTable;
use App\DTOs\Unit\CreateUnitDTO;
use App\DTOs\Unit\UpdateUnitDTO;
use App\Http\Requests\Unit\CreateUnitRequest;
use App\Http\Requests\Unit\UpdateUnitRequest;
use App\Models\CompanyUnit;
use App\Services\UnitService;
use App\Services\CompanyService;
use App\Services\DataTables\DataTableQueryService;
use App\Services\DataTables\DataTableResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class UnitController extends Controller
{
    public function __construct(
        private readonly UnitService $service,
        private readonly CompanyService $companyService,
        private readonly DataTableQueryService $dataTableService
    ) {}

    /**
     * Exibe a listagem das unidades de negócio.
     */
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', CompanyUnit::class);

        return view('admin.units.index');
    }

    /**
     * Endpoint API para o DataTables de Unidades.
     */
    public function datatable(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', CompanyUnit::class);

        // Se for um funcionário, restringir as unidades à sua própria empresa
        $companyId = null;
        $actor = $request->user();
        if ($actor instanceof \App\Models\Employee) {
            $companyId = (int) $actor->company_id;
        }

        $provider = new UnitsDataTable($companyId);
        $result = $this->dataTableService->process($request, $provider);

        return DataTableResponseFactory::create($result);
    }

    /**
     * Exibe o formulário de criação de unidade.
     */
    public function create(): View
    {
        Gate::authorize('create', CompanyUnit::class);

        $companies = $this->companyService->getActiveCompanies();

        return view('admin.units.create', compact('companies'));
    }

    /**
     * Salva uma nova unidade no banco.
     */
    public function store(CreateUnitRequest $request, CreateUnitAction $action)
    {
        Gate::authorize('create', CompanyUnit::class);

        $dto = CreateUnitDTO::fromArray($request->validated());
        $unit = $action->execute($dto);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('units.messages.create_success'),
                'unit_uuid' => $unit->uuid
            ], 201);
        }

        return redirect()->route('admin.units.index')
            ->with('success', __('units.messages.create_success'));
    }

    /**
     * Exibe detalhes de uma unidade.
     */
    public function show(string $uuid): View
    {
        $unit = $this->service->findByUuid($uuid);
        Gate::authorize('view', $unit);

        return view('admin.units.show', compact('unit'));
    }

    /**
     * Exibe o formulário de edição de uma unidade.
     */
    public function edit(string $uuid): View
    {
        $unit = $this->service->findByUuid($uuid);
        Gate::authorize('update', $unit);

        return view('admin.units.edit', compact('unit'));
    }

    /**
     * Atualiza os dados de uma unidade no banco.
     */
    public function update(UpdateUnitRequest $request, string $uuid, UpdateUnitAction $action)
    {
        $unit = $this->service->findByUuid($uuid);
        Gate::authorize('update', $unit);

        $dto = UpdateUnitDTO::fromArray($request->validated());
        $updatedUnit = $action->execute($unit, $dto);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('units.messages.update_success'),
                'unit_uuid' => $updatedUnit->uuid
            ]);
        }

        return redirect()->route('admin.units.index')
            ->with('success', __('units.messages.update_success'));
    }

    /**
     * Remove uma unidade (soft delete).
     */
    public function destroy(string $uuid, DeleteUnitAction $action): JsonResponse
    {
        $unit = $this->service->findByUuid($uuid);
        Gate::authorize('delete', $unit);

        $action->execute($unit);

        return response()->json([
            'success' => true,
            'message' => __('units.messages.delete_success')
        ]);
    }
}
