<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('privacy_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->string('actor_type', 100)->nullable(); // User, Employee
            $table->string('entity_type', 100); // Customer, Employee, User
            $table->uuid('entity_uuid');
            $table->string('action', 50); // access, create, update, delete, export, print
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->uuid('correlation_id')->nullable();
            $table->timestamps();

            $table->index(['actor_type', 'actor_id']);
            $table->index(['entity_type', 'entity_uuid']);
            $table->index('action');
            $table->index('correlation_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('privacy_audit_logs');
    }
};
