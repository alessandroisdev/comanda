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
        Schema::create('modules', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('code', 50)->unique(); // pdv, delivery, totem, etc.
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->string('status', 30)->default('active'); // active, inactive
            $table->json('dependencies')->nullable(); // array de dependências
            $table->string('version_min', 30)->default('1.0.0');
            $table->integer('price_suggested_cents')->default(0);
            $table->timestamps();
        });

        Schema::create('licenses', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique(); // license_uuid
            $table->string('client_name', 150);
            $table->string('client_email', 150);
            $table->string('client_document', 30); // CPF/CNPJ
            $table->string('plan_name', 100);
            $table->string('type', 30)->default('subscription'); // trial, subscription, perpetual, developer, internal
            $table->string('status', 30)->default('active'); // active, trial, expired, suspended, cancelled, blocked
            $table->text('key_data')->nullable(); // base64 payload assinado
            $table->timestamp('issued_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        Schema::create('license_modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('license_id')->constrained('licenses')->onDelete('cascade');
            $table->foreignId('module_id')->constrained('modules')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('license_installations', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique(); // installation_uuid
            $table->string('hostname', 150);
            $table->string('domain', 150)->nullable();
            $table->string('ip_address', 45);
            $table->string('fingerprint', 255)->index();
            $table->string('status', 30)->default('active'); // active, blocked
            $table->timestamps();
        });

        Schema::create('license_activations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('license_id')->constrained('licenses')->onDelete('cascade');
            $table->uuid('installation_uuid')->index();
            $table->string('hostname', 150);
            $table->string('domain', 150)->nullable();
            $table->string('ip_address', 45);
            $table->string('fingerprint', 255);
            $table->string('status', 30)->default('active'); // active, revoked
            $table->timestamp('activated_at')->useCurrent();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();
        });

        Schema::create('license_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('license_id')->nullable()->constrained('licenses')->onDelete('set null');
            $table->uuid('installation_uuid')->nullable()->index();
            $table->string('action', 50); // issue, activate, renew, suspend, cancel, module_change
            $table->json('details')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('license_audit_logs');
        Schema::dropIfExists('license_activations');
        Schema::dropIfExists('license_installations');
        Schema::dropIfExists('license_modules');
        Schema::dropIfExists('licenses');
        Schema::dropIfExists('modules');
    }
};
