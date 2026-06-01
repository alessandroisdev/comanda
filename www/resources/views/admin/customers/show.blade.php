@extends('layouts.admin')

@section('title', 'Detalhes do Cliente')
@section('page_title', 'Cliente: ' . $customer->name)

@section('content')
<div class="mb-4 d-flex justify-content-between align-items-center">
    <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-secondary btn-sm rounded-3">
        <i class="bi bi-arrow-left me-1"></i> Voltar para a Listagem
    </a>
    <a href="{{ route('admin.customers.edit', $customer->uuid) }}" class="btn btn-premium-primary btn-sm rounded-3">
        <i class="bi bi-pencil-square me-1"></i> Editar Dados
    </a>
</div>

<div class="row g-4">
    <div class="col-md-8">
        <div class="card card-premium p-4 shadow-lg border-0 mb-4">
            <h5 class="text-white border-bottom border-secondary pb-2 mb-3">
                <i class="bi bi-emoji-smile me-2 text-primary"></i>Informações Gerais
            </h5>
            <div class="row g-3">
                <div class="col-md-6">
                    <span class="text-muted d-block small">Nome Completo</span>
                    <strong class="text-white fs-5">{{ $customer->name }}</strong>
                </div>
                <div class="col-md-6">
                    <span class="text-muted d-block small">E-mail</span>
                    <strong class="text-white fs-5">{{ $customer->email }}</strong>
                </div>
                <div class="col-md-4">
                    <span class="text-muted d-block small">CPF</span>
                    <span class="text-white">{{ $customer->document ?? '-' }}</span>
                </div>
                <div class="col-md-4">
                    <span class="text-muted d-block small">Telefone</span>
                    <span class="text-white">{{ $customer->phone ?? '-' }}</span>
                </div>
                <div class="col-md-4">
                    <span class="text-muted d-block small">Empresa de Cadastro</span>
                    <span class="text-white"><i class="bi bi-building me-1 text-info"></i> {{ $customer->company->trade_name ?? '-' }}</span>
                </div>
            </div>
        </div>

        <div class="card card-premium p-4 shadow-lg border-0">
            <h5 class="text-white border-bottom border-secondary pb-2 mb-3">
                <i class="bi bi-gear me-2 text-primary"></i>Preferências e Configurações
            </h5>
            <div class="row g-3">
                <div class="col-md-6">
                    <span class="text-muted d-block small">Marketing Opt-In (Campanhas e Promoções)</span>
                    <span class="text-white fs-6">
                        @if($customer->marketing_opt_in)
                            <span class="text-success"><i class="bi bi-check-circle-fill me-1"></i> Autorizado</span>
                        @else
                            <span class="text-muted"><i class="bi bi-x-circle me-1"></i> Não Autorizado</span>
                        @endif
                    </span>
                </div>
                <div class="col-md-6">
                    <span class="text-muted d-block small">Data de Nascimento</span>
                    <span class="text-white"><i class="bi bi-gift me-1 text-danger"></i> {{ $customer->birth_date ? $customer->birth_date->format('d/m/Y') : '-' }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card card-premium p-4 shadow-lg border-0 text-center mb-4">
            <div class="bg-dark rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 80px; height: 80px;">
                <i class="bi bi-person-fill text-primary fs-1"></i>
            </div>
            <h5 class="text-white m-0">{{ $customer->name }}</h5>
            <span class="text-muted small d-block mb-3">UUID: {{ $customer->uuid }}</span>
            <div>
                @if(($customer->status->value ?? $customer->status) == 'active')
                    <span class="badge-premium-active">Ativo</span>
                @else
                    <span class="badge-premium-inactive">Suspenso</span>
                @endif
            </div>
        </div>

        <div class="card card-premium p-4 shadow-lg border-0">
            <h6 class="text-white mb-2">Histórico do Registro</h6>
            <div class="small">
                <span class="text-muted d-block">Cadastrado em:</span>
                <span class="text-white">{{ $customer->created_at ? $customer->created_at->format('d/m/Y H:i') : '-' }}</span>
                <span class="text-muted d-block mt-2">Última atualização:</span>
                <span class="text-white">{{ $customer->updated_at ? $customer->updated_at->format('d/m/Y H:i') : '-' }}</span>
            </div>
        </div>
    </div>
</div>
@endsection
