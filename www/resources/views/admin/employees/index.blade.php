@extends('layouts.admin')

@section('title', 'Funcionários')
@section('page_title', 'Gerenciamento da Equipe Operacional (Lojas)')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <p class="text-muted m-0">Gerencie a equipe de loja (garçons, caixas, cozinheiros, gerentes locais e entregadores).</p>
    </div>
    <div>
        <a href="{{ route('admin.employees.create') }}" class="btn btn-premium-primary">
            <i class="bi bi-plus-circle-fill me-2"></i> Novo Funcionário
        </a>
    </div>
</div>

<div class="card card-premium p-4">
    <x-table id="table-employees">
        <thead>
            <tr>
                <th data-data="employee_number">Matrícula</th>
                <th data-data="name">Nome Completo</th>
                <th data-data="company_name">Empresa</th>
                <th data-data="unit_name">Unidade Física</th>
                <th data-data="role_label">Cargo</th>
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
        new window.ComandaDataTable({
            tableId: 'table-employees',
            ajaxUrl: "{{ route('admin.employees.datatable') }}",
            columns: [
                { data: 'employee_number' },
                { data: 'name' },
                { data: 'company_name' },
                { data: 'unit_name' },
                { 
                    data: 'role_label',
                    render: function(data, type, row) {
                        return `<span class="badge bg-slate-800 border border-slate-700 text-slate-300 px-3 py-2 rounded-pill">${data}</span>`;
                    }
                },
                { 
                    data: 'status_label',
                    render: function(data, type, row) {
                        const badgeClass = row.status === 'active' ? 'badge-premium-active' : 'badge-premium-inactive';
                        return `<span class="${badgeClass}">${data}</span>`;
                    }
                },
                { data: 'actions', orderable: false, searchable: false }
            ]
        });
    });
</script>
@endsection
