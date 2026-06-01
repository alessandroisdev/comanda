@extends('layouts.admin')

@section('title', 'Detalhes da Empresa')
@section('page_title', 'Empresa: ' . $company->trade_name)

@section('content')
<div class="mb-4 d-flex justify-content-between align-items-center">
    <a href="{{ route('admin.companies.index') }}" class="btn btn-outline-secondary btn-sm rounded-3">
        <i class="bi bi-arrow-left me-1"></i> Voltar para a Listagem
    </a>
    <a href="{{ route('admin.companies.edit', $company->uuid) }}" class="btn btn-premium-primary btn-sm rounded-3">
        <i class="bi bi-pencil-square me-1"></i> Editar Dados
    </a>
</div>

<div class="row g-4">
    <div class="col-md-8">
        <div class="card card-premium p-4 shadow-lg border-0 mb-4">
            <h5 class="text-white border-bottom border-secondary pb-2 mb-3">
                <i class="bi bi-building me-2 text-primary"></i>Informações Gerais
            </h5>
            <div class="row g-3">
                <div class="col-md-6">
                    <span class="text-muted d-block small">Razão Social</span>
                    <strong class="text-white fs-5">{{ $company->legal_name }}</strong>
                </div>
                <div class="col-md-6">
                    <span class="text-muted d-block small">Nome Fantasia</span>
                    <strong class="text-white fs-5">{{ $company->trade_name }}</strong>
                </div>
                <div class="col-md-4">
                    <span class="text-muted d-block small">CNPJ / CPF</span>
                    <span class="text-white">{{ $company->document_number }} ({{ $company->document_type->value ?? $company->document_type }})</span>
                </div>
                <div class="col-md-4">
                    <span class="text-muted d-block small">E-mail</span>
                    <span class="text-white">{{ $company->email }}</span>
                </div>
                <div class="col-md-4">
                    <span class="text-muted d-block small">Telefone</span>
                    <span class="text-white">{{ $company->phone }}</span>
                </div>
            </div>
        </div>

        <div class="card card-premium p-4 shadow-lg border-0">
            <h5 class="text-white border-bottom border-secondary pb-2 mb-3">
                <i class="bi bi-gear me-2 text-primary"></i>Preferências de Localização e Configuração
            </h5>
            <div class="row g-3">
                <div class="col-md-4">
                    <span class="text-muted d-block small">Fuso Horário</span>
                    <span class="text-white"><i class="bi bi-globe me-1 text-info"></i> {{ $company->timezone }}</span>
                </div>
                <div class="col-md-4">
                    <span class="text-muted d-block small">Moeda</span>
                    <span class="text-white"><i class="bi bi-cash-coin me-1 text-success"></i> {{ $company->currency }}</span>
                </div>
                <div class="col-md-4">
                    <span class="text-muted d-block small">Idioma</span>
                    <span class="text-white"><i class="bi bi-translate me-1 text-warning"></i> {{ $company->language }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card card-premium p-4 shadow-lg border-0 text-center mb-4">
            <div class="mb-3">
                @if($company->logo)
                    <img src="{{ $company->logo }}" alt="Logo {{ $company->trade_name }}" class="img-fluid rounded-3 mb-2" style="max-height: 120px;">
                @else
                    <div class="bg-dark rounded-3 d-flex align-items-center justify-content-center mx-auto" style="width: 120px; height: 120px;">
                        <i class="bi bi-image text-muted fs-1"></i>
                    </div>
                @endif
            </div>
            <h5 class="text-white m-0">{{ $company->trade_name }}</h5>
            <span class="text-muted small d-block mb-3">UUID: {{ $company->uuid }}</span>
            <div>
                @if(($company->status->value ?? $company->status) == 'active')
                    <span class="badge-premium-active">Ativa</span>
                @else
                    <span class="badge-premium-inactive">Suspensa</span>
                @endif
            </div>
        </div>

        <div class="card card-premium p-4 shadow-lg border-0">
            <h6 class="text-white mb-2">Histórico do Tenant</h6>
            <div class="small">
                <span class="text-muted d-block">Cadastrado em:</span>
                <span class="text-white">{{ $company->created_at ? $company->created_at->format('d/m/Y H:i') : '-' }}</span>
                <span class="text-muted d-block mt-2">Última atualização:</span>
                <span class="text-white">{{ $company->updated_at ? $company->updated_at->format('d/m/Y H:i') : '-' }}</span>
            </div>
        </div>
    </div>
</div>
@endsection
