@extends('layouts.admin')

@section('title', 'Editar Funcionário')
@section('page_title', 'Editar Funcionário: ' . $employee->name)

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.employees.index') }}" class="btn btn-outline-secondary btn-sm rounded-3">
        <i class="bi bi-arrow-left me-1"></i> Voltar para a Listagem
    </a>
</div>

<div class="card card-premium p-4 shadow-lg border-0">
    <form action="{{ route('admin.employees.update', $employee->uuid) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row g-4">
            <h5 class="text-white border-bottom border-secondary pb-2 mb-3">
                <i class="bi bi-person-fill me-2 text-primary"></i>Informações do Funcionário
            </h5>

            <div class="col-md-4">
                <label for="company_id" class="form-label">Empresa / Tenant <span class="text-danger">*</span></label>
                <select class="form-select @error('company_id') is-invalid @enderror" id="company_id" name="company_id" required>
                    <option value="" disabled>Selecione a empresa...</option>
                    @foreach($companies as $company)
                        <option value="{{ $company->id }}" {{ old('company_id', $employee->company_id) == $company->id ? 'selected' : '' }}>{{ $company->trade_name }}</option>
                    @endforeach
                </select>
                @error('company_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4">
                <label for="unit_id" class="form-label">Unidade Física / Filial</label>
                <select class="form-select @error('unit_id') is-invalid @enderror" id="unit_id" name="unit_id">
                    <option value="" selected>Sem Unidade Física (Multi-unidade/Global)</option>
                    @foreach($units as $unit)
                        <option value="{{ $unit->id }}" data-company="{{ $unit->company_id }}" {{ old('unit_id', $employee->unit_id) == $unit->id ? 'selected' : '' }}>{{ $unit->name }}</option>
                    @endforeach
                </select>
                @error('unit_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4">
                <label for="employee_number" class="form-label">Matrícula / Registro <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('employee_number') is-invalid @enderror" id="employee_number" name="employee_number" value="{{ old('employee_number', $employee->employee_number) }}" required placeholder="Ex: EMP-1004">
                @error('employee_number')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="name" class="form-label">Nome Completo <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $employee->name) }}" required placeholder="Ex: Roberto Ramos">
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="email" class="form-label">E-mail Corporativo <span class="text-danger">*</span></label>
                <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $employee->email) }}" required placeholder="Ex: roberto@empresa.com">
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="password" class="form-label">Senha de Acesso (Deixe em branco para manter a atual)</label>
                <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" placeholder="Nova senha se desejar alterar">
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-3">
                <label for="phone" class="form-label">Telefone (Opcional)</label>
                <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone', $employee->phone) }}" placeholder="Apenas números">
                @error('phone')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-3">
                <label for="document" class="form-label">CPF (Opcional)</label>
                <input type="text" class="form-control @error('document') is-invalid @enderror" id="document" name="document" value="{{ old('document', $employee->document) }}" placeholder="Apenas números">
                @error('document')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <h5 class="text-white border-bottom border-secondary pb-2 mt-5 mb-3">
                <i class="bi bi-briefcase me-2 text-primary"></i>Contratação e Papel Operacional
            </h5>

            <div class="col-md-4">
                <label for="role" class="form-label">Cargo Operacional <span class="text-danger">*</span></label>
                <select class="form-select @error('role') is-invalid @enderror" id="role" name="role" required>
                    <option value="waiter" {{ old('role', $employee->role->value ?? $employee->role) == 'waiter' ? 'selected' : '' }}>Garçom</option>
                    <option value="cashier" {{ old('role', $employee->role->value ?? $employee->role) == 'cashier' ? 'selected' : '' }}>Caixa</option>
                    <option value="kitchen" {{ old('role', $employee->role->value ?? $employee->role) == 'kitchen' ? 'selected' : '' }}>Cozinha</option>
                    <option value="manager" {{ old('role', $employee->role->value ?? $employee->role) == 'manager' ? 'selected' : '' }}>Gerente</option>
                    <option value="admin" {{ old('role', $employee->role->value ?? $employee->role) == 'admin' ? 'selected' : '' }}>Administrador</option>
                    <option value="driver" {{ old('role', $employee->role->value ?? $employee->role) == 'driver' ? 'selected' : '' }}>Entregador</option>
                </select>
                @error('role')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4">
                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                    <option value="active" {{ old('status', $employee->status->value ?? $employee->status) == 'active' ? 'selected' : '' }}>Ativo</option>
                    <option value="suspended" {{ old('status', $employee->status->value ?? $employee->status) == 'suspended' ? 'selected' : '' }}>Suspenso</option>
                </select>
                @error('status')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4">
                <label for="hire_date" class="form-label">Data de Contratação</label>
                <input type="date" class="form-control @error('hire_date') is-invalid @enderror" id="hire_date" name="hire_date" value="{{ old('hire_date', $employee->hire_date ? $employee->hire_date->format('Y-m-d') : '') }}">
                @error('hire_date')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4">
                <label for="birth_date" class="form-label">Data de Nascimento</label>
                <input type="date" class="form-control @error('birth_date') is-invalid @enderror" id="birth_date" name="birth_date" value="{{ old('birth_date', $employee->birth_date ? $employee->birth_date->format('Y-m-d') : '') }}">
                @error('birth_date')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="d-flex justify-content-end gap-3 mt-5">
            <a href="{{ route('admin.employees.index') }}" class="btn btn-outline-light px-4 py-2" style="border-radius: 10px;">
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
        const unitSelect = document.getElementById('unit_id');
        const originalUnits = Array.from(unitSelect.options);

        function filterUnits() {
            const selectedCompany = companySelect.value;
            const currentUnitId = "{{ $employee->unit_id }}";
            unitSelect.innerHTML = '';
            
            // Adiciona a primeira opção (vazia)
            unitSelect.appendChild(originalUnits[0]);
            
            originalUnits.forEach(option => {
                if (option.value && option.getAttribute('data-company') === selectedCompany) {
                    const clonedOption = option.cloneNode(true);
                    if (clonedOption.value === currentUnitId) {
                        clonedOption.selected = true;
                    }
                    unitSelect.appendChild(clonedOption);
                }
            });
        }

        companySelect.addEventListener('change', filterUnits);
        if (companySelect.value) {
            filterUnits();
        }
    });
</script>
@endsection
