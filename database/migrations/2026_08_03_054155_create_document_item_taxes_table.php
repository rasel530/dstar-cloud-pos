<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_item_taxes', function (Blueprint $table) {
            $table->uuid('document_item_id');
            $table->uuid('tax_id');
            $table->decimal('amount', 12, 4)->default(0);

            $table->primary(['document_item_id', 'tax_id']);
            $table->index('document_item_id');
            $table->index('tax_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_item_taxes');
    }
};
