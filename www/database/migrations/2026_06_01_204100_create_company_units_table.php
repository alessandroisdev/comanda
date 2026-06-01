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
        Schema::create('company_units', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->string('status', 30)->default('active');
            $table->string('name', 255);
            $table->string('document_number', 30)->nullable()->unique(); // CNPJ da filial
            $table->string('email', 150)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('zipcode', 15);
            $table->string('street', 255);
            $table->string('number', 30);
            $table->string('district', 150);
            $table->string('city', 150);
            $table->string('state', 5);
            $table->string('country', 100)->default('Brasil');
            $table->json('settings_json')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Índices de performance
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_units');
    }
};
