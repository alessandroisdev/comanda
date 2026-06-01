@extends('layouts.admin')

@section('title', 'Comandas Operacionais')
@section('page_title', 'Comandas / Atendimentos')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <p class="text-muted m-0">Acompanhe sessões de atendimento, mesas ativas e consumo de clientes.</p>
    </div>
    <div>
        <a href="{{ route('admin.sessions.create') }}" class="btn btn-premium-primary">
            <i class="bi bi-plus-circle-fill me-2"></i> Abrir Nova Comanda
        </a>
    </div>
</div>

<div class="card card-premium p-4">
    <x-table id="table-sessions">
        <thead>
            <tr>
                <th data-data="table_name">Mesa / Sessão</th>
                <th data-data="status_label">Estado</th>
                <th data-data="people_count">Clientes</th>
                <th data-data="opened_by_name">Aberto Por</th>
                <th data-data="opened_at">Horário Abertura</th>
                <th data-data="closed_at">Horário Fechamento</th>
                <th data-data="actions" data-orderable="false" data-searchable="false">Ações</th>
            </tr>
        </thead>
    </x-table>
</div>

<!-- Modal para Transferir Mesa -->
<div class="modal fade" id="modalTransferSession" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content bg-slate-900 border border-slate-800 text-white">
            <div class="modal-header border-slate-800">
                <h5 class="modal-title fw-bold"><i class="bi bi-arrow-left-right text-primary me-2"></i> Transferir Mesa</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form-transfer-session">
                <div class="modal-body">
                    <input type="hidden" id="transfer-session-uuid">
                    <div class="mb-3">
                        <label for="transfer-new-table-uuid" class="form-label">Selecione a Nova Mesa</label>
                        @php
                            $employee = Auth::guard('employee')->user();
                            $companyId = $employee ? $employee->company_id : null;
                            $tables = \App\Models\Table::where('status', 'available');
                            if($companyId) {
                                $tables->where('company_id', $companyId);
                            }
                            $tables = $tables->get();
                        @endphp
                        <select id="transfer-new-table-uuid" class="form-select bg-slate-950 border-slate-800 text-white" required>
                            <option value="">Selecione a mesa livre...</option>
                            @foreach($tables as $table)
                                <option value="{{ $table->uuid }}">{{ $table->name }} ({{ $table->sector }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-slate-800">
                    <button type="button" class="btn btn-secondary bg-slate-800 border-slate-700" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-premium-primary">Confirmar Transferência</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script type="module">
    document.addEventListener('DOMContentLoaded', () => {
        // Inicializa o DataTable Administrativo
        const dataTable = new window.ComandaDataTable({
            tableId: 'table-sessions',
            ajaxUrl: "{{ route('admin.sessions.datatable') }}",
            order: [[4, 'desc']], // Mais recentes primeiro
            columns: [
                { 
                    data: 'table_name',
                    render: function(data, type, row) {
                        return `<strong class="text-white">${data}</strong> <small class="text-muted d-block">Mesa: ${row.table_code}</small>`;
                    }
                },
                { 
                    data: 'status_label',
                    render: function(data, type, row) {
                        let badgeClass = 'bg-success text-white border border-success-subtle';
                        if (row.status === 'closed') badgeClass = 'bg-secondary text-slate-300 border border-secondary-subtle';
                        else if (row.status === 'cancelled') badgeClass = 'bg-danger text-white border border-danger-subtle';
                        
                        return `<span class="badge ${badgeClass} px-3 py-1 rounded-pill font-semibold">${data}</span>`;
                    }
                },
                { data: 'people_count' },
                { data: 'opened_by_name' },
                { 
                    data: 'opened_at',
                    render: function(data) {
                        return new Date(data).toLocaleString('pt-BR');
                    }
                },
                { 
                    data: 'closed_at',
                    render: function(data) {
                        return data !== '-' ? new Date(data).toLocaleString('pt-BR') : '<span class="text-slate-500">-</span>';
                    }
                },
                { data: 'actions', orderable: false, searchable: false }
            ]
        });

        // Eventos das Comandas
        document.querySelector('#table-sessions').addEventListener('click', (e) => {
            const btnClose = e.target.closest('.btn-close-session');
            const btnCancel = e.target.closest('.btn-cancel-session');
            const btnTransfer = e.target.closest('.btn-transfer-session');

            if (btnClose) {
                const uuid = btnClose.getAttribute('data-uuid');
                if (confirm('Deseja fechar/encerrar esta comanda?')) {
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
                            if (window.LaravelDataTables && window.LaravelDataTables['table-sessions']) {
                                window.LaravelDataTables['table-sessions'].ajax.reload(null, false);
                            }
                        } else {
                            alert(res.message || 'Erro ao fechar comanda.');
                        }
                    });
                }
            }

            if (btnCancel) {
                const uuid = btnCancel.getAttribute('data-uuid');
                if (confirm('Deseja cancelar esta comanda e todos os pedidos associados?')) {
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
                            if (window.LaravelDataTables && window.LaravelDataTables['table-sessions']) {
                                window.LaravelDataTables['table-sessions'].ajax.reload(null, false);
                            }
                        } else {
                            alert(res.message || 'Erro ao cancelar comanda.');
                        }
                    });
                }
            }

            if (btnTransfer) {
                const uuid = btnTransfer.getAttribute('data-uuid');
                document.getElementById('transfer-session-uuid').value = uuid;
                const modal = new bootstrap.Modal(document.getElementById('modalTransferSession'));
                modal.show();
            }
        });

        // Envio do formulário de transferência
        document.getElementById('form-transfer-session').addEventListener('submit', (e) => {
            e.preventDefault();
            const uuid = document.getElementById('transfer-session-uuid').value;
            const tableUuid = document.getElementById('transfer-new-table-uuid').value;

            fetch(`/admin/sessions/${uuid}/transfer`, {
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
                    bootstrap.Modal.getInstance(document.getElementById('modalTransferSession')).hide();
                    if (window.LaravelDataTables && window.LaravelDataTables['table-sessions']) {
                        window.LaravelDataTables['table-sessions'].ajax.reload(null, false);
                    }
                } else {
                    alert(res.message || 'Erro ao transferir comanda.');
                }
            });
        });

        // Escuta canal SSE para atualização de comandas em tempo real
        const sseSource = new EventSource('/sse/stream/admin.sessions');
        
        sseSource.addEventListener('session.opened', () => reloadDataTable());
        sseSource.addEventListener('session.closed', () => reloadDataTable());
        sseSource.addEventListener('session.cancelled', () => reloadDataTable());
        sseSource.addEventListener('session.transferred', () => reloadDataTable());
        sseSource.addEventListener('session.merged', () => reloadDataTable());

        function reloadDataTable() {
            if (window.LaravelDataTables && window.LaravelDataTables['table-sessions']) {
                window.LaravelDataTables['table-sessions'].ajax.reload(null, false);
            }
        }

        window.addEventListener('beforeunload', () => {
            sseSource.close();
        });
    });
</script>
@endsection
