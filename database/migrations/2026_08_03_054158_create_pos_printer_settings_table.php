<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_printer_settings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->nullable()->constrained('tenants')->cascadeOnDelete();
            $table->string('printer_name', 255)->unique();
            $table->integer('paper_width')->default(32);
            $table->text('header')->nullable();
            $table->text('footer')->nullable();
            $table->integer('feed_lines')->default(0);
            $table->boolean('cut_paper')->default(true);
            $table->boolean('print_bitmap')->default(false);
            $table->boolean('open_cash_drawer')->default(true);
            $table->text('cash_drawer_command')->nullable();
            $table->smallInteger('header_alignment')->default(0);
            $table->smallInteger('footer_alignment')->default(0);
            $table->boolean('is_formatting_enabled')->default(true);
            $table->smallInteger('printer_type')->default(0);
            $table->integer('number_of_copies')->default(1);
            $table->integer('code_page')->default(-1);
            $table->integer('character_set')->default(-1);
            $table->integer('margin')->default(0);
            $table->decimal('left_margin', 8, 2)->default(0);
            $table->decimal('top_margin', 8, 2)->default(0);
            $table->decimal('right_margin', 8, 2)->default(0);
            $table->decimal('bottom_margin', 8, 2)->default(0);
            $table->boolean('print_barcode')->default(true);
            $table->string('font_name', 255)->nullable();
            $table->decimal('font_size_percent', 8, 2)->default(100.00);
            $table->boolean('print_logo_full_width')->default(false);
            $table->timestamps();

            $table->index('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_printer_settings');
    }
};
