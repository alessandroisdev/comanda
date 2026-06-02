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
        Schema::table('orders', function (Blueprint $table) {
            $table->index('deleted_at');
        });
        Schema::table('orders_sessions', function (Blueprint $table) {
            $table->index('deleted_at');
        });
        Schema::table('products', function (Blueprint $table) {
            $table->index('deleted_at');
        });
        Schema::table('customers', function (Blueprint $table) {
            $table->index('deleted_at');
            $table->index('status');
        });
        Schema::table('employees', function (Blueprint $table) {
            $table->index('deleted_at');
            $table->index('status');
        });
        Schema::table('delivery_orders', function (Blueprint $table) {
            $table->index('status');
        });
        Schema::table('print_jobs', function (Blueprint $table) {
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['deleted_at']);
        });
        Schema::table('orders_sessions', function (Blueprint $table) {
            $table->dropIndex(['deleted_at']);
        });
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['deleted_at']);
        });
        Schema::table('customers', function (Blueprint $table) {
            $table->dropIndex(['deleted_at']);
            $table->dropIndex(['status']);
        });
        Schema::table('employees', function (Blueprint $table) {
            $table->dropIndex(['deleted_at']);
            $table->dropIndex(['status']);
        });
        Schema::table('delivery_orders', function (Blueprint $table) {
            $table->dropIndex(['status']);
        });
        Schema::table('print_jobs', function (Blueprint $table) {
            $table->dropIndex(['type']);
        });
    }
};
