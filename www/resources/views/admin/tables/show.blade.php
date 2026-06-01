@extends('layouts.admin')

@section('title', 'Detalhes da Mesa')
@section('page_title', 'Detalhes da Mesa')

@section('content')
<div class="row">
    <!-- Informações Gerais da Mesa -->
    <div class="col-md-4 mb-4">
        <div class="card card-premium p-4 h-100">
            <div class="text-center mb-4">
                <div class="d-inline-flex align-items-center justify-content-center bg-slate-900 border border-slate-800 rounded-circle mb-3" style="width: 80px; height: 80px;">
                    <i class="bi bi-grid-3x3-gap-fill text-primary fs-1"></i>
                </div>
                <h4 class="text-white fw-bold m-0">{{ $table->name }}</h4>
                <span class="text-muted small">Código: {{ $table->code }}</span>
            </div>

            <div class="border-top border-slate-800 pt-3">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-slate-400">Setor/Salão:</span>
                    <span class="text-white fw-semibold">{{ $table->sector }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-slate-400">Capacidade:</span>
                    <span class="text-white fw-semibold"><i class="bi bi-people me-1"></i> {{ $table->capacity }} pessoas</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-slate-400">Ordem:</span>
                    <span class="text-white fw-semibold">{{ $table->sort_order }}</span>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-slate-400">Status:</span>
                    @php
                        $badgeClass = 'badge-premium-active';
                        if ($table->status->value === 'occupied') $badgeClass = 'bg-danger text-white border border-danger-subtle';
                        else if ($table->status->value === 'reserved') $badgeClass = 'bg-warning text-dark border border-warning-subtle';
                        else if ($table->status->value === 'blocked') $badgeClass = 'bg-secondary text-white border border-secondary-subtle';
                        else if ($table->status->value === 'cleaning') $badgeClass = 'bg-info text-dark border border-info-subtle';
                    @endphp
                    <span class="badge {{ $badgeClass }} px-3 py-1 rounded-pill font-semibold">{{ $table->status->label() }}</span>
                </div>
            </div>

            <div class="d-grid gap-2 border-top border-slate-800 pt-3 mt-auto">
                <a href="{{ route('admin.tables.edit', $table->uuid) }}" class="btn btn-primary btn-sm"><i class="bi bi-pencil me-1"></i> Editar Cadastro</a>
                <a href="{{ route('admin.tables.index') }}" class="btn btn-secondary bg-slate-800 border-slate-700 btn-sm"><i class="bi bi-arrow-left me-1"></i> Voltar</a>
            </div>
        </div>
    </div>

    <!-- Controle Operacional / Comanda Ativa -->
    <div class="col-md-8 mb-4">
        <div class="card card-premium p-4 h-100">
            <h5 class="text-white mb-4 fw-bold"><i class="bi bi-gear-wide-connected me-2 text-primary"></i> Situação Operacional</h5>

            @php
                $activeSession = $table->sessions()->where('status', 'open')->first();
            @endphp

            @if($activeSession)
                <!-- Mesa Ocupada / Com comanda ativa -->
                <div class="alert alert-danger bg-danger-subtle border border-danger text-white rounded-3 mb-4 p-3">
                    <h6 class="fw-bold m-0"><i class="bi bi-exclamation-triangle-fill me-2"></i> Esta mesa possui um atendimento aberto!</h6>
                    <p class="small m-0 mt-1">Comanda aberta em: {{ $activeSession->opened_at->format('d/m/Y H:i') }} por {{ $activeSession->openedBy->name ?? 'Sistema' }}.</p>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6 mb-3">
                        <div class="card bg-slate-900 border border-slate-800 p-3 rounded-3 text-center">
                            <span class="text-muted small d-block mb-1">Clientes na Mesa</span>
                            <span class="fs-4 text-white fw-bold"><i class="bi bi-people me-2 text-primary"></i> {{ $activeSession->people_count }}</span>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="card bg-slate-900 border border-slate-800 p-3 rounded-3 text-center">
                            <span class="text-muted small d-block mb-1">Consumo Total Acumulado</span>
                            @php
                                $totalCents = 0;
                                foreach($activeSession->orders as $order) {
                                    if ($order->status->value !== 'cancelled') {
                                        $totalCents += $order->total_cents;
                                    }
                                }
                            @endphp
                            <span class="fs-4 text-emerald-400 fw-bold">R$ {{ number_format($totalCents / 100, 2, ',', '.') }}</span>
                        </div>
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2 border-top border-slate-800 pt-3">
                    <a href="{{ route('admin.sessions.show', $activeSession->uuid) }}" class="btn btn-premium-primary"><i class="bi bi-eye-fill me-2"></i> Ver Comanda do Cliente</a>
                    
                    <button class="btn btn-success btn-close-session-direct" data-uuid="{{ $activeSession->uuid }}"><i class="bi bi-check-circle me-2"></i> Fechar e Limpar Mesa</button>
                </div>
            @else
                <!-- Mesa Disponível para abertura -->
                <div class="text-center py-5">
                    <div class="d-inline-flex align-items-center justify-content-center bg-slate-900 border border-slate-800 rounded-circle mb-3" style="width: 60px; height: 60px;">
                        <i class="bi bi-check-lg text-success fs-2"></i>
                    </div>
                    <h5 class="text-white fw-bold">Mesa Livre para Atendimento</h5>
                    <p class="text-muted mx-auto" style="max-width: 450px;">Esta mesa está pronta para receber novos clientes. Abra uma nova comanda para registrar pedidos.</p>
                    
                    @if($table->status->value === 'available')
                        <button type="button" class="btn btn-premium-primary mt-3 px-4" data-bs-toggle="modal" data-bs-target="#modalOpenSession">
                            <i class="bi bi-play-circle-fill me-2"></i> Iniciar Atendimento / Abrir Comanda
                        </button>
                    @else
                        <div class="alert alert-warning bg-warning-subtle border border-warning text-dark rounded-3 inline-block mt-3 px-4 py-2 small d-inline-block">
                            <i class="bi bi-info-circle me-1"></i> A mesa deve estar no status <strong>Disponível</strong> para abrir comanda.
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal para Abrir Comanda -->
@if(!$activeSession && $table->status->value === 'available')
<div class="modal fade" id="modalOpenSession" tabindex="-1" aria-labelledby="modalOpenSessionLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content bg-slate-900 border border-slate-800 text-white">
            <div class="modal-header border-slate-800">
                <h5 class="modal-title fw-bold" id="modalOpenSessionLabel"><i class="bi bi-play-circle text-primary me-2"></i> Abrir Comanda — {{ $table->name }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.sessions.store') }}" method="POST">
                @csrf
                <input type="hidden" name="company_id" value="{{ $table->company_id }}">
                <input type="hidden" name="unit_id" value="{{ $table->unit_id }}">
                <input type="hidden" name="table_id" value="{{ $table->id }}">

                <div class="modal-body">
                    <div class="mb-3">
                        <label for="people_count" class="form-label">Quantidade de Pessoas</label>
                        <input type="number" name="people_count" id="people_count" class="form-control bg-slate-950 border-slate-800 text-white" value="1" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label for="notes" class="form-label">Observações da Sessão</label>
                        <textarea name="notes" id="notes" class="form-control bg-slate-950 border-slate-800 text-white" rows="3" placeholder="Ex: Cliente prefere mesas no canto, comemoração de aniversário..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-slate-800">
                    <button type="button" class="btn btn-secondary bg-slate-800 border-slate-700" data-bs-dismiss="modal">Fechar</button>
                    <button type="submit" class="btn btn-premium-primary">Confirmar Abertura</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@endsection

@section('scripts')
@if($activeSession)
<script type="module">
    document.addEventListener('DOMContentLoaded', () => {
        const btn = document.querySelector('.btn-close-session-direct');
        if (btn) {
            btn.addEventListener('click', () => {
                if (confirm('Tem certeza de que deseja fechar esta comanda e liberar a mesa para limpeza?')) {
                    const uuid = btn.getAttribute('data-uuid');
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
                            window.location.reload();
                        } else {
                            alert(res.message || 'Erro ao fechar comanda.');
                        }
                    });
                }
            });
        }
    });
</script>
@endif
@endsection
