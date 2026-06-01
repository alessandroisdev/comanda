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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('status', 30)->default('active');
            $table->string('legal_name', 255);
            $table->string('trade_name', 255);
            $table->string('document_type', 30);
            $table->string('document_number', 30)->unique();
            $table->string('email', 150)->unique();
            $table->string('phone', 30);
            $table->string('timezone', 50)->default('America/Sao_Paulo');
            $table->string('currency', 10)->default('BRL');
            $table->string('language', 10)->default('pt_BR');
            $table->string('logo', 255)->nullable();
            $table->json('settings_json')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Índices de busca e performance
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
