@extends('layouts.admin')

@section('title', 'Editar Produto')
@section('page_title', 'Editar Produto: ' . $product->name)

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary btn-sm rounded-3">
        <i class="bi bi-arrow-left me-1"></i> Voltar para a Listagem
    </a>
</div>

<div class="card card-premium p-4 shadow-lg border-0">
    <form action="{{ route('admin.products.update', $product->uuid) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row g-4">
            <h5 class="text-white border-bottom border-secondary pb-2 mb-3">
                <i class="bi bi-box-seam me-2 text-primary"></i>Informações do Produto
            </h5>

            <div class="col-md-4">
                <label for="company_id" class="form-label">Empresa / Tenant <span class="text-danger">*</span></label>
                <select class="form-select @error('company_id') is-invalid @enderror" id="company_id" name="company_id" required>
                    <option value="" disabled>Selecione a empresa...</option>
                    @foreach($companies as $company)
                        <option value="{{ $company->id }}" {{ old('company_id', $product->company_id) == $company->id ? 'selected' : '' }}>{{ $company->trade_name }}</option>
                    @endforeach
                </select>
                @error('company_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4">
                <label for="category_id" class="form-label">Categoria do Cardápio <span class="text-danger">*</span></label>
                <select class="form-select @error('category_id') is-invalid @enderror" id="category_id" name="category_id" required>
                    <option value="" disabled>Selecione a categoria...</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" data-company="{{ $category->company_id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                    @endforeach
                </select>
                @error('category_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4">
                <label for="name" class="form-label">Nome do Produto <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $product->name) }}" required placeholder="Ex: Hambúrguer Artesanal, Refrigerante 350ml">
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-3">
                <label for="sku" class="form-label">SKU / Código Único (Opcional)</label>
                <input type="text" class="form-control @error('sku') is-invalid @enderror" id="sku" name="sku" value="{{ old('sku', $product->sku) }}" placeholder="Ex: HAMB-001">
                @error('sku')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-3">
                <label for="barcode" class="form-label">Código de Barras (EAN - Opcional)</label>
                <input type="text" class="form-control @error('barcode') is-invalid @enderror" id="barcode" name="barcode" value="{{ old('barcode', $product->barcode) }}" placeholder="Ex: 7891234567890">
                @error('barcode')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-3">
                <label for="price" class="form-label">Preço de Venda (R$) <span class="text-danger">*</span></label>
                <input type="number" step="0.01" class="form-control @error('price') is-invalid @enderror" id="price" name="price" value="{{ old('price', number_format($product->price_cents / 100, 2, '.', '')) }}" required placeholder="Ex: 29.90">
                @error('price')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-3">
                <label for="cost" class="form-label">Custo do Produto (R$ - Opcional)</label>
                <input type="number" step="0.01" class="form-control @error('cost') is-invalid @enderror" id="cost" name="cost" value="{{ old('cost', $product->cost_cents ? number_format($product->cost_cents / 100, 2, '.', '') : '') }}" placeholder="Ex: 12.50">
                @error('cost')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <h5 class="text-white border-bottom border-secondary pb-2 mt-5 mb-3">
                <i class="bi bi-gear me-2 text-primary"></i>Especificações e Produção
            </h5>

            <div class="col-md-4">
                <label for="preparation_time" class="form-label">Tempo de Preparo Estimado (Minutos)</label>
                <input type="number" class="form-control @error('preparation_time') is-invalid @enderror" id="preparation_time" name="preparation_time" value="{{ old('preparation_time', $product->preparation_time) }}" min="0" placeholder="Ex: 15">
                @error('preparation_time')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4">
                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                    <option value="active" {{ old('status', $product->status->value ?? $product->status) == 'active' ? 'selected' : '' }}>Ativo</option>
                    <option value="inactive" {{ old('status', $product->status->value ?? $product->status) == 'inactive' ? 'selected' : '' }}>Inativo</option>
                </select>
                @error('status')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4">
                <label for="image" class="form-label">URL da Imagem do Produto (Opcional)</label>
                <input type="text" class="form-control @error('image') is-invalid @enderror" id="image" name="image" value="{{ old('image', $product->image) }}" placeholder="https://exemplo.com/hamburguer.jpg">
                @error('image')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12">
                <label for="description" class="form-label">Descrição / Ingredientes (Opcional)</label>
                <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3" placeholder="Ingredientes e detalhes adicionais do produto...">{{ old('description', $product->description) }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="d-flex justify-content-end gap-3 mt-5">
            <a href="{{ route('admin.products.index') }}" class="btn btn-outline-light px-4 py-2" style="border-radius: 10px;">
                Cancelar
            </a>
            <button type="submit" class="btn btn-premium-primary px-5 py-2">
                <i class="bi bi-save me-2"></i>Salvar Alterações
            </button>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const companySelect = document.getElementById('company_id');
        const categorySelect = document.getElementById('category_id');
        const originalCategories = Array.from(categorySelect.options);

        function filterCategories() {
            const selectedCompany = companySelect.value;
            const currentCategoryId = "{{ $product->category_id }}";
            categorySelect.innerHTML = '';
            
            // Adiciona a primeira opção (vazia)
            categorySelect.appendChild(originalCategories[0]);
            
            originalCategories.forEach(option => {
                if (option.value && option.getAttribute('data-company') === selectedCompany) {
                    const clonedOption = option.cloneNode(true);
                    if (clonedOption.value === currentCategoryId) {
                        clonedOption.selected = true;
                    }
                    categorySelect.appendChild(clonedOption);
                }
            });
        }

        companySelect.addEventListener('change', filterCategories);
        if (companySelect.value) {
            filterCategories();
        }
    });
</script>
@endsection
