@extends('layouts.admin')

@section('title', 'Usuários de Painel')
@section('page_title', 'Usuários Administrativos de Painel')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <p class="text-muted m-0">Gerencie a equipe interna e os operadores do painel central corporativo.</p>
    </div>
    <div>
        <a href="{{ route('admin.users.create') }}" class="btn btn-premium-primary">
            <i class="bi bi-plus-circle-fill me-2"></i> Novo Usuário
        </a>
    </div>
</div>

<div class="card card-premium p-4">
    <x-table id="table-users">
        <thead>
            <tr>
                <th data-data="uuid">UUID</th>
                <th data-data="name">Nome do Usuário</th>
                <th data-data="email">E-mail</th>
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
            tableId: 'table-users',
            ajaxUrl: "{{ route('admin.users.datatable') }}",
            columns: [
                { data: 'uuid' },
                { data: 'name' },
                { data: 'email' },
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
