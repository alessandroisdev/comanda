@extends('layouts.admin')

@section('title', 'Detalhes do Turno de Caixa')
@section('page_title', 'Turno de Caixa Finalizado')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card card-premium p-4">
            <h5 class="text-white mb-4 fw-bold"><i class="bi bi-safe-fill text-primary me-2"></i> Detalhes do Turno de Caixa</h5>

            <div class="row g-4 mb-4 border-bottom border-slate-800 pb-4">
                <div class="col-md-6">
                    <div class="card bg-slate-900 border border-slate-800 p-3 rounded-3 text-center">
                        <span class="text-muted small d-block mb-1">Valor de Abertura</span>
                        <span class="fs-4 text-white fw-bold">R$ {{ number_format($shift->opening_amount_cents / 100, 2, ',', '.') }}</span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card bg-slate-900 border border-slate-800 p-3 rounded-3 text-center">
                        <span class="text-muted small d-block mb-1">Valor de Fechamento</span>
                        <span class="fs-4 text-emerald-400 fw-bold">
                            {{ $shift->closing_amount_cents !== null ? 'R$ ' . number_format($shift->closing_amount_cents / 100, 2, ',', '.') : '-' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Análise de Quebra/Sobra de Caixa se aplicável -->
            @if($shift->closing_amount_cents !== null)
                @php
                    $diferenca = $shift->closing_amount_cents - $shift->opening_amount_cents;
                @endphp
                
                @if($diferenca < 0)
                    <div class="alert alert-danger bg-danger-subtle border border-danger text-white rounded-3 mb-4">
                        <h6 class="fw-bold m-0"><i class="bi bi-graph-down-arrow me-2"></i> Quebra de Caixa Identificada</h6>
                        <p class="small m-0 mt-1">O valor de fechamento é inferior ao valor de abertura por <strong>R$ {{ number_format(abs($diferenca) / 100, 2, ',', '.') }}</strong>.</p>
                    </div>
                @elseif($diferenca > 0)
                    <div class="alert alert-success bg-success-subtle border border-success text-white rounded-3 mb-4">
                        <h6 class="fw-bold m-0"><i class="bi bi-graph-up-arrow me-2"></i> Sobra de Caixa Registrada</h6>
                        <p class="small m-0 mt-1">Houve uma sobra de <strong>R$ {{ number_format($diferenca / 100, 2, ',', '.') }}</strong> em relação ao valor de abertura.</p>
                    </div>
                @else
                    <div class="alert alert-info bg-info-subtle border border-info text-dark rounded-3 mb-4">
                        <h6 class="fw-bold m-0"><i class="bi bi-check-circle-fill me-2"></i> Caixa Conciliado Perfeitamente</h6>
                        <p class="small m-0 mt-1">Nenhuma divergência de valores de abertura e fechamento foi encontrada.</p>
                    </div>
                @endif
            @endif

            <div class="mb-4">
                <h6 class="text-white fw-bold mb-3"><i class="bi bi-info-circle me-1 text-primary"></i> Informações do Turno</h6>
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <span class="text-slate-400 d-block small">Operador Abertura:</span>
                        <span class="text-white fw-medium">{{ $shift->openedByEmployee->name ?? 'Sistema' }}</span>
                    </div>
                    <div class="col-md-6 mb-2">
                        <span class="text-slate-400 d-block small">Operador Fechamento:</span>
                        <span class="text-white fw-medium">{{ $shift->closedByEmployee->name ?? '-' }}</span>
                    </div>
                    <div class="col-md-6 mb-2">
                        <span class="text-slate-400 d-block small">Aberto em:</span>
                        <span class="text-white fw-medium">{{ $shift->opened_at->format('d/m/Y H:i:s') }}</span>
                    </div>
                    <div class="col-md-6 mb-2">
                        <span class="text-slate-400 d-block small">Fechado em:</span>
                        <span class="text-white fw-medium">{{ $shift->closed_at ? $shift->closed_at->format('d/m/Y H:i:s') : '-' }}</span>
                    </div>
                    <div class="col-md-6 mb-2">
                        <span class="text-slate-400 d-block small">Empresa:</span>
                        <span class="text-white fw-medium">{{ $shift->company->trade_name }}</span>
                    </div>
                    <div class="col-md-6 mb-2">
                        <span class="text-slate-400 d-block small">Unidade Física:</span>
                        <span class="text-white fw-medium">{{ $shift->unit->name }}</span>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end border-top border-slate-800 pt-3">
                <a href="{{ route('admin.cashier.index') }}" class="btn btn-secondary bg-slate-800 border-slate-700 text-white px-4">Voltar ao Caixa</a>
            </div>
        </div>
    </div>
</div>
@endsection
