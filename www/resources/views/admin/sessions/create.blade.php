@extends('layouts.admin')

@section('title', 'Abrir Comanda')
@section('page_title', 'Abrir Nova Comanda')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card card-premium p-4">
            <h5 class="text-white mb-4 fw-bold"><i class="bi bi-play-circle me-2 text-primary"></i> Iniciar Atendimento</h5>

            @php
                $employee = Auth::guard('employee')->user();
                $companies = $employee 
                    ? \App\Models\Company::where('id', $employee->company_id)->get() 
                    : \App\Models\Company::all();
                $units = $employee 
                    ? \App\Models\CompanyUnit::where('company_id', $employee->company_id)->get() 
                    : \App\Models\CompanyUnit::all();
            @endphp

            <form action="{{ route('admin.sessions.store') }}" method="POST">
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
                        <label for="table_id" class="form-label text-slate-300">Mesa Associada (Opcional)</label>
                        <select name="table_id" id="table_id" class="form-select bg-slate-900 border-slate-700 text-white">
                            <option value="">Ficha Individual / Consumo Avulso</option>
                            @foreach($tables as $table)
                                <option value="{{ $table->id }}" {{ old('table_id') == $table->id ? 'selected' : '' }}>{{ $table->name }} (Setor: {{ $table->sector }})</option>
                            @endforeach
                        </select>
                        @error('table_id')
                            <div class="text-danger mt-1 small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="people_count" class="form-label text-slate-300">Quantidade de Pessoas <span class="text-danger">*</span></label>
                        <input type="number" name="people_count" id="people_count" class="form-control bg-slate-900 border-slate-700 text-white" value="{{ old('people_count', 1) }}" min="1" required>
                        @error('people_count')
                            <div class="text-danger mt-1 small">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-4">
                    <label for="notes" class="form-label text-slate-300">Observações Operacionais</label>
                    <textarea name="notes" id="notes" class="form-control bg-slate-900 border-slate-700 text-white" rows="3" placeholder="Ex: Cliente prefere copos descartáveis, alergia a glúten na mesa..."></textarea>
                    @error('notes')
                        <div class="text-danger mt-1 small">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex justify-content-end gap-2 border-top border-slate-800 pt-3">
                    <a href="{{ route('admin.sessions.index') }}" class="btn btn-secondary bg-slate-800 border-slate-700 text-white px-4">Cancelar</a>
                    <button type="submit" class="btn btn-premium-primary px-4"><i class="bi bi-play-circle me-2"></i> Iniciar Sessão</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
