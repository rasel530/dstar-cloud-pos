<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('z_reports', function (Blueprint $table) {
            $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('report_date')->nullable();
            $table->dateTime('period_from')->nullable();
            $table->dateTime('period_to')->nullable();
            $table->uuid('starting_report_id')->nullable();
            $table->integer('total_sales')->default(0);
            $table->decimal('gross_revenue', 12, 4)->default(0);
            $table->decimal('total_discount', 12, 4)->default(0);
            $table->decimal('total_tax', 12, 4)->default(0);
            $table->decimal('total_refunds', 12, 4)->default(0);
            $table->decimal('net_revenue', 12, 4)->default(0);
            $table->decimal('total_cash', 12, 4)->default(0);
            $table->decimal('total_card', 12, 4)->default(0);
            $table->decimal('total_digital_wallet', 12, 4)->default(0);
            $table->decimal('total_bank_transfer', 12, 4)->default(0);
            $table->json('payment_breakdown')->nullable();
            $table->dateTime('closed_at')->nullable();

            $table->index('user_id');
            $table->index('report_date');
            $table->index('closed_at');
        });
    }

    public function down(): void
    {
        Schema::table('z_reports', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
            $table->dropColumn([
                'report_date',
                'period_from',
                'period_to',
                'starting_report_id',
                'total_sales',
                'gross_revenue',
                'total_discount',
                'total_tax',
                'total_refunds',
                'net_revenue',
                'total_cash',
                'total_card',
                'total_digital_wallet',
                'total_bank_transfer',
                'payment_breakdown',
                'closed_at',
            ]);
        });
    }
};
