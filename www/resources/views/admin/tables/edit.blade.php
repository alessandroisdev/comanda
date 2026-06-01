@extends('layouts.admin')

@section('title', 'Editar Mesa')
@section('page_title', 'Editar Mesa')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card card-premium p-4">
            <h5 class="text-white mb-4 fw-bold"><i class="bi bi-pencil me-2 text-primary"></i> Alterar Cadastro da Mesa</h5>

            <form action="{{ route('admin.tables.update', $table->uuid) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label text-slate-400">Empresa Proprietária</label>
                        <input type="text" class="form-control bg-slate-950 border-slate-800 text-slate-500" value="{{ $table->company->trade_name }}" disabled>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label text-slate-400">Unidade Física</label>
                        <input type="text" class="form-control bg-slate-950 border-slate-800 text-slate-500" value="{{ $table->unit->name }}" disabled>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="code" class="form-label text-slate-300">Código de Identificação <span class="text-danger">*</span></label>
                        <input type="text" name="code" id="code" class="form-control bg-slate-900 border-slate-700 text-white" value="{{ old('code', $table->code) }}" required>
                        @error('code')
                            <div class="text-danger mt-1 small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="name" class="form-label text-slate-300">Nome / Número <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" class="form-control bg-slate-900 border-slate-700 text-white" value="{{ old('name', $table->name) }}" required>
                        @error('name')
                            <div class="text-danger mt-1 small">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="capacity" class="form-label text-slate-300">Capacidade de Pessoas <span class="text-danger">*</span></label>
                        <input type="number" name="capacity" id="capacity" class="form-control bg-slate-900 border-slate-700 text-white" value="{{ old('capacity', $table->capacity) }}" min="1" required>
                        @error('capacity')
                            <div class="text-danger mt-1 small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="sector" class="form-label text-slate-300">Setor / Salão <span class="text-danger">*</span></label>
                        <input type="text" name="sector" id="sector" class="form-control bg-slate-900 border-slate-700 text-white" value="{{ old('sector', $table->sector) }}" required>
                        @error('sector')
                            <div class="text-danger mt-1 small">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label text-slate-400">Status Atual</label>
                        <input type="text" class="form-control bg-slate-950 border-slate-800 text-slate-500" value="{{ $table->status->label() }}" disabled>
                    </div>

                    <div class="col-md-6">
                        <label for="sort_order" class="form-label text-slate-300">Ordem de Exibição</label>
                        <input type="number" name="sort_order" id="sort_order" class="form-control bg-slate-900 border-slate-700 text-white" value="{{ old('sort_order', $table->sort_order) }}">
                        @error('sort_order')
                            <div class="text-danger mt-1 small">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 border-top border-slate-800 pt-3">
                    <a href="{{ route('admin.tables.index') }}" class="btn btn-secondary bg-slate-800 border-slate-700 text-white px-4">Cancelar</a>
                    <button type="submit" class="btn btn-premium-primary px-4"><i class="bi bi-save me-2"></i> Salvar Alterações</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
