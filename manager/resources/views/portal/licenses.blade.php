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
                                        <button class="dropdown-item text-primary" type="button" data-bs-toggle="modal" data-bs-target="#editLicenseModal"
                                            data-id="{{ $lic->id }}"
                                            data-client-name="{{ $lic->client_name }}"
                                            data-client-email="{{ $lic->client_email }}"
                                            data-client-document="{{ $lic->client_document }}"
                                            data-plan-name="{{ $lic->plan_name }}"
                                            data-type="{{ $lic->type }}"
                                            data-status="{{ $lic->status }}"
                                            data-expires-at="{{ $lic->expires_at ? $lic->expires_at->format('Y-m-d') : '' }}"
                                            data-modules="{{ json_encode($lic->modules->pluck('code')->toArray()) }}">
                                            ✏️ Editar Dados
                                        </button>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
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

<!-- Edit License Modal -->
<div class="modal fade" id="editLicenseModal" tabindex="-1" aria-labelledby="editLicenseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editLicenseModalLabel">Editar Licença Comercial</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="" method="POST">
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
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Tipo de Licença</label>
                            <select name="type" class="form-select">
                                <option value="subscription">Subscription (Assinatura)</option>
                                <option value="trial">Trial (Degustação)</option>
                                <option value="perpetual">Perpetual (Vitalícia)</option>
                                <option value="developer">Developer (Desenvolvimento)</option>
                                <option value="internal">Internal (Uso Interno)</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Status da Licença</label>
                            <select name="status" class="form-select">
                                <option value="active">Ativa</option>
                                <option value="trial">Trial</option>
                                <option value="expired">Expirada</option>
                                <option value="suspended">Suspensa</option>
                                <option value="cancelled">Cancelada</option>
                                <option value="blocked">Bloqueada</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Data de Expiração</label>
                            <input type="date" name="expires_at" class="form-control">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Módulos Comercializáveis do Catálogo</label>
                        <div class="row">
                            @foreach($modules as $mod)
                                <div class="col-md-4 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="modules[]" value="{{ $mod->code }}" id="edit_mod_{{ $mod->id }}">
                                        <label class="form-check-input-label" for="edit_mod_{{ $mod->id }}">
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
                    <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const editModal = document.getElementById('editLicenseModal');
        if (editModal) {
            editModal.addEventListener('show.bs.modal', (event) => {
                const button = event.relatedTarget;
                
                // Extrai as informações dos data-* attributes
                const id = button.getAttribute('data-id');
                const clientName = button.getAttribute('data-client-name');
                const clientEmail = button.getAttribute('data-client-email');
                const clientDocument = button.getAttribute('data-client-document');
                const planName = button.getAttribute('data-plan-name');
                const type = button.getAttribute('data-type');
                const status = button.getAttribute('data-status');
                const expiresAt = button.getAttribute('data-expires-at');
                const modules = JSON.parse(button.getAttribute('data-modules') || '[]');

                // Atualiza a URL do formulário para submissão
                const form = editModal.querySelector('form');
                form.action = `/portal/licenses/${id}`;

                // Popula os campos de input
                editModal.querySelector('[name="client_name"]').value = clientName;
                editModal.querySelector('[name="client_email"]').value = clientEmail;
                editModal.querySelector('[name="client_document"]').value = clientDocument;
                editModal.querySelector('[name="plan_name"]').value = planName;
                editModal.querySelector('[name="type"]').value = type;
                editModal.querySelector('[name="status"]').value = status;
                editModal.querySelector('[name="expires_at"]').value = expiresAt;

                // Marca os checkboxes dos módulos da licença
                editModal.querySelectorAll('.form-check-input').forEach(checkbox => {
                    checkbox.checked = modules.includes(checkbox.value);
                });
            });
        }
    });
</script>
@endsection
