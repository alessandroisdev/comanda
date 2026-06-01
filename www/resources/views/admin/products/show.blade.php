@extends('layouts.admin')

@section('title', 'Detalhes do Produto')
@section('page_title', 'Produto: ' . $product->name)

@section('content')
<div class="mb-4 d-flex justify-content-between align-items-center">
    <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary btn-sm rounded-3">
        <i class="bi bi-arrow-left me-1"></i> Voltar para a Listagem
    </a>
    <a href="{{ route('admin.products.edit', $product->uuid) }}" class="btn btn-premium-primary btn-sm rounded-3">
        <i class="bi bi-pencil-square me-1"></i> Editar Dados
    </a>
</div>

<div class="row g-4">
    <div class="col-md-8">
        <div class="card card-premium p-4 shadow-lg border-0 mb-4">
            <h5 class="text-white border-bottom border-secondary pb-2 mb-3">
                <i class="bi bi-box-seam me-2 text-primary"></i>Informações Gerais
            </h5>
            <div class="row g-3">
                <div class="col-md-6">
                    <span class="text-muted d-block small">Nome do Produto</span>
                    <strong class="text-white fs-5">{{ $product->name }}</strong>
                </div>
                <div class="col-md-6">
                    <span class="text-muted d-block small">Categoria do Cardápio</span>
                    <strong class="text-white fs-5">{{ $product->category->name ?? '-' }}</strong>
                </div>
                <div class="col-md-3">
                    <span class="text-muted d-block small">Preço de Venda</span>
                    <strong class="text-success fs-5">R$ {{ number_format($product->price_cents / 100, 2, ',', '.') }}</strong>
                </div>
                <div class="col-md-3">
                    <span class="text-muted d-block small">Custo</span>
                    <span class="text-white fs-5">
                        @if($product->cost_cents)
                            R$ {{ number_format($product->cost_cents / 100, 2, ',', '.') }}
                        @else
                            -
                        @endif
                    </span>
                </div>
                <div class="col-md-3">
                    <span class="text-muted d-block small">SKU</span>
                    <span class="text-white">{{ $product->sku ?? '-' }}</span>
                </div>
                <div class="col-md-3">
                    <span class="text-muted d-block small">Código de Barras</span>
                    <span class="text-white">{{ $product->barcode ?? '-' }}</span>
                </div>

                <div class="col-12 mt-4">
                    <h6 class="text-white border-bottom border-secondary pb-1 mb-2">Estrutura Organizacional e Produção</h6>
                </div>
                <div class="col-md-4">
                    <span class="text-muted d-block small">Empresa Proprietária</span>
                    <span class="text-white"><i class="bi bi-building me-1 text-info"></i> {{ $product->company->trade_name ?? '-' }}</span>
                </div>
                <div class="col-md-4">
                    <span class="text-muted d-block small">Tempo Estimado na Cozinha</span>
                    <span class="text-white"><i class="bi bi-clock me-1 text-warning"></i> {{ $product->preparation_time }} minutos</span>
                </div>
                
                @if($product->description)
                    <div class="col-12 mt-4">
                        <span class="text-muted d-block small">Descrição / Ingredientes</span>
                        <p class="text-white mb-0" style="white-space: pre-line;">{{ $product->description }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card card-premium p-4 shadow-lg border-0 text-center mb-4">
            <div class="mb-3">
                @if($product->image)
                    <img src="{{ $product->image }}" alt="Imagem do {{ $product->name }}" class="img-fluid rounded-3 mb-2 animate-pulse" style="max-height: 150px; object-fit: cover;">
                @else
                    <div class="bg-dark rounded-3 d-flex align-items-center justify-content-center mx-auto" style="width: 120px; height: 120px;">
                        <i class="bi bi-box-seam text-muted fs-1"></i>
                    </div>
                @endif
            </div>
            <h5 class="text-white m-0">{{ $product->name }}</h5>
            <span class="text-muted small d-block mb-3">SKU: {{ $product->sku ?? '-' }}</span>
            <div>
                @if(($product->status->value ?? $product->status) == 'active')
                    <span class="badge-premium-active">Ativo</span>
                @else
                    <span class="badge-premium-inactive">Inativo</span>
                @endif
            </div>
        </div>

        <div class="card card-premium p-4 shadow-lg border-0">
            <h6 class="text-white mb-2">Histórico do Produto</h6>
            <div class="small">
                <span class="text-muted d-block">Cadastrado em:</span>
                <span class="text-white">{{ $product->created_at ? $product->created_at->format('d/m/Y H:i') : '-' }}</span>
                <span class="text-muted d-block mt-2">Última atualização:</span>
                <span class="text-white">{{ $product->updated_at ? $product->updated_at->format('d/m/Y H:i') : '-' }}</span>
            </div>
        </div>
    </div>
</div>
@endsection
