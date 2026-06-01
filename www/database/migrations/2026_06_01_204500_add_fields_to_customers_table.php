<?php

declare(strict_types=1);

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
        Schema::table('customers', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->after('uuid')->constrained('companies')->restrictOnDelete();
            $table->date('birth_date')->nullable()->after('document');
            $table->boolean('marketing_opt_in')->default(false)->after('birth_date');

            $table->index('marketing_opt_in');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropForeign(['customers_company_id_foreign']);
            $table->dropIndex(['marketing_opt_in']);
            $table->dropColumn(['company_id', 'birth_date', 'marketing_opt_in']);
        });
    }
};
