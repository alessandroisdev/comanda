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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->foreignId('category_id')->constrained('categories')->restrictOnDelete();
            $table->string('sku', 100)->nullable();
            $table->string('barcode', 100)->nullable();
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->bigInteger('price_cents');
            $table->bigInteger('cost_cents')->nullable();
            $table->string('status', 30)->default('active'); // active, inactive
            $table->string('image', 255)->nullable();
            $table->integer('preparation_time')->default(0); // em minutos
            $table->timestamps();
            $table->softDeletes();

            // SKU único por empresa
            $table->unique(['company_id', 'sku']);
            
            // Índices para buscas rápidas
            $table->index('status');
            $table->index('barcode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
