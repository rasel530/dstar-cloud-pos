<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Refund documents previously received the literal number "1" because
        // the old generator sorted mixed text numbers and cast "ORD-..." to 0.
        // Renumber them with unique DOC-{Ymd}-{seq} values per tenant/date.
        $rows = DB::table('documents')
            ->where('number', '1')
            ->where('total', '<', 0)
            ->orderBy('created_at')
            ->get();

        $counters = [];
        foreach ($rows as $row) {
            $date = substr((string) $row->created_at, 0, 10);
            $prefix = 'DOC-' . str_replace('-', '', $date) . '-';

            if (!isset($counters[$prefix])) {
                $maxSeq = DB::table('documents')
                    ->where('tenant_id', $row->tenant_id)
                    ->where('number', 'like', $prefix . '%')
                    ->orderByDesc('number')
                    ->value('number');
                $start = $maxSeq ? ((int) substr($maxSeq, strlen($prefix))) + 1 : 1;
                $counters[$prefix] = $start;
            }

            DB::table('documents')
                ->where('id', $row->id)
                ->update(['number' => $prefix . str_pad((string) $counters[$prefix], 4, '0', STR_PAD_LEFT)]);

            $counters[$prefix]++;
        }
    }

    public function down(): void
    {
        // Intentionally empty: the old "1" numbers were meaningless.
    }
};
