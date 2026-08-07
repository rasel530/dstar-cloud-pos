<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::table('price_list_items', function (Blueprint $table) {
            $table->foreign('price_list_id')->references('id')->on('price_lists')->onDelete('cascade');
        });
    }
    public function down(): void {
        Schema::table('price_list_items', function (Blueprint $table) {
            $table->dropForeign(['price_list_id']);
        });
    }
};
