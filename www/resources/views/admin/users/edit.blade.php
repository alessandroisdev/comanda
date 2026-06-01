@extends('layouts.admin')

@section('title', 'Editar Usuário')
@section('page_title', 'Editar Usuário: ' . $user->name)

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary btn-sm rounded-3">
        <i class="bi bi-arrow-left me-1"></i> Voltar para a Listagem
    </a>
</div>

<div class="card card-premium p-4 shadow-lg border-0">
    <form action="{{ route('admin.users.update', $user->uuid) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row g-4">
            <h5 class="text-white border-bottom border-secondary pb-2 mb-3">
                <i class="bi bi-person-fill me-2 text-primary"></i>Dados do Usuário
            </h5>

            <div class="col-md-6">
                <label for="name" class="form-label">Nome Completo <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $user->name) }}" required placeholder="Ex: Alessandro Silva">
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="email" class="form-label">E-mail <span class="text-danger">*</span></label>
                <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $user->email) }}" required placeholder="Ex: alessandro@comanda.com">
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="password" class="form-label">Senha de Acesso (Deixe em branco para manter a atual)</label>
                <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" placeholder="Nova senha (mínimo de 8 caracteres)">
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="status" class="form-label">Status</label>
                <select class="form-select @error('status') is-invalid @enderror" id="status" name="status">
                    <option value="active" {{ old('status', $user->status->value ?? $user->status) == 'active' ? 'selected' : '' }}>Ativo</option>
                    <option value="inactive" {{ old('status', $user->status->value ?? $user->status) == 'inactive' ? 'selected' : '' }}>Inativo</option>
                    <option value="suspended" {{ old('status', $user->status->value ?? $user->status) == 'suspended' ? 'selected' : '' }}>Suspenso</option>
                </select>
                @error('status')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="d-flex justify-content-end gap-3 mt-5">
            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-light px-4 py-2" style="border-radius: 10px;">
                Cancelar
            </a>
            <button type="submit" class="btn btn-premium-primary px-5 py-2">
                <i class="bi bi-save me-2"></i>Salvar Alterações
            </button>
        </div>
    </form>
</div>
@endsection
