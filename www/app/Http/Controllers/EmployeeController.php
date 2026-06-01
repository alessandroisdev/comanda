<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Employee\CreateEmployeeAction;
use App\Actions\Employee\DeleteEmployeeAction;
use App\Actions\Employee\UpdateEmployeeAction;
use App\DataTables\EmployeesDataTable;
use App\DTOs\Employee\CreateEmployeeDTO;
use App\DTOs\Employee\UpdateEmployeeDTO;
use App\Http\Requests\Employee\CreateEmployeeRequest;
use App\Http\Requests\Employee\UpdateEmployeeRequest;
use App\Models\Company;
use App\Models\CompanyUnit;
use App\Models\Employee;
use App\Services\DataTables\DataTableQueryService;
use App\Services\DataTables\DataTableResponseFactory;
use App\Services\EmployeeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    public function __construct(
        private readonly EmployeeService $service,
        private readonly DataTableQueryService $dataTableService
    ) {}

    /**
     * Exibe a listagem principal dos funcionários.
     */
    public function index(): View
    {
        Gate::authorize('viewAny', Employee::class);

        return view('admin.employees.index');
    }

    /**
     * Endpoint para alimentar o DataTables Server-Side de funcionários.
     */
    public function datatable(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Employee::class);

        $provider = new EmployeesDataTable;

        // Se for um funcionário restrito (não admin global de users), aplicar escopo de empresa
        $user = $request->user();
        $result = $this->dataTableService->process($request, $provider, function ($query) use ($user) {
            if ($user instanceof Employee) {
                $query->where('employees.company_id', $user->company_id);
            }
        });

        return DataTableResponseFactory::create($result);
    }

    /**
     * Exibe formulário de cadastro.
     */
    public function create(): View
    {
        Gate::authorize('create', Employee::class);

        $companies = Company::orderBy('trade_name')->get();
        $units = CompanyUnit::orderBy('name')->get();

        return view('admin.employees.create', compact('companies', 'units'));
    }

    /**
     * Armazena um novo funcionário.
     */
    public function store(CreateEmployeeRequest $request, CreateEmployeeAction $action)
    {
        Gate::authorize('create', Employee::class);

        $dto = CreateEmployeeDTO::fromArray($request->validated());
        $employee = $action->execute($dto);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('employees.messages.create_success'),
                'employee_uuid' => $employee->uuid,
            ], 201);
        }

        return redirect()->route('admin.employees.index')
            ->with('success', __('employees.messages.create_success'));
    }

    /**
     * Exibe os detalhes de um funcionário.
     */
    public function show(string $uuid): View
    {
        $employee = $this->service->findByUuid($uuid);
        Gate::authorize('view', $employee);

        return view('admin.employees.show', compact('employee'));
    }

    /**
     * Exibe o formulário de edição de um funcionário.
     */
    public function edit(string $uuid): View
    {
        $employee = $this->service->findByUuid($uuid);
        Gate::authorize('update', $employee);

        $companies = Company::orderBy('trade_name')->get();
        $units = CompanyUnit::where('company_id', $employee->company_id)->orderBy('name')->get();

        return view('admin.employees.edit', compact('employee', 'companies', 'units'));
    }

    /**
     * Atualiza os dados de um funcionário no banco.
     */
    public function update(UpdateEmployeeRequest $request, string $uuid, UpdateEmployeeAction $action)
    {
        $employee = $this->service->findByUuid($uuid);
        Gate::authorize('update', $employee);

        $dto = UpdateEmployeeDTO::fromArray($request->validated());
        $updatedEmployee = $action->execute($employee, $dto);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('employees.messages.update_success'),
                'employee_uuid' => $updatedEmployee->uuid,
            ]);
        }

        return redirect()->route('admin.employees.index')
            ->with('success', __('employees.messages.update_success'));
    }

    /**
     * Remove um funcionário logicamente do sistema.
     */
    public function destroy(string $uuid, DeleteEmployeeAction $action): JsonResponse
    {
        $employee = $this->service->findByUuid($uuid);
        Gate::authorize('delete', $employee);

        $action->execute($employee);

        return response()->json([
            'success' => true,
            'message' => __('employees.messages.delete_success'),
        ]);
    }
}
