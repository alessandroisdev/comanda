<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_inventories', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('data_name', 150);
            $table->string('data_category', 50); // public, internal, confidential, restricted, sensitive
            $table->string('processing_purpose', 255);
            $table->string('legal_basis', 100);
            $table->string('data_subject_type', 50); // customer, employee, user
            $table->string('table_name', 100)->nullable();
            $table->string('column_name', 100)->nullable();
            $table->string('retention_period', 100)->nullable();
            $table->string('security_measures', 255)->nullable();
            $table->timestamps();

            $table->index('data_category');
            $table->index('data_subject_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_inventories');
    }
};
