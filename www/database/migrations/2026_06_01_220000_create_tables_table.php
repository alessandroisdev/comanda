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
        Schema::create('tables', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->foreignId('unit_id')->constrained('company_units')->restrictOnDelete();
            $table->string('code', 50);
            $table->string('name', 100);
            $table->integer('capacity')->default(4);
            $table->string('sector', 100)->default('Salão');
            $table->string('status', 30)->default('available'); // available, occupied, reserved, blocked, cleaning
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            // Índice de busca rápido e unicidade do código da mesa por unidade física
            $table->unique(['unit_id', 'code']);
            $table->index('status');
            $table->index('sector');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tables');
    }
};
