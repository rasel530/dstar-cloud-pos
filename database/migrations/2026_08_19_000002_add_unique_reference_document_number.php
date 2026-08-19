<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Prevent duplicate refunds: only one document may reference a given
        // original document number as a full refund (partial unique index).
        Schema::table('documents', function ($table) {
            $table->unique(['reference_document_number'], 'documents_reference_document_number_unique');
        });
    }

    public function down(): void
    {
        Schema::table('documents', function ($table) {
            $table->dropUnique('documents_reference_document_number_unique');
        });
    }
};
