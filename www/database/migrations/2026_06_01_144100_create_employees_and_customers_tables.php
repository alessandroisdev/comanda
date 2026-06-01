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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name', 150);
            $table->string('email', 150)->unique();
            $table->string('password');
            $table->string('phone', 30)->nullable();
            $table->string('document', 20)->nullable()->unique(); // CPF/CNPJ
            $table->string('status', 30)->default('active'); // active, suspended
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name', 150);
            $table->string('email', 150)->unique();
            $table->string('password');
            $table->string('phone', 30)->nullable();
            $table->string('document', 20)->nullable()->unique(); // CPF/CNPJ
            $table->string('status', 30)->default('active');
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
        Schema::dropIfExists('employees');
    }
};
