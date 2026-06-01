<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_sharing_records', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->string('recipient_name', 150);
            $table->string('sharing_purpose', 255);
            $table->string('legal_basis', 100);
            $table->text('shared_data'); // lista de campos compartilhados
            $table->string('security_measures', 255)->nullable();
            $table->timestamps();

            $table->index('recipient_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_sharing_records');
    }
};
