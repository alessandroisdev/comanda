@extends('layouts.admin')

@section('title', 'Detalhes do Usuário')
@section('page_title', 'Usuário: ' . $user->name)

@section('content')
<div class="mb-4 d-flex justify-content-between align-items-center">
    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary btn-sm rounded-3">
        <i class="bi bi-arrow-left me-1"></i> Voltar para a Listagem
    </a>
    <a href="{{ route('admin.users.edit', $user->uuid) }}" class="btn btn-premium-primary btn-sm rounded-3">
        <i class="bi bi-pencil-square me-1"></i> Editar Dados
    </a>
</div>

<div class="row g-4">
    <div class="col-md-8">
        <div class="card card-premium p-4 shadow-lg border-0 mb-4">
            <h5 class="text-white border-bottom border-secondary pb-2 mb-3">
                <i class="bi bi-person-fill me-2 text-primary"></i>Informações do Usuário Administrativo
            </h5>
            <div class="row g-3">
                <div class="col-md-6">
                    <span class="text-muted d-block small">Nome Completo</span>
                    <strong class="text-white fs-5">{{ $user->name }}</strong>
                </div>
                <div class="col-md-6">
                    <span class="text-muted d-block small">E-mail</span>
                    <strong class="text-white fs-5">{{ $user->email }}</strong>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card card-premium p-4 shadow-lg border-0 text-center mb-4">
            <div class="bg-dark rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 80px; height: 80px;">
                <i class="bi bi-person-badge text-primary fs-1"></i>
            </div>
            <h5 class="text-white m-0">{{ $user->name }}</h5>
            <span class="text-muted small d-block mb-3">UUID: {{ $user->uuid }}</span>
            <div>
                @if(($user->status->value ?? $user->status) == 'active')
                    <span class="badge-premium-active">Ativo</span>
                @elseif(($user->status->value ?? $user->status) == 'inactive')
                    <span class="badge bg-secondary p-2 rounded-3">Inativo</span>
                @else
                    <span class="badge-premium-inactive">Suspenso</span>
                @endif
            </div>
        </div>

        <div class="card card-premium p-4 shadow-lg border-0">
            <h6 class="text-white mb-2">Histórico do Registro</h6>
            <div class="small">
                <span class="text-muted d-block">Cadastrado em:</span>
                <span class="text-white">{{ $user->created_at ? $user->created_at->format('d/m/Y H:i') : '-' }}</span>
                <span class="text-muted d-block mt-2">Última atualização:</span>
                <span class="text-white">{{ $user->updated_at ? $user->updated_at->format('d/m/Y H:i') : '-' }}</span>
            </div>
        </div>
    </div>
</div>
@endsection
