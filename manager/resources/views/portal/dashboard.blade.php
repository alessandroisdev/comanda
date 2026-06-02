@extends('layouts.portal')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Painel de Controle Comercial</h1>
</div>

<div class="row">
    <div class="col-md-3 mb-4">
        <div class="card p-3 border-start border-primary border-4">
            <small class="text-uppercase text-muted fw-bold">Licenças Emitidas</small>
            <div class="fs-2 fw-bold text-dark">{{ $totalLicenses }}</div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card p-3 border-start border-success border-4">
            <small class="text-uppercase text-muted fw-bold">Ativações Ativas</small>
            <div class="fs-2 fw-bold text-success">{{ $totalActivations }}</div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card p-3 border-start border-warning border-4">
            <small class="text-uppercase text-muted fw-bold">Instalações Cadastradas</small>
            <div class="fs-2 fw-bold text-warning">{{ $totalInstallations }}</div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card p-3 border-start border-danger border-4">
            <small class="text-uppercase text-muted fw-bold">Módulos no Catálogo</small>
            <div class="fs-2 fw-bold text-danger">{{ $totalModules }}</div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-8 mb-4">
        <div class="card p-4">
            <h5 class="fw-bold mb-3">Últimas Licenças Emitidas</h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>Plano</th>
                            <th>Tipo</th>
                            <th>Status</th>
                            <th>Data de Expiração</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentLicenses as $lic)
                            <tr>
                                <td>
                                    <strong>{{ $lic->client_name }}</strong><br>
                                    <small class="text-muted">{{ $lic->client_email }}</small>
                                </td>
                                <td>{{ $lic->plan_name }}</td>
                                <td>
                                    <span class="badge bg-secondary">{{ $lic->type }}</span>
                                </td>
                                <td>
                                    @if($lic->status === 'active')
                                        <span class="badge bg-success">Ativa</span>
                                    @elseif($lic->status === 'trial')
                                        <span class="badge bg-info">Trial</span>
                                    @elseif($lic->status === 'expired')
                                        <span class="badge bg-danger">Expirada</span>
                                    @else
                                        <span class="badge bg-warning">{{ $lic->status }}</span>
                                    @endif
                                </td>
                                <td>{{ $lic->expires_at ? $lic->expires_at->format('d/m/Y') : 'Vitalícia' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4">Nenhuma licença emitida ainda.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="card p-4">
            <h5 class="fw-bold mb-3">Últimas Ações de Auditoria</h5>
            <ul class="list-group list-group-flush">
                @forelse($recentAuditLogs as $log)
                    <li class="list-group-item px-0">
                        <div class="d-flex justify-content-between">
                            <strong>{{ strtoupper($log->action) }}</strong>
                            <small class="text-muted">{{ $log->created_at ? $log->created_at->diffForHumans() : '' }}</small>
                        </div>
                        <small class="text-muted">
                            Licença: {{ $log->license?->client_name ?? 'N/A' }}<br>
                            IP: {{ $log->ip_address }}
                        </small>
                    </li>
                @empty
                    <li class="list-group-item px-0 text-center py-4 text-muted">Nenhum log gravado.</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>
@endsection
