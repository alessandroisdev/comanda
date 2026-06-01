<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\OrderSession\CancelOrderSessionAction;
use App\Actions\OrderSession\CloseOrderSessionAction;
use App\Actions\OrderSession\MergeOrderSessionsAction;
use App\Actions\OrderSession\OpenOrderSessionAction;
use App\Actions\OrderSession\TransferTableAction;
use App\DataTables\OrderSessionsDataTable;
use App\Models\Employee;
use App\Models\OrderSession;
use App\Models\Table;
use App\Services\DataTables\DataTableQueryService;
use App\Services\DataTables\DataTableResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class OrderSessionController extends Controller
{
    public function __construct(
        private readonly DataTableQueryService $dataTableService
    ) {}

    public function index(): View
    {
        Gate::authorize('viewAny', OrderSession::class);

        return view('admin.sessions.index');
    }

    public function datatable(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', OrderSession::class);

        $provider = new OrderSessionsDataTable;
        $result = $this->dataTableService->process($request, $provider);

        return DataTableResponseFactory::create($result);
    }

    public function create(): View
    {
        Gate::authorize('create', OrderSession::class);

        // Carrega mesas livres do tenant do usuário logado
        /** @var Employee|null $employee */
        $employee = Auth::guard('employee')->user();
        $companyId = $employee ? $employee->company_id : null;

        $tables = Table::where('status', 'available');
        if ($companyId) {
            $tables->where('company_id', $companyId);
        }
        $tables = $tables->get();

        return view('admin.sessions.create', compact('tables'));
    }

    public function store(Request $request, OpenOrderSessionAction $action)
    {
        Gate::authorize('create', OrderSession::class);

        /** @var Employee|null $employee */
        $employee = Auth::guard('employee')->user();

        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'unit_id' => 'required|exists:company_units,id',
            'table_id' => 'nullable|exists:tables,id',
            'people_count' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:500',
        ]);

        $data = array_merge($validated, [
            'opened_by_employee_id' => $employee ? $employee->id : 1, // fallback se não autenticado (ex: seeders ou admin)
        ]);

        /** @var OrderSession $session */
        $session = $action->execute($data);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'session_uuid' => $session->uuid,
            ], 201);
        }

        return redirect()->route('admin.sessions.show', $session->uuid)
            ->with('success', 'Comanda aberta com sucesso.');
    }

    public function show(string $uuid): View
    {
        /** @var OrderSession $session */
        $session = OrderSession::where('uuid', $uuid)
            ->with(['table', 'openedBy', 'closedBy', 'orders.items.product'])
            ->firstOrFail();

        Gate::authorize('view', $session);

        // Calcula total geral somando todos os pedidos ativos (não cancelados)
        $totalCents = 0;
        foreach ($session->orders as $order) {
            if ($order->status->value !== 'cancelled') {
                $totalCents += $order->total_cents;
            }
        }

        return view('admin.sessions.show', compact('session', 'totalCents'));
    }

    public function close(Request $request, string $uuid, CloseOrderSessionAction $action): JsonResponse
    {
        /** @var OrderSession $session */
        $session = OrderSession::where('uuid', $uuid)->firstOrFail();
        Gate::authorize('update', $session);

        /** @var Employee|null $employee */
        $employee = Auth::guard('employee')->user();
        $employeeId = $employee ? $employee->id : 1;

        $action->execute($session, $employeeId);

        return response()->json([
            'success' => true,
            'message' => 'Comanda encerrada com sucesso.',
        ]);
    }

    public function cancel(Request $request, string $uuid, CancelOrderSessionAction $action): JsonResponse
    {
        /** @var OrderSession $session */
        $session = OrderSession::where('uuid', $uuid)->firstOrFail();
        Gate::authorize('update', $session);

        /** @var Employee|null $employee */
        $employee = Auth::guard('employee')->user();
        $employeeId = $employee ? $employee->id : 1;

        $action->execute($session, $employeeId);

        return response()->json([
            'success' => true,
            'message' => 'Comanda cancelada com sucesso.',
        ]);
    }

    public function transfer(Request $request, string $uuid, TransferTableAction $action): JsonResponse
    {
        /** @var OrderSession $session */
        $session = OrderSession::where('uuid', $uuid)->firstOrFail();
        Gate::authorize('update', $session);

        $request->validate([
            'table_uuid' => 'required|exists:tables,uuid',
        ]);

        /** @var Table $newTable */
        $newTable = Table::where('uuid', $request->input('table_uuid'))->firstOrFail();

        // Verifica se a nova mesa está disponível
        if ($newTable->status->value !== 'available') {
            return response()->json([
                'success' => false,
                'message' => 'A mesa de destino não está disponível.',
            ], 422);
        }

        $action->execute($session, $newTable);

        return response()->json([
            'success' => true,
            'message' => 'Mesa transferida com sucesso.',
        ]);
    }

    public function merge(Request $request, string $uuid, MergeOrderSessionsAction $action): JsonResponse
    {
        /** @var OrderSession $session */
        $session = OrderSession::where('uuid', $uuid)->firstOrFail();
        Gate::authorize('update', $session);

        $request->validate([
            'target_session_uuid' => 'required|exists:orders_sessions,uuid',
        ]);

        /** @var OrderSession $targetSession */
        $targetSession = OrderSession::where('uuid', $request->input('target_session_uuid'))->firstOrFail();

        // Verifica se a comanda destino está aberta
        if ($targetSession->status->value !== 'open') {
            return response()->json([
                'success' => false,
                'message' => 'A comanda de destino deve estar aberta.',
            ], 422);
        }

        // Verifica se são a mesma comanda
        if ($session->id === $targetSession->id) {
            return response()->json([
                'success' => false,
                'message' => 'Não é possível mesclar uma comanda nela mesma.',
            ], 422);
        }

        /** @var Employee|null $employee */
        $employee = Auth::guard('employee')->user();
        $employeeId = $employee ? $employee->id : 1;

        $action->execute($session, $targetSession, $employeeId);

        return response()->json([
            'success' => true,
            'message' => 'Comandas mescladas com sucesso.',
        ]);
    }
}
