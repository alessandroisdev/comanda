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
        Schema::create('orders_sessions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->foreignId('unit_id')->constrained('company_units')->restrictOnDelete();
            $table->foreignId('table_id')->nullable()->constrained('tables')->restrictOnDelete();
            $table->foreignId('opened_by_employee_id')->constrained('employees')->restrictOnDelete();
            $table->foreignId('closed_by_employee_id')->nullable()->constrained('employees')->restrictOnDelete();
            $table->string('status', 30)->default('open'); // open, closed, cancelled
            $table->dateTime('opened_at');
            $table->dateTime('closed_at')->nullable();
            $table->integer('people_count')->default(1);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('opened_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders_sessions');
    }
};
