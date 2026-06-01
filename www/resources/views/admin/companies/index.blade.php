@extends('layouts.admin')

@section('title', 'Empresas')
@section('page_title', 'Gerenciamento de Empresas (Tenants)')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <p class="text-muted m-0">Cadastre e configure os tenants/empresas que operam no ecossistema.</p>
    </div>
    <div>
        <a href="{{ route('admin.companies.create') }}" class="btn btn-premium-primary">
            <i class="bi bi-plus-circle-fill me-2"></i> Nova Empresa
        </a>
    </div>
</div>

<div class="card card-premium p-4">
    <x-table id="table-companies">
        <thead>
            <tr>
                <th data-data="uuid">UUID</th>
                <th data-data="trade_name">Nome Fantasia</th>
                <th data-data="document_number">CNPJ/CPF</th>
                <th data-data="email">E-mail</th>
                <th data-data="phone">Telefone</th>
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
            tableId: 'table-companies',
            ajaxUrl: "{{ route('admin.companies.datatable') }}",
            columns: [
                { data: 'uuid' },
                { data: 'trade_name' },
                { data: 'document_number' },
                { data: 'email' },
                { data: 'phone' },
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
