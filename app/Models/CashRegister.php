<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CashRegister extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'cash_registers';

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'shift_id',
        'shift_name',
        'user_id',
        'closed_by',
        'opening_cash',
        'status',
        'opened_at',
        'last_activity_at',
        'closed_at',
        'expected_cash',
        'actual_cash',
        'difference',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'opening_cash' => 'decimal:4',
            'expected_cash' => 'decimal:4',
            'actual_cash' => 'decimal:4',
            'difference' => 'decimal:4',
            'opened_at' => 'datetime',
            'last_activity_at' => 'datetime',
            'closed_at' => 'datetime',
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

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(CashMovement::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(RegisterSession::class, 'register_id');
    }
}
