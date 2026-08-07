<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_log', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('log_name', 255)->nullable();
            $table->text('description');
            $table->string('subject_type', 255)->nullable();
            $table->string('event', 255)->nullable();
            $table->uuid('subject_id')->nullable();
            $table->string('causer_type', 255)->nullable();
            $table->uuid('causer_id')->nullable();
            $table->jsonb('properties')->nullable();
            $table->uuid('batch_uuid')->nullable();
            $table->timestamps();

            $table->index('log_name');
            $table->index(['subject_type', 'subject_id']);
            $table->index(['causer_type', 'causer_id']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_log');
    }
};
