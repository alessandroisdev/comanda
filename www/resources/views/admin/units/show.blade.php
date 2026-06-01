@extends('layouts.admin')

@section('title', 'Detalhes da Unidade')
@section('page_title', 'Unidade: ' . $unit->name)

@section('content')
<div class="mb-4 d-flex justify-content-between align-items-center">
    <a href="{{ route('admin.units.index') }}" class="btn btn-outline-secondary btn-sm rounded-3">
        <i class="bi bi-arrow-left me-1"></i> Voltar para a Listagem
    </a>
    <a href="{{ route('admin.units.edit', $unit->uuid) }}" class="btn btn-premium-primary btn-sm rounded-3">
        <i class="bi bi-pencil-square me-1"></i> Editar Dados
    </a>
</div>

<div class="row g-4">
    <div class="col-md-8">
        <div class="card card-premium p-4 shadow-lg border-0 mb-4">
            <h5 class="text-white border-bottom border-secondary pb-2 mb-3">
                <i class="bi bi-geo-alt me-2 text-primary"></i>Informações Gerais e Endereço
            </h5>
            <div class="row g-3">
                <div class="col-md-6">
                    <span class="text-muted d-block small">Nome da Unidade</span>
                    <strong class="text-white fs-5">{{ $unit->name }}</strong>
                </div>
                <div class="col-md-6">
                    <span class="text-muted d-block small">Empresa Proprietária</span>
                    <strong class="text-white fs-5">{{ $unit->company->trade_name ?? '-' }}</strong>
                </div>
                <div class="col-md-4">
                    <span class="text-muted d-block small">CNPJ / CPF</span>
                    <span class="text-white">{{ $unit->document_number ?? '-' }}</span>
                </div>
                <div class="col-md-4">
                    <span class="text-muted d-block small">E-mail da Filial</span>
                    <span class="text-white">{{ $unit->email ?? '-' }}</span>
                </div>
                <div class="col-md-4">
                    <span class="text-muted d-block small">Telefone da Filial</span>
                    <span class="text-white">{{ $unit->phone ?? '-' }}</span>
                </div>

                <div class="col-12 mt-4">
                    <h6 class="text-white border-bottom border-secondary pb-1 mb-2">Endereço Físico</h6>
                </div>
                <div class="col-md-3">
                    <span class="text-muted d-block small">CEP</span>
                    <span class="text-white">{{ $unit->zipcode }}</span>
                </div>
                <div class="col-md-6">
                    <span class="text-muted d-block small">Rua / Logradouro</span>
                    <span class="text-white">{{ $unit->street }}, nº {{ $unit->number }}</span>
                </div>
                <div class="col-md-3">
                    <span class="text-muted d-block small">Bairro</span>
                    <span class="text-white">{{ $unit->district }}</span>
                </div>
                <div class="col-md-4">
                    <span class="text-muted d-block small">Cidade / Estado</span>
                    <span class="text-white">{{ $unit->city }} — {{ $unit->state }}</span>
                </div>
                <div class="col-md-4">
                    <span class="text-muted d-block small">País</span>
                    <span class="text-white">{{ $unit->country }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card card-premium p-4 shadow-lg border-0 text-center mb-4">
            <div class="bg-dark rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 80px; height: 80px;">
                <i class="bi bi-geo-fill text-primary fs-1"></i>
            </div>
            <h5 class="text-white m-0">{{ $unit->name }}</h5>
            <span class="text-muted small d-block mb-3">UUID: {{ $unit->uuid }}</span>
            <div>
                @if(($unit->status->value ?? $unit->status) == 'active')
                    <span class="badge-premium-active">Ativa</span>
                @else
                    <span class="badge-premium-inactive">Inativa</span>
                @endif
            </div>
        </div>

        <div class="card card-premium p-4 shadow-lg border-0">
            <h6 class="text-white mb-2">Histórico da Filial</h6>
            <div class="small">
                <span class="text-muted d-block">Cadastrado em:</span>
                <span class="text-white">{{ $unit->created_at ? $unit->created_at->format('d/m/Y H:i') : '-' }}</span>
                <span class="text-muted d-block mt-2">Última atualização:</span>
                <span class="text-white">{{ $unit->updated_at ? $unit->updated_at->format('d/m/Y H:i') : '-' }}</span>
            </div>
        </div>
    </div>
</div>
@endsection
