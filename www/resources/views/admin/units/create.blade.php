@extends('layouts.admin')

@section('title', 'Nova Unidade Física')
@section('page_title', 'Cadastrar Nova Unidade')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.units.index') }}" class="btn btn-outline-secondary btn-sm rounded-3">
        <i class="bi bi-arrow-left me-1"></i> Voltar para a Listagem
    </a>
</div>

<div class="card card-premium p-4 shadow-lg border-0">
    <form action="{{ route('admin.units.store') }}" method="POST">
        @csrf

        <div class="row g-4">
            <h5 class="text-white border-bottom border-secondary pb-2 mb-3">
                <i class="bi bi-info-circle me-2 text-primary"></i>Informações da Unidade
            </h5>

            <div class="col-md-6">
                <label for="company_id" class="form-label">Empresa / Tenant <span class="text-danger">*</span></label>
                <select class="form-select @error('company_id') is-invalid @enderror" id="company_id" name="company_id" required>
                    <option value="" disabled selected>Selecione a empresa proprietária...</option>
                    @foreach($companies as $company)
                        <option value="{{ $company->id }}" {{ old('company_id') == $company->id ? 'selected' : '' }}>{{ $company->trade_name }} ({{ $company->document_number }})</option>
                    @endforeach
                </select>
                @error('company_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="name" class="form-label">Nome da Unidade / Filial <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required placeholder="Ex: Filial Centro ou Shopping">
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4">
                <label for="document_number" class="form-label">CNPJ da Filial (Opcional)</label>
                <input type="text" class="form-control @error('document_number') is-invalid @enderror" id="document_number" name="document_number" value="{{ old('document_number') }}" placeholder="Apenas números">
                @error('document_number')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4">
                <label for="email" class="form-label">E-mail da Filial (Opcional)</label>
                <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" placeholder="centro@empresa.com">
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4">
                <label for="phone" class="form-label">Telefone da Filial (Opcional)</label>
                <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone') }}" placeholder="(11) 99999-9999">
                @error('phone')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <h5 class="text-white border-bottom border-secondary pb-2 mt-5 mb-3">
                <i class="bi bi-geo-alt me-2 text-primary"></i>Endereço da Unidade
            </h5>

            <div class="col-md-3">
                <label for="zipcode" class="form-label">CEP <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('zipcode') is-invalid @enderror" id="zipcode" name="zipcode" value="{{ old('zipcode') }}" required placeholder="00000-000">
                @error('zipcode')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="street" class="form-label">Logradouro / Rua <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('street') is-invalid @enderror" id="street" name="street" value="{{ old('street') }}" required placeholder="Av. Paulista">
                @error('street')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-3">
                <label for="number" class="form-label">Número <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('number') is-invalid @enderror" id="number" name="number" value="{{ old('number') }}" required placeholder="1000">
                @error('number')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4">
                <label for="district" class="form-label">Bairro <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('district') is-invalid @enderror" id="district" name="district" value="{{ old('district') }}" required placeholder="Bela Vista">
                @error('district')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4">
                <label for="city" class="form-label">Cidade <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('city') is-invalid @enderror" id="city" name="city" value="{{ old('city') }}" required placeholder="São Paulo">
                @error('city')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-2">
                <label for="state" class="form-label">Estado (UF) <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('state') is-invalid @enderror" id="state" name="state" value="{{ old('state') }}" required placeholder="SP" maxlength="2">
                @error('state')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-2">
                <label for="status" class="form-label">Status da Filial</label>
                <select class="form-select @error('status') is-invalid @enderror" id="status" name="status">
                    <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Ativa</option>
                    <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inativa</option>
                </select>
                @error('status')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="d-flex justify-content-end gap-3 mt-5">
            <a href="{{ route('admin.units.index') }}" class="btn btn-outline-light px-4 py-2" style="border-radius: 10px;">
                Cancelar
            </a>
            <button type="submit" class="btn btn-premium-primary px-5 py-2">
                <i class="bi bi-save me-2"></i>Salvar Unidade
            </button>
        </div>
    </form>
</div>
@endsection
