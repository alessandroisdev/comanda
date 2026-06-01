@extends('layouts.admin')

@section('title', 'Novo Cliente')
@section('page_title', 'Cadastrar Novo Cliente')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-secondary btn-sm rounded-3">
        <i class="bi bi-arrow-left me-1"></i> Voltar para a Listagem
    </a>
</div>

<div class="card card-premium p-4 shadow-lg border-0">
    <form action="{{ route('admin.customers.store') }}" method="POST">
        @csrf

        <div class="row g-4">
            <h5 class="text-white border-bottom border-secondary pb-2 mb-3">
                <i class="bi bi-emoji-smile me-2 text-primary"></i>Informações do Cliente
            </h5>

            <div class="col-md-6">
                <label for="company_id" class="form-label">Empresa / Tenant <span class="text-danger">*</span></label>
                <select class="form-select @error('company_id') is-invalid @enderror" id="company_id" name="company_id" required>
                    <option value="" disabled selected>Selecione a empresa proprietária...</option>
                    @foreach($companies as $company)
                        <option value="{{ $company->id }}" {{ old('company_id') == $company->id ? 'selected' : '' }}>{{ $company->trade_name }}</option>
                    @endforeach
                </select>
                @error('company_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="name" class="form-label">Nome Completo <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required placeholder="Ex: Alessandro Silva">
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="email" class="form-label">E-mail <span class="text-danger">*</span></label>
                <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required placeholder="Ex: alessandro@cliente.com">
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="password" class="form-label">Senha de Acesso (Opcional - Para App/Fidelidade)</label>
                <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" placeholder="Nova senha se o cliente utilizar canais digitais">
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4">
                <label for="phone" class="form-label">Telefone (Opcional)</label>
                <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone') }}" placeholder="Apenas números">
                @error('phone')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4">
                <label for="document" class="form-label">CPF (Opcional)</label>
                <input type="text" class="form-control @error('document') is-invalid @enderror" id="document" name="document" value="{{ old('document') }}" placeholder="Apenas números">
                @error('document')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4">
                <label for="birth_date" class="form-label">Data de Nascimento (Opcional)</label>
                <input type="date" class="form-control @error('birth_date') is-invalid @enderror" id="birth_date" name="birth_date" value="{{ old('birth_date') }}">
                @error('birth_date')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                    <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Ativo</option>
                    <option value="suspended" {{ old('status') == 'suspended' ? 'selected' : '' }}>Suspenso</option>
                </select>
                @error('status')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6 d-flex align-items-center mt-5">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="marketing_opt_in" name="marketing_opt_in" value="1" {{ old('marketing_opt_in') ? 'checked' : '' }}>
                    <label class="form-check-label text-slate-400 font-semibold" for="marketing_opt_in">Aceita receber campanhas de marketing/fidelidade</label>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-3 mt-5">
            <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-light px-4 py-2" style="border-radius: 10px;">
                Cancelar
            </a>
            <button type="submit" class="btn btn-premium-primary px-5 py-2">
                <i class="bi bi-save me-2"></i>Salvar Cliente
            </button>
        </div>
    </form>
</div>
@endsection
