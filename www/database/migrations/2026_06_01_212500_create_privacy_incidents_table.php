<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('privacy_incidents', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->string('incident_type', 150);
            $table->string('severity', 30)->default('low'); // low, medium, high, critical
            $table->text('affected_data');
            $table->integer('affected_subjects_count')->default(0);
            $table->text('description');
            $table->text('measures_taken')->nullable();
            $table->boolean('anpd_notified')->default(false);
            $table->boolean('subjects_notified')->default(false);
            $table->string('status', 30)->default('open'); // open, contained, resolved
            $table->timestamps();

            $table->index('severity');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('privacy_incidents');
    }
};
