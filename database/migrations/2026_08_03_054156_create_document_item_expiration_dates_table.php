<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_item_expiration_dates', function (Blueprint $table) {
            $table->uuid('document_item_id')->primary();
            $table->date('expiration_date');

            $table->foreign('document_item_id')->references('id')->on('document_items')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_item_expiration_dates');
    }
};
