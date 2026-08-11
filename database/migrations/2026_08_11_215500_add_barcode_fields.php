<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('barcodes', function (Blueprint $table) {
            if (!Schema::hasColumn('barcodes', 'barcode_type')) {
                $table->string('barcode_type', 20)->default('CODE_128');
            }
            if (!Schema::hasColumn('barcodes', 'is_enabled')) {
                $table->boolean('is_enabled')->default(true);
            }
        });
    }

    public function down(): void
    {
        Schema::table('barcodes', function (Blueprint $table) {
            $table->dropColumn(['barcode_type', 'is_enabled']);
        });
    }
};
