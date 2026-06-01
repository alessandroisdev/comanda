<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Customer\CreateCustomerAction;
use App\Actions\Customer\UpdateCustomerAction;
use App\Actions\Customer\DeleteCustomerAction;
use App\DataTables\CustomersDataTable;
use App\DTOs\Customer\CreateCustomerDTO;
use App\DTOs\Customer\UpdateCustomerDTO;
use App\Http\Requests\Customer\CreateCustomerRequest;
use App\Http\Requests\Customer\UpdateCustomerRequest;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Employee;
use App\Services\CustomerService;
use App\Services\DataTables\DataTableQueryService;
use App\Services\DataTables\DataTableResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function __construct(
        private readonly CustomerService $service,
        private readonly DataTableQueryService $dataTableService
    ) {}

    /**
     * Exibe a listagem principal de clientes.
     */
    public function index(): View
    {
        Gate::authorize('viewAny', Customer::class);

        return view('admin.customers.index');
    }

    /**
     * Endpoint para alimentar o DataTables Server-Side de clientes.
     */
    public function datatable(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Customer::class);

        $provider = new CustomersDataTable();
        
        // Aplicar restrição de locatário se o usuário for um funcionário de empresa
        $user = $request->user();
        $result = $this->dataTableService->process($request, $provider, function ($query) use ($user) {
            if ($user instanceof Employee) {
                $query->where('customers.company_id', $user->company_id);
            }
        });

        return DataTableResponseFactory::create($result);
    }

    /**
     * Exibe o formulário de cadastro de cliente.
     */
    public function create(): View
    {
        Gate::authorize('create', Customer::class);

        $companies = Company::orderBy('trade_name')->get();

        return view('admin.customers.create', compact('companies'));
    }

    /**
     * Armazena um novo cliente no banco.
     */
    public function store(CreateCustomerRequest $request, CreateCustomerAction $action)
    {
        Gate::authorize('create', Customer::class);

        $dto = CreateCustomerDTO::fromArray($request->validated());
        $customer = $action->execute($dto);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('customers.messages.create_success'),
                'customer_uuid' => $customer->uuid
            ], 201);
        }

        return redirect()->route('admin.customers.index')
            ->with('success', __('customers.messages.create_success'));
    }

    /**
     * Exibe os detalhes de um cliente.
     */
    public function show(string $uuid): View
    {
        $customer = $this->service->findByUuid($uuid);
        Gate::authorize('view', $customer);

        return view('admin.customers.show', compact('customer'));
    }

    /**
     * Exibe o formulário de edição de cliente.
     */
    public function edit(string $uuid): View
    {
        $customer = $this->service->findByUuid($uuid);
        Gate::authorize('update', $customer);

        $companies = Company::orderBy('trade_name')->get();

        return view('admin.customers.edit', compact('customer', 'companies'));
    }

    /**
     * Atualiza os dados de um cliente no banco.
     */
    public function update(UpdateCustomerRequest $request, string $uuid, UpdateCustomerAction $action)
    {
        $customer = $this->service->findByUuid($uuid);
        Gate::authorize('update', $customer);

        $dto = UpdateCustomerDTO::fromArray($request->validated());
        $updatedCustomer = $action->execute($customer, $dto);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('customers.messages.update_success'),
                'customer_uuid' => $updatedCustomer->uuid
            ]);
        }

        return redirect()->route('admin.customers.index')
            ->with('success', __('customers.messages.update_success'));
    }

    /**
     * Remove um cliente do sistema.
     */
    public function destroy(string $uuid, DeleteCustomerAction $action): JsonResponse
    {
        $customer = $this->service->findByUuid($uuid);
        Gate::authorize('delete', $customer);

        $action->execute($customer);

        return response()->json([
            'success' => true,
            'message' => __('customers.messages.delete_success')
        ]);
    }
}
