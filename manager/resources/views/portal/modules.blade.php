@extends('layouts.portal')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Catálogo de Módulos</h1>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createModuleModal">
        🆕 Cadastrar Novo Módulo
    </button>
</div>

<div class="card p-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Nome</th>
                    <th>Descrição</th>
                    <th>Versão Mínima</th>
                    <th>Preço Sugerido</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($modules as $mod)
                    <tr>
                        <td><code>{{ $mod->code }}</code></td>
                        <td><strong>{{ $mod->name }}</strong></td>
                        <td>{{ $mod->description }}</td>
                        <td>{{ $mod->version_min }}</td>
                        <td>R$ {{ number_format($mod->price_suggested_cents / 100, 2, ',', '.') }}</td>
                        <td>
                            @if($mod->status === 'active')
                                <span class="badge bg-success">Ativo</span>
                            @else
                                <span class="badge bg-danger">Inativo</span>
                            @endif
                        </td>
                        <td>
                            <form action="/portal/modules/{{ $mod->id }}/toggle" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-secondary">
                                    🔄 Alternar Status
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4">Nenhum módulo cadastrado.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Create Module Modal -->
<div class="modal fade" id="createModuleModal" tabindex="-1" aria-labelledby="createModuleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createModuleModalLabel">Cadastrar Módulo Comercial</h5>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <form action="/portal/modules" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Código Único (Sem espaços)</label>
                        <input type="text" name="code" class="form-control" required placeholder="Ex: bi, loyalty, integrations">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nome Comercial</label>
                        <input type="text" name="name" class="form-control" required placeholder="Ex: Business Intelligence Avançado">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descrição Detalhada</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Insira o resumo funcional do módulo comercial..."></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Versão Mínima</label>
                            <input type="text" name="version_min" class="form-control" required value="1.0.0">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Preço Sugerido (Centavos)</label>
                            <input type="number" name="price_suggested_cents" class="form-control" required value="9900" placeholder="Ex: 9900 para R$ 99,00">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                    <button type="submit" class="btn btn-primary">Cadastrar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
