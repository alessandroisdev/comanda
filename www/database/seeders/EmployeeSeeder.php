<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\EmployeeRoleEnum;
use App\Enums\EmployeeStatusEnum;
use App\Models\Company;
use App\Models\Employee;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $companies = Company::all();

        if ($companies->isEmpty()) {
            return;
        }

        foreach ($companies as $company) {
            $units = $company->units;

            if ($units->isEmpty()) {
                continue;
            }

            foreach ($units as $unit) {
                // Cadastrar um gerente fixo para testes
                Employee::create([
                    'uuid' => (string) Str::uuid(),
                    'company_id' => $company->id,
                    'unit_id' => $unit->id,
                    'employee_number' => 'EMP-'.$company->id.$unit->id.'01',
                    'name' => 'Gerente '.$unit->name,
                    'email' => 'gerente.'.Str::slug($unit->name).'@'.Str::slug($company->trade_name).'.com',
                    'password' => Hash::make('password123'),
                    'phone' => '11988888888',
                    'document' => '11122233344',
                    'birth_date' => '1985-05-10',
                    'hire_date' => '2024-01-01',
                    'status' => EmployeeStatusEnum::ACTIVE,
                    'role' => EmployeeRoleEnum::MANAGER,
                ]);

                // Cadastrar um garçom fixo para testes
                Employee::create([
                    'uuid' => (string) Str::uuid(),
                    'company_id' => $company->id,
                    'unit_id' => $unit->id,
                    'employee_number' => 'EMP-'.$company->id.$unit->id.'02',
                    'name' => 'Garçom '.$unit->name,
                    'email' => 'garcom.'.Str::slug($unit->name).'@'.Str::slug($company->trade_name).'.com',
                    'password' => Hash::make('password123'),
                    'phone' => '11977777777',
                    'document' => '55566677788',
                    'birth_date' => '1995-08-20',
                    'hire_date' => '2024-02-01',
                    'status' => EmployeeStatusEnum::ACTIVE,
                    'role' => EmployeeRoleEnum::WAITER,
                ]);

                // Cadastrar um operador de caixa fixo para testes
                Employee::create([
                    'uuid' => (string) Str::uuid(),
                    'company_id' => $company->id,
                    'unit_id' => $unit->id,
                    'employee_number' => 'EMP-'.$company->id.$unit->id.'03',
                    'name' => 'Caixa '.$unit->name,
                    'email' => 'caixa.'.Str::slug($unit->name).'@'.Str::slug($company->trade_name).'.com',
                    'password' => Hash::make('password123'),
                    'phone' => '11966666666',
                    'document' => '88899900011',
                    'birth_date' => '1990-12-15',
                    'hire_date' => '2024-03-01',
                    'status' => EmployeeStatusEnum::ACTIVE,
                    'role' => EmployeeRoleEnum::CASHIER,
                ]);

                // Cadastrar um auxiliar de cozinha fixo para testes
                Employee::create([
                    'uuid' => (string) Str::uuid(),
                    'company_id' => $company->id,
                    'unit_id' => $unit->id,
                    'employee_number' => 'EMP-'.$company->id.$unit->id.'04',
                    'name' => 'Cozinheiro '.$unit->name,
                    'email' => 'cozinha.'.Str::slug($unit->name).'@'.Str::slug($company->trade_name).'.com',
                    'password' => Hash::make('password123'),
                    'phone' => '11955555555',
                    'document' => '33344455566',
                    'birth_date' => '1988-02-28',
                    'hire_date' => '2024-01-15',
                    'status' => EmployeeStatusEnum::ACTIVE,
                    'role' => EmployeeRoleEnum::KITCHEN,
                ]);
            }
        }
    }
}
