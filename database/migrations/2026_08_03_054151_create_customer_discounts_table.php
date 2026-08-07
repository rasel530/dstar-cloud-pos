<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_discounts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('customer_id');
            $table->smallInteger('type')->default(0);
            $table->integer('uid')->nullable();
            $table->decimal('value', 12, 4)->default(0);
            $table->timestamps();

            $table->unique(['customer_id', 'type', 'uid']);
            $table->index('customer_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_discounts');
    }
};
