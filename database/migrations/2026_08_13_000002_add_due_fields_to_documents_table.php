<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->decimal('paid_amount', 12, 4)->default(0)->after('total');
            $table->decimal('due_amount', 12, 4)->default(0)->after('paid_amount');
            $table->decimal('tax_amount', 12, 4)->default(0)->after('due_amount');
        });

        DB::statement("
            UPDATE documents SET
                paid_amount = CASE WHEN paid_status = 1 THEN total ELSE 0 END,
                due_amount = CASE WHEN paid_status = 1 THEN 0 ELSE total END
        ");
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn(['paid_amount', 'due_amount', 'tax_amount']);
        });
    }
};
