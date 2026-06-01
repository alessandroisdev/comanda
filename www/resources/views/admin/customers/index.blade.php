@extends('layouts.admin')

@section('title', 'Clientes')
@section('page_title', 'Carteira de Clientes (Consumidores)')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <p class="text-muted m-0">Gerencie a base de clientes do ecossistema, incluindo consentimento de marketing e dados de fidelidade.</p>
    </div>
    <div>
        <a href="{{ route('admin.customers.create') }}" class="btn btn-premium-primary">
            <i class="bi bi-plus-circle-fill me-2"></i> Novo Cliente
        </a>
    </div>
</div>

<div class="card card-premium p-4">
    <x-table id="table-customers">
        <thead>
            <tr>
                <th data-data="name">Nome Completo</th>
                <th data-data="email">E-mail</th>
                <th data-data="phone">Telefone</th>
                <th data-data="company_name">Empresa de Origem</th>
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
            tableId: 'table-customers',
            ajaxUrl: "{{ route('admin.customers.datatable') }}",
            columns: [
                { data: 'name' },
                { data: 'email' },
                { data: 'phone' },
                { data: 'company_name' },
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
