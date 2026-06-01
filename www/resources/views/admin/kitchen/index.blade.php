@extends('layouts.admin')

@section('title', 'Painel de Cozinha (Produção)')
@section('page_title', 'Fila de Produção da Cozinha')

@section('styles')
<style>
    /* Estilos Premium e Responsivos Dedicados à Cozinha */
    .kitchen-card {
        background: #0f172a;
        border: 2px solid #1e293b;
        border-radius: 14px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
    }
    .kitchen-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.5);
    }
    .kitchen-card.status-pending {
        border-color: #3b82f6; /* Blue 500 */
    }
    .kitchen-card.status-preparing {
        border-color: #f59e0b; /* Amber 500 */
    }
    .kitchen-card.status-ready {
        border-color: #06b6d4; /* Cyan 500 */
    }

    .obs-highlight {
        background: rgba(239, 68, 68, 0.15);
        color: #ef4444;
        border: 1px solid rgba(239, 68, 68, 0.3);
        border-radius: 6px;
        padding: 8px;
        font-weight: 500;
    }
</style>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 border-bottom border-slate-800 pb-3">
    <div>
        <p class="text-muted m-0">Painel operacional de produção de pedidos integrado em tempo real via SSE (Server-Sent Events).</p>
    </div>
    <div>
        <span class="badge bg-slate-900 border border-slate-800 text-slate-300 p-2 rounded-3">
            <span class="rounded-circle bg-emerald-500 d-inline-block animate-pulse me-1" style="width: 8px; height: 8px;"></span> Realtime SSE Ativo
        </span>
    </div>
</div>

<div class="row g-4" id="kitchen-tickets-container">
    @if($tickets->isEmpty())
        <div class="col-12 text-center py-5" id="no-tickets-placeholder">
            <div class="d-inline-flex align-items-center justify-content-center bg-slate-900 border border-slate-800 rounded-circle mb-3" style="width: 80px; height: 80px;">
                <i class="bi bi-egg-fried text-muted fs-1 animate-bounce"></i>
            </div>
            <h5 class="text-white fw-bold">Sem pedidos em produção no momento</h5>
            <p class="text-muted">A fila da cozinha está limpa! Novos pedidos de clientes aparecerão aqui automaticamente.</p>
        </div>
    @else
        @foreach($tickets as $ticket)
            <div class="col-md-6 col-lg-4 col-xl-3" id="ticket-col-{{ $ticket->uuid }}">
                <div class="kitchen-card status-{{ $ticket->status->value }} p-3 h-100 d-flex flex-column">
                    <!-- Topo do Card -->
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <span class="badge bg-slate-800 border border-slate-700 text-white fs-6 mb-1">
                                {{ $ticket->order->session->table ? $sessionName = $ticket->order->session->table->name : 'Avulso' }}
                            </span>
                            <small class="text-muted d-block">Pedido: {{ $ticket->order->order_number }}</small>
                        </div>
                        <div>
                            @php
                                $statusBadge = 'bg-primary';
                                if ($ticket->status->value === 'preparing') $statusBadge = 'bg-warning text-dark';
                                else if ($ticket->status->value === 'ready') $statusBadge = 'bg-info text-dark';
                            @endphp
                            <span class="badge {{ $statusBadge }} text-uppercase font-bold" style="font-size: 0.75rem;">{{ $ticket->status->label() }}</span>
                        </div>
                    </div>

                    <!-- Corpo / Itens do Ticket -->
                    <div class="my-3 flex-grow-1 border-top border-slate-800 pt-3">
                        <ul class="list-unstyled m-0">
                            @foreach($ticket->order->items as $item)
                                <li class="mb-3">
                                    <div class="d-flex justify-content-between">
                                        <span class="text-white fw-bold"><i class="bi bi-chevron-right text-primary me-1"></i> {{ $item->quantity }}x {{ $item->product->name }}</span>
                                    </div>
                                    @if($item->notes)
                                        <div class="obs-highlight mt-1 small">
                                            <i class="bi bi-exclamation-circle-fill me-1"></i> OBS: {{ $item->notes }}
                                        </div>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <!-- Rodapé com Ações Operacionais -->
                    <div class="border-top border-slate-800 pt-3 mt-auto">
                        @if($ticket->status->value === 'pending')
                            <button class="btn btn-primary w-100 btn-action" data-url="/admin/kitchen/{{ $ticket->uuid }}/start">
                                <i class="bi bi-play-fill me-1"></i> Iniciar Preparo
                            </button>
                        @elseif($ticket->status->value === 'preparing')
                            <button class="btn btn-warning text-dark w-100 btn-action" data-url="/admin/kitchen/{{ $ticket->uuid }}/ready">
                                <i class="bi bi-check-lg me-1"></i> Marcar como Pronto
                            </button>
                        @elseif($ticket->status->value === 'ready')
                            <button class="btn btn-success w-100 btn-action" data-url="/admin/kitchen/{{ $ticket->uuid }}/complete">
                                <i class="bi bi-box-arrow-right me-1"></i> Finalizar/Retirar
                            </button>
                        @endif

                        <button class="btn btn-outline-danger btn-sm w-100 mt-2 btn-action-cancel" data-url="/admin/kitchen/{{ $ticket->uuid }}/cancel">
                            <i class="bi bi-x-circle me-1"></i> Cancelar Produção
                        </button>
                    </div>
                </div>
            </div>
        @endforeach
    @endif
</div>
@endsection

@section('scripts')
<script type="module">
    document.addEventListener('DOMContentLoaded', () => {
        // Manipulador de Ações do Card
        const container = document.getElementById('kitchen-tickets-container');
        if (container) {
            container.addEventListener('click', (e) => {
                const btnAction = e.target.closest('.btn-action');
                const btnCancel = e.target.closest('.btn-action-cancel');

                if (btnAction) {
                    const url = btnAction.getAttribute('data-url');
                    executeAction(url);
                }

                if (btnCancel) {
                    if (confirm('Tem certeza de que deseja cancelar a produção deste ticket de cozinha?')) {
                        const url = btnCancel.getAttribute('data-url');
                        executeAction(url);
                    }
                }
            });
        }

        function executeAction(url) {
            fetch(url, {
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

        // Subscrição ao SSE da Cozinha para atualização em tempo real
        const sseSource = new EventSource('/sse/stream/admin.kitchen');

        // Escuta qualquer evento de mudança na cozinha
        sseSource.addEventListener('kitchen.created', () => reloadKitchenPage());
        sseSource.addEventListener('kitchen.preparing', () => reloadKitchenPage());
        sseSource.addEventListener('kitchen.ready', () => reloadKitchenPage());
        sseSource.addEventListener('kitchen.completed', () => reloadKitchenPage());

        function reloadKitchenPage() {
            console.log('SSE: Kitchen state modified, reloading page...');
            window.location.reload();
        }

        window.addEventListener('beforeunload', () => {
            sseSource.close();
        });
    });
</script>
@endsection
