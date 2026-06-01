@extends('layouts.admin')

@section('title', 'Lançar Itens do Pedido')
@section('page_title', 'Lançamento de Itens')

@section('content')
<div class="row">
    <!-- Esquerda: Lista de Itens no Pedido -->
    <div class="col-lg-8 mb-4">
        <div class="card card-premium p-4 h-100 d-flex flex-column">
            <div class="d-flex justify-content-between align-items-center mb-4 border-bottom border-slate-800 pb-3">
                <div>
                    <h5 class="text-white fw-bold m-0"><i class="bi bi-cart-fill text-primary me-2"></i> Itens do Pedido — {{ $order->order_number }}</h5>
                    <span class="text-muted small">Mesa: {{ $order->session->table ? $order->session->table->name : 'Consumo Individual' }}</span>
                </div>
                <div class="text-end">
                    <span class="text-muted small d-block">Subtotal</span>
                    <span class="text-emerald-400 fw-bold fs-4" id="order-total-cents">R$ {{ number_format($order->total_cents / 100, 2, ',', '.') }}</span>
                </div>
            </div>

            @if($order->items->isEmpty())
                <div class="text-center py-5 my-auto" id="empty-items-placeholder">
                    <div class="d-inline-flex align-items-center justify-content-center bg-slate-900 border border-slate-800 rounded-circle mb-3" style="width: 60px; height: 60px;">
                        <i class="bi bi-cart-x text-muted fs-2"></i>
                    </div>
                    <h6 class="text-white fw-bold">Nenhum item lançado neste pedido</h6>
                    <p class="text-muted small mx-auto" style="max-width: 400px;">Use o painel lateral para buscar produtos e adicioná-los a este pedido.</p>
                </div>
            @endif

            <div class="table-responsive @if($order->items->isEmpty()) d-none @endif" id="table-items-container">
                <table class="table table-dark table-hover table-borderless align-middle m-0" id="table-order-items">
                    <thead>
                        <tr class="text-slate-400 border-bottom border-slate-800">
                            <th>Produto</th>
                            <th class="text-center" style="width: 150px;">Quantidade</th>
                            <th class="text-end">Unitário</th>
                            <th class="text-end">Subtotal</th>
                            <th>Obs.</th>
                            <th class="text-center" style="width: 60px;">Excluir</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                            <tr class="border-bottom border-slate-900" id="item-row-{{ $item->uuid }}">
                                <td class="text-white fw-semibold">{{ $item->product->name }}</td>
                                <td class="text-center">
                                    <div class="input-group input-group-sm justify-content-center">
                                        <button class="btn btn-secondary border-slate-700 bg-slate-800 btn-qty-minus" data-uuid="{{ $item->uuid }}" data-qty="{{ $item->quantity }}">-</button>
                                        <input type="text" class="form-control bg-slate-950 border-slate-800 text-white text-center text-qty-value" style="max-width: 50px;" value="{{ $item->quantity }}" readonly>
                                        <button class="btn btn-secondary border-slate-700 bg-slate-800 btn-qty-plus" data-uuid="{{ $item->uuid }}" data-qty="{{ $item->quantity }}">+</button>
                                    </div>
                                </td>
                                <td class="text-end text-slate-300">R$ {{ number_format($item->unit_price_cents / 100, 2, ',', '.') }}</td>
                                <td class="text-end text-emerald-400 fw-semibold">R$ {{ number_format($item->total_price_cents / 100, 2, ',', '.') }}</td>
                                <td class="text-slate-400 small">{{ $item->notes ?? '-' }}</td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-danger btn-remove-item" data-uuid="{{ $item->uuid }}"><i class="bi bi-trash"></i></button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center border-top border-slate-800 pt-3 mt-auto">
                <a href="{{ route('admin.sessions.show', $order->session->uuid) }}" class="btn btn-secondary bg-slate-800 border-slate-700"><i class="bi bi-arrow-left me-1"></i> Voltar para Comanda</a>
                
                @if($order->status->value === 'draft' || $order->status->value === 'pending')
                    <button class="btn btn-premium-primary btn-send-kitchen-direct px-4" @if($order->items->isEmpty()) disabled @endif>
                        <i class="bi bi-fire me-2"></i> Enviar para Cozinha
                    </button>
                @endif
            </div>
        </div>
    </div>

    <!-- Direita: Formulário de Adição de Produtos -->
    <div class="col-lg-4 mb-4">
        <div class="card card-premium p-4 h-100">
            <h5 class="text-white mb-4 fw-bold"><i class="bi bi-plus-circle-fill text-primary me-2"></i> Adicionar Produto</h5>

            <form id="form-add-item">
                <div class="mb-3">
                    <label for="product_uuid" class="form-label text-slate-300">Selecione o Produto</label>
                    <select id="product_uuid" class="form-select bg-slate-900 border-slate-700 text-white" required>
                        <option value="">Selecione...</option>
                        @foreach($products as $product)
                            <option value="{{ $product->uuid }}" data-price="{{ $product->price_cents }}">
                                {{ $product->name }} (R$ {{ number_format($product->price_cents / 100, 2, ',', '.') }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label for="quantity" class="form-label text-slate-300">Quantidade</label>
                    <input type="number" id="quantity" class="form-control bg-slate-900 border-slate-700 text-white" value="1" min="1" required>
                </div>

                <div class="mb-4">
                    <label for="notes" class="form-label text-slate-300">Observações / Adicionais</label>
                    <textarea id="notes" class="form-control bg-slate-900 border-slate-700 text-white" rows="3" placeholder="Ex: Sem gelo e limão, bem passado, ponto da carne..."></textarea>
                </div>

                <button type="submit" class="btn btn-premium-primary w-100"><i class="bi bi-plus-lg me-1"></i> Adicionar ao Pedido</button>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script type="module">
    document.addEventListener('DOMContentLoaded', () => {
        const orderUuid = "{{ $order->uuid }}";

        // Formulário de adição de item
        document.getElementById('form-add-item').addEventListener('submit', (e) => {
            e.preventDefault();
            
            const productUuid = document.getElementById('product_uuid').value;
            const qty = document.getElementById('quantity').value;
            const itemNotes = document.getElementById('notes').value;

            if(!productUuid) return;

            fetch(`/admin/orders/${orderUuid}/items`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    product_uuid: productUuid,
                    quantity: qty,
                    notes: itemNotes
                })
            })
            .then(res => res.json())
            .then(res => {
                if (res.success) {
                    window.location.reload();
                } else {
                    alert(res.message);
                }
            });
        });

        // Eventos na tabela de itens (Exclusão e Quantidade)
        const tableContainer = document.getElementById('table-items-container');
        if (tableContainer) {
            tableContainer.addEventListener('click', (e) => {
                const btnRemove = e.target.closest('.btn-remove-item');
                const btnMinus = e.target.closest('.btn-qty-minus');
                const btnPlus = e.target.closest('.btn-qty-plus');

                if (btnRemove) {
                    const itemUuid = btnRemove.getAttribute('data-uuid');
                    if (confirm('Deseja remover este item do pedido?')) {
                        fetch(`/admin/orders/${orderUuid}/items/${itemUuid}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            }
                        })
                        .then(res => res.json())
                        .then(res => {
                            if (res.success) {
                                window.location.reload();
                            } else {
                                alert(res.message);
                            }
                        });
                    }
                }

                if (btnMinus) {
                    const itemUuid = btnMinus.getAttribute('data-uuid');
                    const qty = parseInt(btnMinus.getAttribute('data-qty'));
                    if (qty <= 1) {
                        alert('A quantidade mínima é 1. Se deseja excluir, clique na lixeira.');
                        return;
                    }
                    updateQty(itemUuid, qty - 1);
                }

                if (btnPlus) {
                    const itemUuid = btnPlus.getAttribute('data-uuid');
                    const qty = parseInt(btnPlus.getAttribute('data-qty'));
                    updateQty(itemUuid, qty + 1);
                }
            });
        }

        function updateQty(itemUuid, newQty) {
            fetch(`/admin/orders/${orderUuid}/items/${itemUuid}/quantity`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ quantity: newQty })
            })
            .then(res => res.json())
            .then(res => {
                if (res.success) {
                    window.location.reload();
                } else {
                    alert(res.message);
                }
            });
        }

        // Enviar para Cozinha
        const btnSendKitchen = document.querySelector('.btn-send-kitchen-direct');
        if (btnSendKitchen) {
            btnSendKitchen.addEventListener('click', () => {
                if (confirm('Deseja enviar este pedido para a cozinha para iniciar a produção?')) {
                    fetch(`/admin/orders/${orderUuid}/send-to-kitchen`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    })
                    .then(res => res.json())
                    .then(res => {
                        if (res.success) {
                            window.location.href = "{{ route('admin.sessions.show', $order->session->uuid) }}";
                        } else {
                            alert(res.message);
                        }
                    });
                }
            });
        }
    });
</script>
@endsection
