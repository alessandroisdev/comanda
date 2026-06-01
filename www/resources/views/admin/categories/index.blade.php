@extends('layouts.admin')

@section('title', 'Categorias do Cardápio')
@section('page_title', 'Categorias do Cardápio / Menu')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <p class="text-muted m-0">Organize seus produtos e cardápios em divisões lógicas (ex: Entradas, Bebidas, Sobremesas).</p>
    </div>
    <div>
        <a href="{{ route('admin.categories.create') }}" class="btn btn-premium-primary">
            <i class="bi bi-plus-circle-fill me-2"></i> Nova Categoria
        </a>
    </div>
</div>

<div class="card card-premium p-4">
    <x-table id="table-categories">
        <thead>
            <tr>
                <th data-data="sort_order">Ordem</th>
                <th data-data="name">Nome da Categoria</th>
                <th data-data="description">Descrição</th>
                <th data-data="company_name">Empresa Proprietária</th>
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
            tableId: 'table-categories',
            ajaxUrl: "{{ route('admin.categories.datatable') }}",
            order: [[0, 'asc']], // Ordenação padrão pela ordem de exibição
            columns: [
                { 
                    data: 'sort_order',
                    render: function(data) {
                        return `<span class="badge bg-slate-800 border border-slate-700 px-2 py-1 rounded-3">${data}</span>`;
                    }
                },
                { data: 'name' },
                { data: 'description' },
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
