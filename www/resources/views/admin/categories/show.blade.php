@extends('layouts.admin')

@section('title', 'Detalhes da Categoria')
@section('page_title', 'Categoria: ' . $category->name)

@section('content')
<div class="mb-4 d-flex justify-content-between align-items-center">
    <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary btn-sm rounded-3">
        <i class="bi bi-arrow-left me-1"></i> Voltar para a Listagem
    </a>
    <a href="{{ route('admin.categories.edit', $category->uuid) }}" class="btn btn-premium-primary btn-sm rounded-3">
        <i class="bi bi-pencil-square me-1"></i> Editar Dados
    </a>
</div>

<div class="row g-4">
    <div class="col-md-8">
        <div class="card card-premium p-4 shadow-lg border-0 mb-4">
            <h5 class="text-white border-bottom border-secondary pb-2 mb-3">
                <i class="bi bi-tag-fill me-2 text-primary"></i>Informações Gerais
            </h5>
            <div class="row g-3">
                <div class="col-md-6">
                    <span class="text-muted d-block small">Nome da Categoria</span>
                    <strong class="text-white fs-5">{{ $category->name }}</strong>
                </div>
                <div class="col-md-6">
                    <span class="text-muted d-block small">Empresa Proprietária</span>
                    <strong class="text-white fs-5">{{ $category->company->trade_name ?? '-' }}</strong>
                </div>
                <div class="col-md-6">
                    <span class="text-muted d-block small">Ordem de Exibição</span>
                    <span class="text-white fs-5">{{ $category->sort_order }}</span>
                </div>
                @if($category->description)
                    <div class="col-12 mt-3">
                        <span class="text-muted d-block small">Descrição</span>
                        <p class="text-white mb-0" style="white-space: pre-line;">{{ $category->description }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card card-premium p-4 shadow-lg border-0 text-center mb-4">
            <div class="bg-dark rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 80px; height: 80px;">
                <i class="bi bi-tag text-primary fs-1"></i>
            </div>
            <h5 class="text-white m-0">{{ $category->name }}</h5>
            <span class="text-muted small d-block mb-3">UUID: {{ $category->uuid }}</span>
            <div>
                @if(($category->status->value ?? $category->status) == 'active')
                    <span class="badge-premium-active">Ativa</span>
                @else
                    <span class="badge-premium-inactive">Inativa</span>
                @endif
            </div>
        </div>

        <div class="card card-premium p-4 shadow-lg border-0">
            <h6 class="text-white mb-2">Histórico do Registro</h6>
            <div class="small">
                <span class="text-muted d-block">Cadastrado em:</span>
                <span class="text-white">{{ $category->created_at ? $category->created_at->format('d/m/Y H:i') : '-' }}</span>
                <span class="text-muted d-block mt-2">Última atualização:</span>
                <span class="text-white">{{ $category->updated_at ? $category->updated_at->format('d/m/Y H:i') : '-' }}</span>
            </div>
        </div>
    </div>
</div>
@endsection
