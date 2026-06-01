<?php

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
        Schema::create('delivery_orders', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('unit_id')->constrained('company_units')->onDelete('cascade');
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->foreignId('delivery_zone_id')->nullable()->constrained('delivery_zones')->onDelete('set null');

            // Dados pessoais do destinatário (LGPD - Rastreável)
            $table->string('recipient_name');
            $table->string('recipient_phone');

            // Endereço de entrega
            $table->string('street');
            $table->string('number');
            $table->string('complement')->nullable();
            $table->string('neighborhood');
            $table->string('city');
            $table->string('state');
            $table->string('zip_code');

            // Valores e prazos
            $table->decimal('delivery_fee', 10, 2);
            $table->timestamp('estimated_delivery_time')->nullable();
            $table->string('status')->default('pending'); // pending, assigned, dispatched, delivered, cancelled
            $table->string('tracking_code')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_orders');
    }
};
