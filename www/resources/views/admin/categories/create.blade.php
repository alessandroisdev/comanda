@extends('layouts.admin')

@section('title', 'Nova Categoria')
@section('page_title', 'Cadastrar Nova Categoria')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary btn-sm rounded-3">
        <i class="bi bi-arrow-left me-1"></i> Voltar para a Listagem
    </a>
</div>

<div class="card card-premium p-4 shadow-lg border-0">
    <form action="{{ route('admin.categories.store') }}" method="POST">
        @csrf

        <div class="row g-4">
            <h5 class="text-white border-bottom border-secondary pb-2 mb-3">
                <i class="bi bi-tag-fill me-2 text-primary"></i>Dados da Categoria
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
                <label for="name" class="form-label">Nome da Categoria <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required placeholder="Ex: Bebidas, Massas, Sobremesas">
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="sort_order" class="form-label">Ordem de Exibição</label>
                <input type="number" class="form-control @error('sort_order') is-invalid @enderror" id="sort_order" name="sort_order" value="{{ old('sort_order', 0) }}" min="0" placeholder="Ex: 0, 1, 2">
                @error('sort_order')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                    <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Ativo</option>
                    <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inativo</option>
                </select>
                @error('status')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12">
                <label for="description" class="form-label">Descrição da Categoria (Opcional)</label>
                <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3" placeholder="Breve descrição dos produtos pertencentes a esta categoria...">{{ old('description') }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="d-flex justify-content-end gap-3 mt-5">
            <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-light px-4 py-2" style="border-radius: 10px;">
                Cancelar
            </a>
            <button type="submit" class="btn btn-premium-primary px-5 py-2">
                <i class="bi bi-save me-2"></i>Salvar Categoria
            </button>
        </div>
    </form>
</div>
@endsection
