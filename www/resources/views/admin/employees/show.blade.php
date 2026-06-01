@extends('layouts.admin')

@section('title', 'Detalhes do Funcionário')
@section('page_title', 'Funcionário: ' . $employee->name)

@section('content')
<div class="mb-4 d-flex justify-content-between align-items-center">
    <a href="{{ route('admin.employees.index') }}" class="btn btn-outline-secondary btn-sm rounded-3">
        <i class="bi bi-arrow-left me-1"></i> Voltar para a Listagem
    </a>
    <a href="{{ route('admin.employees.edit', $employee->uuid) }}" class="btn btn-premium-primary btn-sm rounded-3">
        <i class="bi bi-pencil-square me-1"></i> Editar Dados
    </a>
</div>

<div class="row g-4">
    <div class="col-md-8">
        <div class="card card-premium p-4 shadow-lg border-0 mb-4">
            <h5 class="text-white border-bottom border-secondary pb-2 mb-3">
                <i class="bi bi-person-fill me-2 text-primary"></i>Informações do Funcionário
            </h5>
            <div class="row g-3">
                <div class="col-md-6">
                    <span class="text-muted d-block small">Nome Completo</span>
                    <strong class="text-white fs-5">{{ $employee->name }}</strong>
                </div>
                <div class="col-md-6">
                    <span class="text-muted d-block small">E-mail</span>
                    <strong class="text-white fs-5">{{ $employee->email }}</strong>
                </div>
                <div class="col-md-4">
                    <span class="text-muted d-block small">CPF</span>
                    <span class="text-white">{{ $employee->document ?? '-' }}</span>
                </div>
                <div class="col-md-4">
                    <span class="text-muted d-block small">Telefone</span>
                    <span class="text-white">{{ $employee->phone ?? '-' }}</span>
                </div>
                <div class="col-md-4">
                    <span class="text-muted d-block small">Matrícula</span>
                    <span class="text-white">{{ $employee->employee_number }}</span>
                </div>

                <div class="col-12 mt-4">
                    <h6 class="text-white border-bottom border-secondary pb-1 mb-2">Estrutura Organizacional</h6>
                </div>
                <div class="col-md-6">
                    <span class="text-muted d-block small">Empresa Proprietária</span>
                    <span class="text-white fs-6"><i class="bi bi-building me-1 text-info"></i> {{ $employee->company->trade_name ?? '-' }}</span>
                </div>
                <div class="col-md-6">
                    <span class="text-muted d-block small">Unidade Física</span>
                    <span class="text-white fs-6"><i class="bi bi-geo-alt me-1 text-warning"></i> {{ $employee->unit->name ?? 'Global/Toda Empresa' }}</span>
                </div>
            </div>
        </div>

        <div class="card card-premium p-4 shadow-lg border-0">
            <h5 class="text-white border-bottom border-secondary pb-2 mb-3">
                <i class="bi bi-calendar me-2 text-primary"></i>Datas Importantes
            </h5>
            <div class="row g-3">
                <div class="col-md-6">
                    <span class="text-muted d-block small">Data de Contratação</span>
                    <span class="text-white"><i class="bi bi-calendar-check me-1 text-success"></i> {{ $employee->hire_date ? $employee->hire_date->format('d/m/Y') : '-' }}</span>
                </div>
                <div class="col-md-6">
                    <span class="text-muted d-block small">Data de Nascimento</span>
                    <span class="text-white"><i class="bi bi-gift me-1 text-danger"></i> {{ $employee->birth_date ? $employee->birth_date->format('d/m/Y') : '-' }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card card-premium p-4 shadow-lg border-0 text-center mb-4">
            <div class="bg-dark rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 80px; height: 80px;">
                <i class="bi bi-person-badge-fill text-primary fs-1"></i>
            </div>
            <h5 class="text-white m-0">{{ $employee->name }}</h5>
            <span class="text-muted small d-block mb-3">Matrícula: {{ $employee->employee_number }}</span>
            <div class="mb-3">
                <span class="badge bg-primary p-2 rounded-3 text-uppercase" style="font-size: 0.8rem;">
                    {{ $employee->role->label() ?? $employee->role }}
                </span>
            </div>
            <div>
                @if(($employee->status->value ?? $employee->status) == 'active')
                    <span class="badge-premium-active">Ativo</span>
                @else
                    <span class="badge-premium-inactive">Suspenso</span>
                @endif
            </div>
        </div>

        <div class="card card-premium p-4 shadow-lg border-0">
            <h6 class="text-white mb-2">Histórico do Funcionário</h6>
            <div class="small">
                <span class="text-muted d-block">Cadastrado em:</span>
                <span class="text-white">{{ $employee->created_at ? $employee->created_at->format('d/m/Y H:i') : '-' }}</span>
                <span class="text-muted d-block mt-2">Última atualização:</span>
                <span class="text-white">{{ $employee->updated_at ? $employee->updated_at->format('d/m/Y H:i') : '-' }}</span>
            </div>
        </div>
    </div>
</div>
@endsection
