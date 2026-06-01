@extends('layouts.admin')

@section('title', 'Controle de Mesas')
@section('page_title', 'Controle e Gestão de Mesas')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <p class="text-muted m-0">Visualize e controle o estado físico das mesas do estabelecimento em tempo real.</p>
    </div>
    <div>
        <a href="{{ route('admin.tables.create') }}" class="btn btn-premium-primary">
            <i class="bi bi-plus-circle-fill me-2"></i> Nova Mesa
        </a>
    </div>
</div>

<!-- Grid Reativa de Mesas (Visão Geral em Tempo Real) -->
<h5 class="text-white mb-3 fw-bold"><i class="bi bi-grid-3x3-gap me-2 text-primary"></i> Mapa de Mesas (Tempo Real via SSE)</h5>
<div class="row row-cols-2 row-cols-md-4 row-cols-lg-6 g-3 mb-5" id="tables-map-grid">
    <!-- Será preenchido via AJAX / Javascript para manter reatividade total -->
</div>

<div class="card card-premium p-4">
    <h5 class="text-white mb-3 fw-bold"><i class="bi bi-list-stars me-2 text-primary"></i> Listagem Administrativa</h5>
    <x-table id="table-tables">
        <thead>
            <tr>
                <th data-data="code">Código</th>
                <th data-data="name">Nome / Número</th>
                <th data-data="sector">Setor</th>
                <th data-data="capacity">Capacidade</th>
                <th data-data="company_name">Empresa</th>
                <th data-data="unit_name">Unidade</th>
                <th data-data="status_label">Status</th>
                <th data-data="actions" data-orderable="false" data-searchable="false">Ações</th>
            </tr>
        </thead>
    </x-table>
</div>
@endsection

@section('scripts')
<script type="module">
    document.addEventListener('DOMContentLoaded', () => {
        // Inicializa o DataTable Administrativo
        const dataTable = new window.ComandaDataTable({
            tableId: 'table-tables',
            ajaxUrl: "{{ route('admin.tables.datatable') }}",
            order: [[1, 'asc']],
            columns: [
                { data: 'code' },
                { data: 'name' },
                { data: 'sector' },
                { 
                    data: 'capacity',
                    render: function(data) {
                        return `<i class="bi bi-people me-1 text-primary"></i> ${data} pessoas`;
                    }
                },
                { data: 'company_name' },
                { data: 'unit_name' },
                { 
                    data: 'status_label',
                    render: function(data, type, row) {
                        let badgeClass = 'badge-premium-active';
                        if (row.status === 'occupied') badgeClass = 'bg-danger text-white border border-danger-subtle';
                        else if (row.status === 'reserved') badgeClass = 'bg-warning text-dark border border-warning-subtle';
                        else if (row.status === 'blocked') badgeClass = 'bg-secondary text-white border border-secondary-subtle';
                        else if (row.status === 'cleaning') badgeClass = 'bg-info text-dark border border-info-subtle';
                        
                        return `<span class="badge ${badgeClass} px-3 py-2 rounded-pill font-semibold">${data}</span>`;
                    }
                },
                { data: 'actions', orderable: false, searchable: false }
            ]
        });

        // Carrega o Mapa de Mesas do estabelecimento
        function loadTablesMap() {
            fetch("{{ route('admin.tables.datatable') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ length: 100 })
            })
            .then(res => res.json())
            .then(res => {
                const grid = document.getElementById('tables-map-grid');
                grid.innerHTML = '';
                
                if (res.data.length === 0) {
                    grid.innerHTML = '<div class="col-12"><div class="alert alert-secondary text-center py-4 bg-slate-900 border border-slate-800">Nenhuma mesa cadastrada.</div></div>';
                    return;
                }

                res.data.forEach(table => {
                    let cardBg = 'rgba(30, 41, 59, 0.4)';
                    let borderCol = '#1e293b';
                    let statusCol = '#94a3b8';
                    let pulseClass = '';

                    if (table.status === 'available') {
                        cardBg = 'rgba(16, 185, 129, 0.1)';
                        borderCol = 'rgba(16, 185, 129, 0.3)';
                        statusCol = '#10b981';
                    } else if (table.status === 'occupied') {
                        cardBg = 'rgba(239, 68, 68, 0.1)';
                        borderCol = 'rgba(239, 68, 68, 0.3)';
                        statusCol = '#ef4444';
                        pulseClass = 'animate-pulse';
                    } else if (table.status === 'reserved') {
                        cardBg = 'rgba(245, 158, 11, 0.1)';
                        borderCol = 'rgba(245, 158, 11, 0.3)';
                        statusCol = '#f59e0b';
                    } else if (table.status === 'cleaning') {
                        cardBg = 'rgba(6, 182, 212, 0.1)';
                        borderCol = 'rgba(6, 182, 212, 0.3)';
                        statusCol = '#06b6d4';
                    }

                    const cardHtml = `
                        <div class="col" id="table-card-${table.uuid}">
                            <div class="card p-3 text-center transition-all h-100 card-premium" style="background: ${cardBg}; border: 1px solid ${borderCol};">
                                <h6 class="text-white fw-bold m-0 fs-5">${table.name}</h6>
                                <small class="text-muted d-block mb-2">Cód: ${table.code} • ${table.sector}</small>
                                <div class="d-flex align-items-center justify-content-center gap-1 my-2">
                                    <span class="rounded-circle d-inline-block ${pulseClass}" style="width: 8px; height: 8px; background-color: ${statusCol};"></span>
                                    <span style="color: ${statusCol}; font-size: 0.8rem; font-weight: 600;">${table.status_label}</span>
                                </div>
                                <div class="mt-2 pt-2 border-top border-slate-800">
                                    <span class="text-muted" style="font-size: 0.75rem;"><i class="bi bi-people me-1"></i> Cap: ${table.capacity}</span>
                                </div>
                            </div>
                        </div>
                    `;
                    grid.insertAdjacentHTML('beforeend', cardHtml);
                });
            });
        }

        loadTablesMap();

        // Escuta canal SSE para atualização de status em tempo real sem refresh
        const sseSource = new EventSource('/sse/stream/admin.tables');
        
        sseSource.addEventListener('tables.status_changed', (e) => {
            const eventData = JSON.parse(e.data);
            console.log('SSE status changed:', eventData);
            
            // Recarrega o mapa de mesas e o DataTable de forma transparente
            loadTablesMap();
            if (window.LaravelDataTables && window.LaravelDataTables['table-tables']) {
                window.LaravelDataTables['table-tables'].ajax.reload(null, false);
            }
        });

        // Libera conexão SSE ao destruir a página
        window.addEventListener('beforeunload', () => {
            sseSource.close();
        });
    });
</script>
@endsection
