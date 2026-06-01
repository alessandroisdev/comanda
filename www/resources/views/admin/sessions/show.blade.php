@extends('layouts.admin')

@section('title', 'Gerenciamento da Comanda')
@section('page_title', 'Detalhes da Comanda')

@section('content')
<div class="row">
    <!-- Resumo da Comanda e Ações Rápidas -->
    <div class="col-lg-4 mb-4">
        <div class="card card-premium p-4 mb-4">
            <div class="text-center mb-4">
                <div class="d-inline-flex align-items-center justify-content-center bg-slate-900 border border-slate-800 rounded-circle mb-3" style="width: 80px; height: 80px;">
                    <i class="bi bi-receipt-cutoff text-primary fs-1"></i>
                </div>
                <h4 class="text-white fw-bold m-0">{{ $session->table ? $session->table->name : 'Consumo Individual' }}</h4>
                <span class="text-muted small">ID Sessão: {{ substr($session->uuid, 0, 8) }}</span>
            </div>

            <div class="border-top border-slate-800 pt-3">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-slate-400">Status:</span>
                    @php
                        $badgeClass = 'bg-success text-white border border-success-subtle';
                        if ($session->status->value === 'closed') $badgeClass = 'bg-secondary text-slate-300 border border-secondary-subtle';
                        else if ($session->status->value === 'cancelled') $badgeClass = 'bg-danger text-white border border-danger-subtle';
                    @endphp
                    <span class="badge {{ $badgeClass }} px-3 py-1 rounded-pill font-semibold">{{ $session->status->label() }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-slate-400">Clientes:</span>
                    <span class="text-white fw-semibold"><i class="bi bi-people me-1 text-primary"></i> {{ $session->people_count }} pessoas</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-slate-400">Aberto por:</span>
                    <span class="text-white fw-semibold">{{ $session->openedBy->name ?? 'Sistema' }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-slate-400">Abertura:</span>
                    <span class="text-white fw-semibold small">{{ $session->opened_at->format('d/m/Y H:i') }}</span>
                </div>
                @if($session->closed_at)
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-slate-400">Fechamento:</span>
                        <span class="text-white fw-semibold small">{{ $session->closed_at->format('d/m/Y H:i') }}</span>
                    </div>
                @endif
                <div class="d-flex justify-content-between border-top border-slate-800 pt-3 mt-3">
                    <span class="text-white fw-bold fs-5">Total Consumido:</span>
                    <span class="text-emerald-400 fw-bold fs-5">R$ {{ number_format($totalCents / 100, 2, ',', '.') }}</span>
                </div>
            </div>

            @if($session->status->value === 'open')
                <div class="d-grid gap-2 border-top border-slate-800 pt-3 mt-4">
                    <!-- Ação para Lançar Novo Pedido -->
                    <form action="{{ route('admin.orders.store') }}" method="POST" class="d-grid">
                        @csrf
                        <input type="hidden" name="company_id" value="{{ $session->company_id }}">
                        <input type="hidden" name="unit_id" value="{{ $session->unit_id }}">
                        <input type="hidden" name="session_uuid" value="{{ $session->uuid }}">
                        <button type="submit" class="btn btn-premium-primary w-100">
                            <i class="bi bi-cart-plus-fill me-2"></i> Lançar Novo Pedido
                        </button>
                    </form>

                    <button class="btn btn-success btn-close-session-page" data-uuid="{{ $session->uuid }}"><i class="bi bi-check-circle me-1"></i> Encerrar Atendimento</button>
                    <button class="btn btn-warning btn-transfer-session-page" data-uuid="{{ $session->uuid }}"><i class="bi bi-arrow-left-right me-1"></i> Transferir Mesa</button>
                    <button class="btn btn-info btn-merge-session-page text-dark" data-uuid="{{ $session->uuid }}"><i class="bi bi-plus-slash-minus me-1"></i> Mesclar Comanda</button>
                    <button class="btn btn-danger btn-cancel-session-page" data-uuid="{{ $session->uuid }}"><i class="bi bi-x-circle me-1"></i> Cancelar Atendimento</button>
                </div>
            @endif
        </div>
    </div>

    <!-- Histórico de Pedidos / Lançamento de Itens -->
    <div class="col-lg-8 mb-4">
        <div class="card card-premium p-4 h-100">
            <h5 class="text-white mb-4 fw-bold"><i class="bi bi-clock-history me-2 text-primary"></i> Pedidos Lançados nesta Comanda</h5>

            @if($session->orders->isEmpty())
                <div class="text-center py-5">
                    <div class="d-inline-flex align-items-center justify-content-center bg-slate-900 border border-slate-800 rounded-circle mb-3" style="width: 60px; height: 60px;">
                        <i class="bi bi-cart-x text-muted fs-2"></i>
                    </div>
                    <h6 class="text-white fw-bold">Nenhum pedido lançado ainda</h6>
                    <p class="text-muted small mx-auto" style="max-width: 400px;">Clique no botão "Lançar Novo Pedido" ao lado para abrir uma venda e adicionar itens para esta mesa.</p>
                </div>
            @else
                <div class="accordion accordion-premium" id="accordionOrders">
                    @foreach($session->orders as $order)
                        <div class="accordion-item bg-slate-900 border border-slate-800 rounded-3 mb-3 overflow-hidden">
                            <h2 class="accordion-header" id="heading-{{ $order->uuid }}">
                                <button class="accordion-button bg-slate-900 text-white collapsed px-4 py-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-{{ $order->uuid }}" aria-expanded="false" aria-controls="collapse-{{ $order->uuid }}">
                                    <div class="d-flex justify-content-between align-items-center w-100 me-3">
                                        <div>
                                            <span class="badge bg-slate-800 border border-slate-700 text-white me-2">{{ $order->order_number }}</span>
                                            <span class="text-muted small">{{ $order->created_at->format('H:i') }} • por {{ $order->employee->name ?? 'Sistema' }}</span>
                                        </div>
                                        <div class="text-end">
                                            @php
                                                $orderBadge = 'bg-slate-800 border border-slate-700 text-white';
                                                if ($order->status->value === 'sent_to_kitchen') $orderBadge = 'bg-primary text-white border border-primary-subtle';
                                                else if ($order->status->value === 'preparing') $orderBadge = 'bg-warning text-dark border border-warning-subtle';
                                                else if ($order->status->value === 'ready') $orderBadge = 'bg-info text-dark border border-info-subtle';
                                                else if ($order->status->value === 'delivered') $orderBadge = 'bg-success text-white border border-success-subtle';
                                                else if ($order->status->value === 'cancelled') $orderBadge = 'bg-danger text-white border border-danger-subtle';
                                            @endphp
                                            <span class="badge {{ $orderBadge }} me-3">{{ $order->status->label() }}</span>
                                            <span class="text-emerald-400 fw-bold">R$ {{ number_format($order->total_cents / 100, 2, ',', '.') }}</span>
                                        </div>
                                    </div>
                                </button>
                            </h2>
                            <div id="collapse-{{ $order->uuid }}" class="accordion-collapse collapse" aria-labelledby="heading-{{ $order->uuid }}" data-bs-parent="#accordionOrders">
                                <div class="accordion-body bg-slate-950 px-4 py-3 border-top border-slate-800">
                                    @if($order->items->isEmpty())
                                        <p class="text-muted small m-0 text-center py-3">Este pedido não possui itens lançados.</p>
                                    @else
                                        <div class="table-responsive">
                                            <table class="table table-dark table-hover table-borderless m-0">
                                                <thead>
                                                    <tr class="text-slate-400 border-bottom border-slate-800">
                                                        <th>Produto</th>
                                                        <th class="text-center">Qtd</th>
                                                        <th class="text-end">Preço Unitário</th>
                                                        <th class="text-end">Preço Total</th>
                                                        <th>Observações</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($order->items as $item)
                                                        <tr class="border-bottom border-slate-900">
                                                            <td class="text-white fw-medium">{{ $item->product->name }}</td>
                                                            <td class="text-center">{{ $item->quantity }}</td>
                                                            <td class="text-end text-slate-300">R$ {{ number_format($item->unit_price_cents / 100, 2, ',', '.') }}</td>
                                                            <td class="text-end text-emerald-400 fw-semibold">R$ {{ number_format($item->total_price_cents / 100, 2, ',', '.') }}</td>
                                                            <td class="text-slate-400 small">{{ $item->notes ?? '-' }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @endif

                                    @if($session->status->value === 'open' && ($order->status->value === 'draft' || $order->status->value === 'pending'))
                                        <div class="d-flex justify-content-end gap-2 border-top border-slate-800 pt-3 mt-3">
                                            <a href="{{ route('admin.orders.show', $order->uuid) }}" class="btn btn-sm btn-primary"><i class="bi bi-pencil me-1"></i> Lançar / Editar Itens</a>
                                            <button class="btn btn-sm btn-success btn-send-kitchen" data-uuid="{{ $order->uuid }}"><i class="bi bi-fire me-1"></i> Enviar Cozinha</button>
                                            <button class="btn btn-sm btn-danger btn-cancel-order" data-uuid="{{ $order->uuid }}"><i class="bi bi-x-circle me-1"></i> Cancelar Pedido</button>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal para Transferência -->
<div class="modal fade" id="modalTransfer" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content bg-slate-900 border border-slate-800 text-white">
            <div class="modal-header border-slate-800">
                <h5 class="modal-title fw-bold"><i class="bi bi-arrow-left-right text-primary me-2"></i> Transferir Mesa</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form-transfer">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="transfer-table" class="form-label">Selecione a Nova Mesa</label>
                        @php
                            $tables = \App\Models\Table::where('status', 'available')
                                ->where('company_id', $session->company_id)
                                ->get();
                        @endphp
                        <select id="transfer-table" class="form-select bg-slate-950 border-slate-800 text-white" required>
                            <option value="">Selecione...</option>
                            @foreach($tables as $table)
                                <option value="{{ $table->uuid }}">{{ $table->name }} (Capacidade: {{ $table->capacity }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-slate-800">
                    <button type="button" class="btn btn-secondary bg-slate-800 border-slate-700" data-bs-dismiss="modal">Fechar</button>
                    <button type="submit" class="btn btn-premium-primary">Confirmar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para Mesclagem -->
<div class="modal fade" id="modalMerge" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content bg-slate-900 border border-slate-800 text-white">
            <div class="modal-header border-slate-800">
                <h5 class="modal-title fw-bold"><i class="bi bi-plus-slash-minus text-primary me-2"></i> Mesclar Comandas</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form-merge">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="merge-target" class="form-label">Comanda Destino (A mesa que receberá os pedidos)</label>
                        @php
                            $openSessions = \App\Models\OrderSession::where('status', 'open')
                                ->where('company_id', $session->company_id)
                                ->where('id', '!=', $session->id)
                                ->with('table')
                                ->get();
                        @endphp
                        <select id="merge-target" class="form-select bg-slate-950 border-slate-800 text-white" required>
                            <option value="">Selecione...</option>
                            @foreach($openSessions as $os)
                                <option value="{{ $os->uuid }}">{{ $os->table ? $os->table->name : 'Consumo Individual (' . substr($os->uuid, 0, 6) . ')' }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-slate-800">
                    <button type="button" class="btn btn-secondary bg-slate-800 border-slate-700" data-bs-dismiss="modal">Fechar</button>
                    <button type="submit" class="btn btn-premium-primary">Confirmar Mesclagem</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script type="module">
    document.addEventListener('DOMContentLoaded', () => {
        // Enviar para Cozinha
        document.querySelectorAll('.btn-send-kitchen').forEach(btn => {
            btn.addEventListener('click', () => {
                const uuid = btn.getAttribute('data-uuid');
                if (confirm('Deseja enviar este pedido para a fila de produção da cozinha?')) {
                    fetch(`/admin/orders/${uuid}/send-to-kitchen`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
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
            });
        });

        // Cancelar Pedido
        document.querySelectorAll('.btn-cancel-order').forEach(btn => {
            btn.addEventListener('click', () => {
                const uuid = btn.getAttribute('data-uuid');
                if (confirm('Deseja realmente cancelar este pedido?')) {
                    fetch(`/admin/orders/${uuid}/cancel`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
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
            });
        });

        // Fechar Comanda
        const btnClose = document.querySelector('.btn-close-session-page');
        if (btnClose) {
            btnClose.addEventListener('click', () => {
                const uuid = btnClose.getAttribute('data-uuid');
                if (confirm('Deseja fechar esta comanda? A mesa correspondente será liberada para limpeza.')) {
                    fetch(`/admin/sessions/${uuid}/close`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    })
                    .then(res => res.json())
                    .then(res => {
                        if (res.success) {
                            window.location.href = "{{ route('admin.sessions.index') }}";
                        } else {
                            alert(res.message);
                        }
                    });
                }
            });
        }

        // Cancelar Comanda
        const btnCancel = document.querySelector('.btn-cancel-session-page');
        if (btnCancel) {
            btnCancel.addEventListener('click', () => {
                const uuid = btnCancel.getAttribute('data-uuid');
                if (confirm('Deseja cancelar esta comanda inteira e todos os seus pedidos? Esta ação é irreversível e auditada.')) {
                    fetch(`/admin/sessions/${uuid}/cancel`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    })
                    .then(res => res.json())
                    .then(res => {
                        if (res.success) {
                            window.location.href = "{{ route('admin.sessions.index') }}";
                        } else {
                            alert(res.message);
                        }
                    });
                }
            });
        }

        // Transferência
        const btnTransfer = document.querySelector('.btn-transfer-session-page');
        if (btnTransfer) {
            btnTransfer.addEventListener('click', () => {
                const modal = new bootstrap.Modal(document.getElementById('modalTransfer'));
                modal.show();
            });
        }

        document.getElementById('form-transfer').addEventListener('submit', (e) => {
            e.preventDefault();
            const tableUuid = document.getElementById('transfer-table').value;
            fetch(`/admin/sessions/{{ $session->uuid }}/transfer`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ table_uuid: tableUuid })
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

        // Mesclagem
        const btnMerge = document.querySelector('.btn-merge-session-page');
        if (btnMerge) {
            btnMerge.addEventListener('click', () => {
                const modal = new bootstrap.Modal(document.getElementById('modalMerge'));
                modal.show();
            });
        }

        document.getElementById('form-merge').addEventListener('submit', (e) => {
            e.preventDefault();
            const targetUuid = document.getElementById('merge-target').value;
            fetch(`/admin/sessions/{{ $session->uuid }}/merge`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ target_session_uuid: targetUuid })
            })
            .then(res => res.json())
            .then(res => {
                if (res.success) {
                    window.location.href = `/admin/sessions/${targetUuid}`;
                } else {
                    alert(res.message);
                }
            });
        });
    });
</script>
@endsection
