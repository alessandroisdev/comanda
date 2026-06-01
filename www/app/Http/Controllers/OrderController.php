<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Order\CancelOrderAction;
use App\Actions\Order\CreateOrderAction;
use App\Actions\Order\SendOrderToKitchenAction;
use App\Actions\OrderItem\AddOrderItemAction;
use App\Actions\OrderItem\RemoveOrderItemAction;
use App\Actions\OrderItem\UpdateOrderItemQuantityAction;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderSession;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function store(Request $request, CreateOrderAction $action)
    {
        Gate::authorize('create', Order::class);

        $employee = Auth::guard('employee')->user();
        
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'unit_id' => 'required|exists:company_units,id',
            'session_uuid' => 'required|exists:orders_sessions,uuid',
            'notes' => 'nullable|string|max:500',
        ]);

        $session = OrderSession::where('uuid', $validated['session_uuid'])->firstOrFail();

        // Garante isolamento de tenant
        if ($employee && $employee->company_id !== $session->company_id) {
            abort(403, 'Acesso não autorizado.');
        }

        $data = [
            'company_id' => $validated['company_id'],
            'unit_id' => $validated['unit_id'],
            'session_id' => $session->id,
            'employee_id' => $employee ? $employee->id : 1,
            'notes' => $validated['notes'] ?? null,
            'discount_cents' => 0,
        ];

        $order = $action->execute($data);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'order_uuid' => $order->uuid,
                'order_number' => $order->order_number,
            ], 201);
        }

        return redirect()->route('admin.sessions.show', $session->uuid)
            ->with('success', 'Pedido criado com sucesso.');
    }

    public function show(string $uuid): View
    {
        $order = Order::where('uuid', $uuid)
            ->with(['session.table', 'items.product', 'employee'])
            ->firstOrFail();

        Gate::authorize('view', $order);

        // Carrega produtos do tenant do usuário logado para poder adicionar itens
        $employee = Auth::guard('employee')->user();
        $companyId = $employee ? $employee->company_id : $order->company_id;

        $products = Product::where('status', 'active');
        if ($companyId) {
            $products->where('company_id', $companyId);
        }
        $products = $products->get();

        return view('admin.orders.show', compact('order', 'products'));
    }

    public function sendToKitchen(Request $request, string $uuid, SendOrderToKitchenAction $action): JsonResponse
    {
        $order = Order::where('uuid', $uuid)->firstOrFail();
        Gate::authorize('update', $order);

        if ($order->status->value !== 'draft' && $order->status->value !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'O pedido não está em rascunho ou pendente para ser enviado à cozinha.',
            ], 422);
        }

        $action->execute($order);

        return response()->json([
            'success' => true,
            'message' => 'Pedido enviado para a cozinha com sucesso.',
        ]);
    }

    public function cancel(Request $request, string $uuid, CancelOrderAction $action): JsonResponse
    {
        $order = Order::where('uuid', $uuid)->firstOrFail();
        Gate::authorize('update', $order);

        $action->execute($order);

        return response()->json([
            'success' => true,
            'message' => 'Pedido cancelado com sucesso.',
        ]);
    }

    public function addItem(Request $request, string $uuid, AddOrderItemAction $action): JsonResponse
    {
        $order = Order::where('uuid', $uuid)->firstOrFail();
        Gate::authorize('update', $order);

        if ($order->status->value !== 'draft' && $order->status->value !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Não é possível adicionar itens a um pedido que já foi enviado à produção ou finalizado.',
            ], 422);
        }

        $request->validate([
            'product_uuid' => 'required|exists:products,uuid',
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:250',
        ]);

        $product = Product::where('uuid', $request->input('product_uuid'))->firstOrFail();

        // Garante isolamento de tenant
        if ($order->company_id !== $product->company_id) {
            abort(403, 'Acesso não autorizado.');
        }

        $action->execute($order, $product, (int) $request->input('quantity'), $request->input('notes'));

        return response()->json([
            'success' => true,
            'message' => 'Item adicionado com sucesso.',
        ]);
    }

    public function removeItem(Request $request, string $uuid, string $itemUuid, RemoveOrderItemAction $action): JsonResponse
    {
        $order = Order::where('uuid', $uuid)->firstOrFail();
        Gate::authorize('update', $order);

        if ($order->status->value !== 'draft' && $order->status->value !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Não é possível remover itens de um pedido que já foi enviado à produção ou finalizado.',
            ], 422);
        }

        $item = OrderItem::where('uuid', $itemUuid)->firstOrFail();

        // Garante isolamento
        if ($item->order_id !== $order->id) {
            abort(403, 'Acesso não autorizado.');
        }

        $action->execute($item);

        return response()->json([
            'success' => true,
            'message' => 'Item removido com sucesso.',
        ]);
    }

    public function updateItemQuantity(Request $request, string $uuid, string $itemUuid, UpdateOrderItemQuantityAction $action): JsonResponse
    {
        $order = Order::where('uuid', $uuid)->firstOrFail();
        Gate::authorize('update', $order);

        if ($order->status->value !== 'draft' && $order->status->value !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Não é possível alterar itens de um pedido que já foi enviado à produção ou finalizado.',
            ], 422);
        }

        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $item = OrderItem::where('uuid', $itemUuid)->firstOrFail();

        // Garante isolamento
        if ($item->order_id !== $order->id) {
            abort(403, 'Acesso não autorizado.');
        }

        $action->execute($item, (int) $request->input('quantity'));

        return response()->json([
            'success' => true,
            'message' => 'Quantidade do item atualizada com sucesso.',
        ]);
    }
}
