<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashMovement extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'cash_movements';

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'cash_register_id',
        'user_id',
        'type',
        'amount',
        'reason',
        'date',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:4',
            'date' => 'date',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'branch_id');
    }

    public function cashRegister(): BelongsTo
    {
        return $this->belongsTo(CashRegister::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
