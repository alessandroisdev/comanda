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
        Schema::table('license_activations', function (Blueprint $table) {
            $table->index('status');
        });
        Schema::table('license_installations', function (Blueprint $table) {
            $table->index('status');
        });
        Schema::table('licenses', function (Blueprint $table) {
            $table->index('status');
            $table->index('type');
        });
        Schema::table('modules', function (Blueprint $table) {
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('license_activations', function (Blueprint $table) {
            $table->dropIndex(['status']);
        });
        Schema::table('license_installations', function (Blueprint $table) {
            $table->dropIndex(['status']);
        });
        Schema::table('licenses', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['type']);
        });
        Schema::table('modules', function (Blueprint $table) {
            $table->dropIndex(['status']);
        });
    }
};
