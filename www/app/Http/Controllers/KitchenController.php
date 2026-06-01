<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\KitchenTicket\CancelKitchenTicketAction;
use App\Actions\KitchenTicket\CompleteKitchenTicketAction;
use App\Actions\KitchenTicket\MarkKitchenReadyAction;
use App\Actions\KitchenTicket\StartKitchenPreparoAction;
use App\Models\KitchenTicket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class KitchenController extends Controller
{
    public function index(): View
    {
        Gate::authorize('viewAny', KitchenTicket::class);

        // Carrega tickets de cozinha pendentes, preparando ou prontos do tenant do funcionário logado
        $employee = Auth::guard('employee')->user();
        $companyId = $employee ? $employee->company_id : null;

        $tickets = KitchenTicket::whereIn('status', ['pending', 'preparing', 'ready'])
            ->with(['order.items.product', 'order.session.table']);

        if ($companyId) {
            $tickets->whereHas('order', function ($query) use ($companyId) {
                $query->where('company_id', $companyId);
            });
        }

        $tickets = $tickets->orderBy('created_at', 'asc')->get();

        return view('admin.kitchen.index', compact('tickets'));
    }

    public function start(string $uuid, StartKitchenPreparoAction $action): JsonResponse
    {
        $ticket = KitchenTicket::where('uuid', $uuid)->firstOrFail();
        Gate::authorize('update', $ticket);

        if ($ticket->status->value !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'O ticket não está pendente para iniciar o preparo.',
            ], 422);
        }

        $action->execute($ticket);

        return response()->json([
            'success' => true,
            'message' => 'Preparo iniciado com sucesso.',
        ]);
    }

    public function ready(string $uuid, MarkKitchenReadyAction $action): JsonResponse
    {
        $ticket = KitchenTicket::where('uuid', $uuid)->firstOrFail();
        Gate::authorize('update', $ticket);

        if ($ticket->status->value !== 'preparing') {
            return response()->json([
                'success' => false,
                'message' => 'O ticket não está em preparo para ser marcado como pronto.',
            ], 422);
        }

        $action->execute($ticket);

        return response()->json([
            'success' => true,
            'message' => 'Pedido marcado como pronto com sucesso.',
        ]);
    }

    public function complete(string $uuid, CompleteKitchenTicketAction $action): JsonResponse
    {
        $ticket = KitchenTicket::where('uuid', $uuid)->firstOrFail();
        Gate::authorize('update', $ticket);

        if ($ticket->status->value !== 'ready') {
            return response()->json([
                'success' => false,
                'message' => 'O ticket não está pronto para ser finalizado.',
            ], 422);
        }

        $action->execute($ticket);

        return response()->json([
            'success' => true,
            'message' => 'Ticket de cozinha finalizado com sucesso.',
        ]);
    }

    public function cancel(string $uuid, CancelKitchenTicketAction $action): JsonResponse
    {
        $ticket = KitchenTicket::where('uuid', $uuid)->firstOrFail();
        Gate::authorize('update', $ticket);

        $action->execute($ticket);

        return response()->json([
            'success' => true,
            'message' => 'Ticket de cozinha cancelado com sucesso.',
        ]);
    }
}
