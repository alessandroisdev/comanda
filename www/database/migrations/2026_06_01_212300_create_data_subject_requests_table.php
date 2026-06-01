<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_subject_requests', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->string('subject_type', 50); // customer, employee, user
            $table->uuid('subject_uuid');
            $table->string('request_type', 50); // confirmation, access, correction, anonymization, blocking, deletion, portability, sharing_info, consent_revocation, opposition
            $table->string('status', 30)->default('pending'); // pending, in_progress, completed, rejected
            $table->timestamp('deadline_at');
            $table->string('assigned_to', 150)->nullable();
            $table->text('response_content')->nullable();
            $table->text('evidence_notes')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index('subject_uuid');
            $table->index('request_type');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_subject_requests');
    }
};
