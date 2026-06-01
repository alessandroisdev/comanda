@extends('layouts.admin')

@section('title', 'Unidades Físicas')
@section('page_title', 'Unidades Físicas (Filiais)')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <p class="text-muted m-0">Cadastre e gerencie as filiais físicas vinculadas aos tenants.</p>
    </div>
    <div>
        <a href="{{ route('admin.units.create') }}" class="btn btn-premium-primary">
            <i class="bi bi-plus-circle-fill me-2"></i> Nova Unidade
        </a>
    </div>
</div>

<div class="card card-premium p-4">
    <x-table id="table-units">
        <thead>
            <tr>
                <th data-data="uuid">UUID</th>
                <th data-data="name">Nome da Filial</th>
                <th data-data="company_name">Empresa Proprietária</th>
                <th data-data="city">Cidade</th>
                <th data-data="state">UF</th>
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
            tableId: 'table-units',
            ajaxUrl: "{{ route('admin.units.datatable') }}",
            columns: [
                { data: 'uuid' },
                { data: 'name' },
                { data: 'company_name' },
                { data: 'city' },
                { data: 'state' },
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
