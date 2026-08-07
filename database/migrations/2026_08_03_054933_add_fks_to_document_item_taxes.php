<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('document_item_taxes', function (Blueprint $table) {
            $table->foreign('document_item_id')->references('id')->on('document_items')->onDelete('cascade');
            $table->foreign('tax_id')->references('id')->on('taxes')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('document_item_taxes', function (Blueprint $table) {
            $table->dropForeign(['document_item_id']);
            $table->dropForeign(['tax_id']);
        });
    }
};
