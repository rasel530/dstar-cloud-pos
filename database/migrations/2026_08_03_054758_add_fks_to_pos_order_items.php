<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::table('pos_order_items', function (Blueprint $table) {
            $table->foreign('pos_order_id')->references('id')->on('pos_orders')->onDelete('cascade');
        });
    }
    public function down(): void {
        Schema::table('pos_order_items', function (Blueprint $table) {
            $table->dropForeign(['pos_order_id']);
        });
    }
};
