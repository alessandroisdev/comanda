<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consents', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->string('subject_type', 50); // customer, employee
            $table->unsignedBigInteger('subject_id');
            $table->uuid('subject_uuid');
            $table->string('purpose', 150);
            $table->text('consent_text');
            $table->string('term_version', 30)->default('1.0');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('status', 30)->default('granted'); // granted, revoked
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();

            $table->index(['subject_type', 'subject_id']);
            $table->index('subject_uuid');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consents');
    }
};
