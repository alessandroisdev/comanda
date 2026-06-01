<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->after('uuid')->constrained('companies')->restrictOnDelete();
            $table->foreignId('unit_id')->nullable()->after('company_id')->constrained('company_units')->restrictOnDelete();
            $table->string('employee_number', 50)->nullable()->after('unit_id');
            $table->date('birth_date')->nullable()->after('document');
            $table->date('hire_date')->nullable()->after('birth_date');
            $table->string('role', 50)->default('waiter')->after('status');

            // Índice de busca único para número de funcionário por empresa
            $table->unique(['company_id', 'employee_number']);
            $table->index('role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropForeign(['employees_company_id_foreign']);
            $table->dropForeign(['employees_unit_id_foreign']);
            $table->dropUnique(['company_id', 'employee_number']);
            $table->dropColumn(['company_id', 'unit_id', 'employee_number', 'birth_date', 'hire_date', 'role']);
        });
    }
};
