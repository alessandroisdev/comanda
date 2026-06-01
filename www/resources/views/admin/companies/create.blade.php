@extends('layouts.admin')

@section('title', 'Nova Empresa')
@section('page_title', 'Cadastrar Nova Empresa')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.companies.index') }}" class="btn btn-outline-secondary btn-sm rounded-3">
        <i class="bi bi-arrow-left me-1"></i> Voltar para a Listagem
    </a>
</div>

<div class="card card-premium p-4 shadow-lg border-0">
    <form action="{{ route('admin.companies.store') }}" method="POST">
        @csrf

        <div class="row g-4">
            <h5 class="text-white border-bottom border-secondary pb-2 mb-3">
                <i class="bi bi-info-circle me-2 text-primary"></i>Informações Básicas
            </h5>
            
            <div class="col-md-6">
                <label for="legal_name" class="form-label">Razão Social <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('legal_name') is-invalid @enderror" id="legal_name" name="legal_name" value="{{ old('legal_name') }}" required placeholder="Ex: Comanda Alimentos LTDA">
                @error('legal_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="trade_name" class="form-label">Nome Fantasia <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('trade_name') is-invalid @enderror" id="trade_name" name="trade_name" value="{{ old('trade_name') }}" required placeholder="Ex: Comanda Gourmet">
                @error('trade_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-3">
                <label for="document_type" class="form-label">Tipo de Documento <span class="text-danger">*</span></label>
                <select class="form-select @error('document_type') is-invalid @enderror" id="document_type" name="document_type" required>
                    <option value="CNPJ" {{ old('document_type') == 'CNPJ' ? 'selected' : '' }}>CNPJ</option>
                    <option value="CPF" {{ old('document_type') == 'CPF' ? 'selected' : '' }}>CPF</option>
                </select>
                @error('document_type')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-3">
                <label for="document_number" class="form-label">Número de Documento <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('document_number') is-invalid @enderror" id="document_number" name="document_number" value="{{ old('document_number') }}" required placeholder="Apenas números">
                @error('document_number')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-3">
                <label for="email" class="form-label">E-mail Corporativo <span class="text-danger">*</span></label>
                <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required placeholder="contato@empresa.com">
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-3">
                <label for="phone" class="form-label">Telefone <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone') }}" required placeholder="(11) 99999-9999">
                @error('phone')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <h5 class="text-white border-bottom border-secondary pb-2 mt-5 mb-3">
                <i class="bi bi-gear me-2 text-primary"></i>Configurações e Localização
            </h5>

            <div class="col-md-4">
                <label for="timezone" class="form-label">Fuso Horário</label>
                <select class="form-select @error('timezone') is-invalid @enderror" id="timezone" name="timezone">
                    <option value="America/Sao_Paulo" {{ old('timezone', 'America/Sao_Paulo') == 'America/Sao_Paulo' ? 'selected' : '' }}>America/Sao_Paulo (GTM-3)</option>
                    <option value="America/Manaus" {{ old('timezone') == 'America/Manaus' ? 'selected' : '' }}>America/Manaus (GTM-4)</option>
                    <option value="UTC" {{ old('timezone') == 'UTC' ? 'selected' : '' }}>UTC</option>
                </select>
                @error('timezone')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4">
                <label for="currency" class="form-label">Moeda Padrão</label>
                <select class="form-select @error('currency') is-invalid @enderror" id="currency" name="currency">
                    <option value="BRL" {{ old('currency', 'BRL') == 'BRL' ? 'selected' : '' }}>BRL (R$)</option>
                    <option value="USD" {{ old('currency') == 'USD' ? 'selected' : '' }}>USD ($)</option>
                    <option value="EUR" {{ old('currency') == 'EUR' ? 'selected' : '' }}>EUR (€)</option>
                </select>
                @error('currency')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4">
                <label for="language" class="form-label">Idioma</label>
                <select class="form-select @error('language') is-invalid @enderror" id="language" name="language">
                    <option value="pt_BR" {{ old('language', 'pt_BR') == 'pt_BR' ? 'selected' : '' }}>Português (pt_BR)</option>
                    <option value="en" {{ old('language') == 'en' ? 'selected' : '' }}>English (en)</option>
                    <option value="es" {{ old('language') == 'es' ? 'selected' : '' }}>Español (es)</option>
                </select>
                @error('language')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="status" class="form-label">Status da Empresa</label>
                <select class="form-select @error('status') is-invalid @enderror" id="status" name="status">
                    <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Ativa</option>
                    <option value="suspended" {{ old('status') == 'suspended' ? 'selected' : '' }}>Suspensa</option>
                </select>
                @error('status')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="logo" class="form-label">URL do Logotipo</label>
                <input type="text" class="form-control @error('logo') is-invalid @enderror" id="logo" name="logo" value="{{ old('logo') }}" placeholder="https://exemplo.com/logo.png">
                @error('logo')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="d-flex justify-content-end gap-3 mt-5">
            <a href="{{ route('admin.companies.index') }}" class="btn btn-outline-light px-4 py-2" style="border-radius: 10px;">
                Cancelar
            </a>
            <button type="submit" class="btn btn-premium-primary px-5 py-2">
                <i class="bi bi-save me-2"></i>Salvar Empresa
            </button>
        </div>
    </form>
</div>
@endsection
