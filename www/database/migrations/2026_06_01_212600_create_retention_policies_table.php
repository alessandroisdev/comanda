<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('retention_policies', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('data_category', 100)->unique(); // customers, orders, employees, logs
            $table->integer('retention_months');
            $table->string('legal_obligation', 255)->nullable();
            $table->string('disposal_method', 50)->default('hard_delete'); // anonymization, hard_delete
            $table->timestamps();

            $table->index('data_category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('retention_policies');
    }
};
