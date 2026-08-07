<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class StartingCash extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'starting_cash';

    protected $fillable = [
        'tenant_id',
        'user_id',
        'amount',
        'description',
        'starting_cash_type',
        'z_report_number',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:4',
            'starting_cash_type' => 'integer',
            'z_report_number' => 'integer',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
