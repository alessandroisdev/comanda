@extends('layouts.admin')

@section('title', 'Caixa Operacional')
@section('page_title', 'Caixa / Movimentação Financeira')

@section('content')
<div class="row">
    <!-- Turno Ativo / Formulário de Abertura ou Fechamento -->
    <div class="col-lg-5 mb-4">
        @if($activeShift)
            <!-- Caixa Aberto -->
            <div class="card card-premium p-4 border border-success border-opacity-25 h-100">
                <div class="d-flex justify-content-between align-items-center mb-4 border-bottom border-slate-800 pb-3">
                    <div>
                        <span class="badge bg-success-subtle text-success border border-success border-opacity-50 px-3 py-1 rounded-pill fw-bold">CAIXA ABERTO</span>
                    </div>
                    <span class="text-muted small">Turno ID: {{ substr($activeShift->uuid, 0, 8) }}</span>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-6">
                        <div class="card bg-slate-900 border border-slate-800 p-3 rounded-3 text-center">
                            <span class="text-muted small d-block mb-1">Fundo de Abertura</span>
                            <span class="fs-4 text-white fw-bold">R$ {{ number_format($activeShift->opening_amount_cents / 100, 2, ',', '.') }}</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="card bg-slate-900 border border-slate-800 p-3 rounded-3 text-center">
                            <span class="text-muted small d-block mb-1">Aberto em</span>
                            <span class="fs-6 text-white fw-bold d-block mt-2">{{ $activeShift->opened_at->format('d/m H:i') }}</span>
                        </div>
                    </div>
                </div>

                <div class="border-top border-slate-800 pt-3 mb-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-slate-400">Operador:</span>
                        <span class="text-white fw-semibold">{{ $activeShift->openedByEmployee->name ?? 'Sistema' }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-slate-400">Empresa:</span>
                        <span class="text-white fw-semibold">{{ $activeShift->company->trade_name }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-slate-400">Unidade:</span>
                        <span class="text-white fw-semibold">{{ $activeShift->unit->name }}</span>
                    </div>
                </div>

                <div class="mt-auto">
                    <button type="button" class="btn btn-danger w-100 py-2 fw-semibold" data-bs-toggle="modal" data-bs-target="#modalCloseShift">
                        <i class="bi bi-lock-fill me-1"></i> Fechar Turno de Caixa
                    </button>
                </div>
            </div>
        @else
            <!-- Caixa Fechado -->
            <div class="card card-premium p-4 border border-danger border-opacity-25 h-100">
                <div class="d-flex justify-content-between align-items-center mb-4 border-bottom border-slate-800 pb-3">
                    <div>
                        <span class="badge bg-danger-subtle text-danger border border-danger border-opacity-50 px-3 py-1 rounded-pill fw-bold">CAIXA FECHADO</span>
                    </div>
                </div>

                <p class="text-muted small">Não há nenhum turno de caixa ativo para o seu usuário. Abra o caixa com um valor inicial para começar a registrar transações.</p>

                @php
                    $employee = Auth::guard('employee')->user();
                    $companies = $employee ? \App\Models\Company::where('id', $employee->company_id)->get() : \App\Models\Company::all();
                    $units = $employee ? \App\Models\CompanyUnit::where('company_id', $employee->company_id)->get() : \App\Models\CompanyUnit::all();
                @endphp

                <form action="{{ route('admin.cashier.store') }}" method="POST" class="mt-3">
                    @csrf

                    <div class="mb-3">
                        <label for="company_id" class="form-label text-slate-300">Empresa Proprietária <span class="text-danger">*</span></label>
                        <select name="company_id" id="company_id" class="form-select bg-slate-900 border-slate-700 text-white" required>
                            <option value="">Selecione...</option>
                            @foreach($companies as $company)
                                <option value="{{ $company->id }}">{{ $company->trade_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="unit_id" class="form-label text-slate-300">Unidade Física <span class="text-danger">*</span></label>
                        <select name="unit_id" id="unit_id" class="form-select bg-slate-900 border-slate-700 text-white" required>
                            <option value="">Selecione...</option>
                            @foreach($units as $unit)
                                <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="opening_amount" class="form-label text-slate-300">Valor de Abertura (R$) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="opening_amount" id="opening_amount" class="form-control bg-slate-900 border-slate-700 text-white fs-5 text-center fw-bold" placeholder="0,00" value="0.00" required>
                    </div>

                    <button type="submit" class="btn btn-premium-primary w-100 py-2 fw-semibold">
                        <i class="bi bi-unlock-fill me-1"></i> Abrir Turno de Caixa
                    </button>
                </form>
            </div>
        @endif
    </div>

    <!-- Histórico de Caixas Anteriores -->
    <div class="col-lg-7 mb-4">
        <div class="card card-premium p-4 h-100">
            <h5 class="text-white mb-4 fw-bold"><i class="bi bi-clock-history me-2 text-primary"></i> Turnos de Caixa Anteriores (Histórico)</h5>

            @if($shifts->isEmpty())
                <div class="text-center py-5">
                    <div class="d-inline-flex align-items-center justify-content-center bg-slate-900 border border-slate-800 rounded-circle mb-3" style="width: 60px; height: 60px;">
                        <i class="bi bi-safe-fill text-muted fs-2"></i>
                    </div>
                    <h6 class="text-white fw-bold">Nenhum turno de caixa finalizado</h6>
                    <p class="text-muted small">Os turnos fechados aparecerão listados aqui.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-dark table-hover table-borderless align-middle m-0">
                        <thead>
                            <tr class="text-slate-400 border-bottom border-slate-800">
                                <th>Abertura</th>
                                <th>Operador</th>
                                <th class="text-end">Abertura</th>
                                <th class="text-end">Fechamento</th>
                                <th>Estado</th>
                                <th class="text-center" style="width: 50px;">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($shifts as $s)
                                <tr class="border-bottom border-slate-900">
                                    <td>
                                        <span class="text-white fw-semibold small d-block">{{ $s->opened_at->format('d/m/Y H:i') }}</span>
                                        @if($s->closed_at)
                                            <span class="text-muted small d-block">Até {{ $s->closed_at->format('d/m H:i') }}</span>
                                        @endif
                                    </td>
                                    <td class="text-slate-300 small">{{ $s->openedByEmployee->name ?? 'Sistema' }}</td>
                                    <td class="text-end text-slate-300">R$ {{ number_format($s->opening_amount_cents / 100, 2, ',', '.') }}</td>
                                    <td class="text-end text-emerald-400 fw-semibold">
                                        {{ $s->closing_amount_cents !== null ? 'R$ ' . number_format($s->closing_amount_cents / 100, 2, ',', '.') : '-' }}
                                    </td>
                                    <td>
                                        @php
                                            $stateBadge = 'bg-success text-white border border-success-subtle';
                                            if ($s->status->value === 'closed') $stateBadge = 'bg-secondary text-slate-300 border border-slate-700';
                                        @endphp
                                        <span class="badge {{ $stateBadge }} small">{{ $s->status->label() }}</span>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('admin.cashier.show', $s->uuid) }}" class="btn btn-sm btn-outline-info" title="Visualizar Detalhes"><i class="bi bi-eye"></i></a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal para Fechamento de Caixa -->
@if($activeShift)
<div class="modal fade" id="modalCloseShift" tabindex="-1" aria-labelledby="modalCloseShiftLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content bg-slate-900 border border-slate-800 text-white">
            <div class="modal-header border-slate-800">
                <h5 class="modal-title fw-bold" id="modalCloseShiftLabel"><i class="bi bi-lock-fill text-danger me-2"></i> Fechar Turno de Caixa</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.cashier.close', $activeShift->uuid) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <p class="text-muted small">Insira o valor em dinheiro físico (e outras formas) somado em caixa no final do dia para conciliação.</p>
                    <div class="mb-3">
                        <label for="closing_amount" class="form-label">Valor de Fechamento (R$) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="closing_amount" id="closing_amount" class="form-control bg-slate-950 border-slate-800 text-white fs-4 text-center fw-bold" placeholder="0,00" required>
                    </div>
                </div>
                <div class="modal-footer border-slate-800">
                    <button type="button" class="btn btn-secondary bg-slate-800 border-slate-700" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Confirmar Fechamento</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@endsection
