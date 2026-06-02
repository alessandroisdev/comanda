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
        Schema::create('backup_policies', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('frequency', 30)->default('daily'); // hourly, daily, weekly, monthly
            $table->string('destination', 50)->default('local'); // local, s3, minio
            $table->integer('retention_days')->default(30);
            $table->boolean('is_encrypted')->default(true);
            $table->timestamps();
        });

        Schema::create('backups', function (Blueprint $table) {
            $table->id();
            $table->string('filename', 255);
            $table->string('path', 255);
            $table->string('disk', 50)->default('local');
            $table->string('checksum', 64); // SHA-256
            $table->bigInteger('size_bytes');
            $table->boolean('is_encrypted')->default(true);
            $table->timestamps();
        });

        Schema::create('backup_executions', function (Blueprint $table) {
            $table->id();
            $table->string('type', 30)->default('backup'); // backup, restore
            $table->string('status', 30); // success, failed, running
            $table->timestamp('started_at');
            $table->timestamp('finished_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('backup_executions');
        Schema::dropIfExists('backups');
        Schema::dropIfExists('backup_policies');
    }
};
