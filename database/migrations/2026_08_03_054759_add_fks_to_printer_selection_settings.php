<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::table('pos_printer_selection_settings', function (Blueprint $table) {
            $table->foreign('pos_printer_selection_id')->references('id')->on('pos_printer_selections')->onDelete('cascade');
        });
    }
    public function down(): void {
        Schema::table('pos_printer_selection_settings', function (Blueprint $table) {
            $table->dropForeign(['pos_printer_selection_id']);
        });
    }
};
