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
        Schema::create('licenses', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique(); // license_uuid público
            $table->uuid('installation_uuid')->index();
            $table->uuid('client_uuid')->index();
            $table->string('type', 30)->default('subscription'); // trial, subscription, perpetual
            $table->string('status', 30)->default('active'); // active, expired, suspended
            $table->text('key_data'); // base64 da licença completa
            $table->timestamp('issued_at');
            $table->timestamp('expires_at');
            $table->json('modules'); // array de chaves de módulos licenciados
            $table->timestamps();
        });

        Schema::create('license_validations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('license_id')->nullable()->constrained('licenses')->onDelete('cascade');
            $table->string('status', 30); // valid, expired, invalid
            $table->string('ip_address', 45)->nullable();
            $table->text('details')->nullable(); // log de erro se for invalid
            $table->timestamp('created_at')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('license_validations');
        Schema::dropIfExists('licenses');
    }
};
