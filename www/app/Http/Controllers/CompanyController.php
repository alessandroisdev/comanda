<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Company\CreateCompanyAction;
use App\Actions\Company\DeleteCompanyAction;
use App\Actions\Company\UpdateCompanyAction;
use App\DataTables\CompaniesDataTable;
use App\DTOs\Company\CreateCompanyDTO;
use App\DTOs\Company\UpdateCompanyDTO;
use App\Http\Requests\Company\CreateCompanyRequest;
use App\Http\Requests\Company\UpdateCompanyRequest;
use App\Models\Company;
use App\Services\CompanyService;
use App\Services\DataTables\DataTableQueryService;
use App\Services\DataTables\DataTableResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class CompanyController extends Controller
{
    public function __construct(
        private readonly CompanyService $service,
        private readonly DataTableQueryService $dataTableService
    ) {}

    /**
     * Exibe a listagem principal das empresas (Tela HTML).
     */
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Company::class);

        return view('admin.companies.index');
    }

    /**
     * Endpoint API para alimentar o DataTables Server-Side de Empresas.
     */
    public function datatable(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Company::class);

        $provider = new CompaniesDataTable;
        $result = $this->dataTableService->process($request, $provider);

        return DataTableResponseFactory::create($result);
    }

    /**
     * Exibe o formulário de cadastro de nova empresa.
     */
    public function create(): View
    {
        Gate::authorize('create', Company::class);

        return view('admin.companies.create');
    }

    /**
     * Armazena uma nova empresa no banco de dados.
     */
    public function store(CreateCompanyRequest $request, CreateCompanyAction $action)
    {
        Gate::authorize('create', Company::class);

        $dto = CreateCompanyDTO::fromArray($request->validated());
        $company = $action->execute($dto);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('companies.messages.create_success'),
                'company_uuid' => $company->uuid,
            ], 201);
        }

        return redirect()->route('admin.companies.index')
            ->with('success', __('companies.messages.create_success'));
    }

    /**
     * Exibe os detalhes de uma empresa.
     */
    public function show(string $uuid): View
    {
        $company = $this->service->findByUuid($uuid);
        Gate::authorize('view', $company);

        return view('admin.companies.show', compact('company'));
    }

    /**
     * Exibe o formulário de edição de uma empresa.
     */
    public function edit(string $uuid): View
    {
        $company = $this->service->findByUuid($uuid);
        Gate::authorize('update', $company);

        return view('admin.companies.edit', compact('company'));
    }

    /**
     * Atualiza os dados de uma empresa no banco.
     */
    public function update(UpdateCompanyRequest $request, string $uuid, UpdateCompanyAction $action)
    {
        $company = $this->service->findByUuid($uuid);
        Gate::authorize('update', $company);

        $dto = UpdateCompanyDTO::fromArray($request->validated());
        $updatedCompany = $action->execute($company, $dto);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('companies.messages.update_success'),
                'company_uuid' => $updatedCompany->uuid,
            ]);
        }

        return redirect()->route('admin.companies.index')
            ->with('success', __('companies.messages.update_success'));
    }

    /**
     * Remove uma empresa de forma definitiva ou lógica (soft delete).
     */
    public function destroy(string $uuid, DeleteCompanyAction $action): JsonResponse
    {
        $company = $this->service->findByUuid($uuid);
        Gate::authorize('delete', $company);

        $action->execute($company);

        return response()->json([
            'success' => true,
            'message' => __('companies.messages.delete_success'),
        ]);
    }
}
