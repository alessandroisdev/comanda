@extends('layouts.admin')

@section('title', 'Produtos')
@section('page_title', 'Catálogo de Produtos')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <p class="text-muted m-0">Cadastre e configure todos os produtos comercializados pelas unidades locais.</p>
    </div>
    <div>
        <a href="{{ route('admin.products.create') }}" class="btn btn-premium-primary">
            <i class="bi bi-plus-circle-fill me-2"></i> Novo Produto
        </a>
    </div>
</div>

<div class="card card-premium p-4">
    <x-table id="table-products">
        <thead>
            <tr>
                <th data-data="sku">SKU</th>
                <th data-data="name">Nome do Produto</th>
                <th data-data="category_name">Categoria</th>
                <th data-data="price_formatted">Preço de Venda</th>
                <th data-data="cost_formatted">Preço de Custo</th>
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
            tableId: 'table-products',
            ajaxUrl: "{{ route('admin.products.datatable') }}",
            columns: [
                { 
                    data: 'sku',
                    render: function(data) {
                        return `<code class="text-info">${data}</code>`;
                    }
                },
                { data: 'name' },
                { data: 'category_name' },
                { 
                    data: 'price_formatted',
                    render: function(data) {
                        return `<strong class="text-success">${data}</strong>`;
                    }
                },
                { data: 'cost_formatted' },
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
