@extends('layouts.admin')

@section('title', 'Nova Mesa')
@section('page_title', 'Nova Mesa')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card card-premium p-4">
            <h5 class="text-white mb-4 fw-bold"><i class="bi bi-plus-circle me-2 text-primary"></i> Cadastrar Nova Mesa</h5>

            @php
                $employee = Auth::guard('employee')->user();
                $companies = $employee 
                    ? \App\Models\Company::where('id', $employee->company_id)->get() 
                    : \App\Models\Company::all();
                $units = $employee 
                    ? \App\Models\CompanyUnit::where('company_id', $employee->company_id)->get() 
                    : \App\Models\CompanyUnit::all();
            @endphp

            <form action="{{ route('admin.tables.store') }}" method="POST">
                @csrf

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="company_id" class="form-label text-slate-300">Empresa Proprietária <span class="text-danger">*</span></label>
                        <select name="company_id" id="company_id" class="form-select bg-slate-900 border-slate-700 text-white" required>
                            <option value="">Selecione...</option>
                            @foreach($companies as $company)
                                <option value="{{ $company->id }}" {{ old('company_id') == $company->id ? 'selected' : '' }}>{{ $company->trade_name }}</option>
                            @endforeach
                        </select>
                        @error('company_id')
                            <div class="text-danger mt-1 small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="unit_id" class="form-label text-slate-300">Unidade Física <span class="text-danger">*</span></label>
                        <select name="unit_id" id="unit_id" class="form-select bg-slate-900 border-slate-700 text-white" required>
                            <option value="">Selecione...</option>
                            @foreach($units as $unit)
                                <option value="{{ $unit->id }}" {{ old('unit_id') == $unit->id ? 'selected' : '' }}>{{ $unit->name }}</option>
                            @endforeach
                        </select>
                        @error('unit_id')
                            <div class="text-danger mt-1 small">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="code" class="form-label text-slate-300">Código de Identificação <span class="text-danger">*</span></label>
                        <input type="text" name="code" id="code" class="form-control bg-slate-900 border-slate-700 text-white" placeholder="Ex: M-01, VIP-02" value="{{ old('code') }}" required>
                        @error('code')
                            <div class="text-danger mt-1 small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="name" class="form-label text-slate-300">Nome / Número <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" class="form-control bg-slate-900 border-slate-700 text-white" placeholder="Ex: Mesa 01, VIP Superior" value="{{ old('name') }}" required>
                        @error('name')
                            <div class="text-danger mt-1 small">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="capacity" class="form-label text-slate-300">Capacidade de Pessoas <span class="text-danger">*</span></label>
                        <input type="number" name="capacity" id="capacity" class="form-control bg-slate-900 border-slate-700 text-white" placeholder="Ex: 4, 8" value="{{ old('capacity', 4) }}" min="1" required>
                        @error('capacity')
                            <div class="text-danger mt-1 small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="sector" class="form-label text-slate-300">Setor / Salão <span class="text-danger">*</span></label>
                        <input type="text" name="sector" id="sector" class="form-control bg-slate-900 border-slate-700 text-white" placeholder="Ex: Salão Principal, Terraço" value="{{ old('sector', 'Salão Principal') }}" required>
                        @error('sector')
                            <div class="text-danger mt-1 small">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <label for="status" class="form-label text-slate-300">Status Inicial <span class="text-danger">*</span></label>
                        <select name="status" id="status" class="form-select bg-slate-900 border-slate-700 text-white" required>
                            <option value="available" {{ old('status') == 'available' ? 'selected' : '' }}>Disponível</option>
                            <option value="occupied" {{ old('status') == 'occupied' ? 'selected' : '' }}>Ocupada</option>
                            <option value="reserved" {{ old('status') == 'reserved' ? 'selected' : '' }}>Reservada</option>
                            <option value="blocked" {{ old('status') == 'blocked' ? 'selected' : '' }}>Bloqueada</option>
                            <option value="cleaning" {{ old('status') == 'cleaning' ? 'selected' : '' }}>Limpeza</option>
                        </select>
                        @error('status')
                            <div class="text-danger mt-1 small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="sort_order" class="form-label text-slate-300">Ordem de Exibição</label>
                        <input type="number" name="sort_order" id="sort_order" class="form-control bg-slate-900 border-slate-700 text-white" placeholder="Ex: 10, 20" value="{{ old('sort_order', 10) }}">
                        @error('sort_order')
                            <div class="text-danger mt-1 small">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 border-top border-slate-800 pt-3">
                    <a href="{{ route('admin.tables.index') }}" class="btn btn-secondary bg-slate-800 border-slate-700 text-white px-4">Cancelar</a>
                    <button type="submit" class="btn btn-premium-primary px-4"><i class="bi bi-save me-2"></i> Salvar Mesa</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
