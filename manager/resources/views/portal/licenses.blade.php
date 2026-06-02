@extends('layouts.portal')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Licenças & Contratos</h1>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createLicenseModal">
        🆕 Emitir Nova Licença
    </button>
</div>

<div class="card p-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>Cliente</th>
                    <th>Plano</th>
                    <th>Tipo</th>
                    <th>Status</th>
                    <th>Módulos</th>
                    <th>Validade</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($licenses as $lic)
                    <tr>
                        <td>
                            <strong>{{ $lic->client_name }}</strong><br>
                            <small class="text-muted">Doc: {{ $lic->client_document }}</small><br>
                            <small class="text-muted">Email: {{ $lic->client_email }}</small>
                        </td>
                        <td>{{ $lic->plan_name }}</td>
                        <td>
                            <span class="badge bg-secondary">{{ strtoupper($lic->type) }}</span>
                        </td>
                        <td>
                            @if($lic->status === 'active')
                                <span class="badge bg-success">Ativa</span>
                            @elseif($lic->status === 'trial')
                                <span class="badge bg-info">Trial</span>
                            @elseif($lic->status === 'expired')
                                <span class="badge bg-danger">Expirada</span>
                            @elseif($lic->status === 'suspended')
                                <span class="badge bg-warning">Suspensa</span>
                            @else
                                <span class="badge bg-secondary">{{ $lic->status }}</span>
                            @endif
                        </td>
                        <td>
                            @foreach($lic->modules as $mod)
                                <span class="badge bg-light text-dark border">{{ $mod->code }}</span>
                            @endforeach
                        </td>
                        <td>
                            Issued: {{ $lic->issued_at ? $lic->issued_at->format('d/m/Y') : '-' }}<br>
                            Expires: {{ $lic->expires_at ? $lic->expires_at->format('d/m/Y') : 'Vitalícia' }}
                        </td>
                        <td>
                            <div class="btn-group">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    Ações
                                </button>
                                <ul class="dropdown-menu">
                                    <li>
                                        <form action="/portal/licenses/{{ $lic->id }}/renew" method="POST" class="d-inline p-0">
                                            @csrf
                                            <input type="hidden" name="expires_at" value="{{ \Carbon\Carbon::now()->addYear()->format('Y-m-d') }}">
                                            <button class="dropdown-item" type="submit">➕ Renovar (+1 Ano)</button>
                                        </form>
                                    </li>
                                    <li>
                                        <form action="/portal/licenses/{{ $lic->id }}/suspend" method="POST" class="d-inline p-0">
                                            @csrf
                                            <button class="dropdown-item text-warning" type="submit">⏸️ Suspender</button>
                                        </form>
                                    </li>
                                    <li>
                                        <form action="/portal/licenses/{{ $lic->id }}/cancel" method="POST" class="d-inline p-0">
                                            @csrf
                                            <button class="dropdown-item text-danger" type="submit">🚫 Cancelar</button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4">Nenhuma licença comercial registrada.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Create License Modal -->
<div class="modal fade" id="createLicenseModal" tabindex="-1" aria-labelledby="createLicenseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createLicenseModalLabel">Emitir Nova Licença Comercial</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="/portal/licenses" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nome do Cliente</label>
                            <input type="text" name="client_name" class="form-control" required placeholder="Ex: Alessandro Dev">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">E-mail Comercial</label>
                            <input type="email" name="client_email" class="form-control" required placeholder="Ex: alessandro@site.com">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Documento (CPF ou CNPJ)</label>
                            <input type="text" name="client_document" class="form-control" required placeholder="Ex: 12.345.678/0001-99">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nome do Plano</label>
                            <input type="text" name="plan_name" class="form-control" required placeholder="Ex: Enterprise Plan">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tipo de Licença</label>
                            <select name="type" class="form-select">
                                <option value="subscription">Subscription (Assinatura)</option>
                                <option value="trial">Trial (Degustação)</option>
                                <option value="perpetual">Perpetual (Vitalícia)</option>
                                <option value="developer">Developer (Desenvolvimento)</option>
                                <option value="internal">Internal (Uso Interno)</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Data de Expiração</label>
                            <input type="date" name="expires_at" class="form-control" value="{{ \Carbon\Carbon::now()->addYear()->format('Y-m-d') }}">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Módulos Comercializáveis do Catálogo</label>
                        <div class="row">
                            @foreach($modules as $mod)
                                <div class="col-md-4 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="modules[]" value="{{ $mod->code }}" id="mod_{{ $mod->id }}">
                                        <label class="form-check-input-label" for="mod_{{ $mod->id }}">
                                            {{ $mod->name }} (<small class="text-muted">{{ $mod->code }}</small>)
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                    <button type="submit" class="btn btn-primary">Emitir Licença</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
