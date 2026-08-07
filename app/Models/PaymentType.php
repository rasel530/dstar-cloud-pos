<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentType extends Model
{
    use HasUuids;
    use HasFactory;

    protected $table = 'payment_types';

    protected $fillable = [
        'tenant_id',
        'name',
        'code',
        'is_customer_required',
        'is_fiscal',
        'is_slip_required',
        'is_change_allowed',
        'is_quick_payment',
        'is_enabled',
        'open_cash_drawer',
        'shortcut_key',
        'mark_as_paid',
        'rounding_increment',
        'rounding_rule',
        'ordinal',
    ];

    protected function casts(): array
    {
        return [
            'is_customer_required' => 'boolean',
            'is_fiscal' => 'boolean',
            'is_slip_required' => 'boolean',
            'is_change_allowed' => 'boolean',
            'is_quick_payment' => 'boolean',
            'is_enabled' => 'boolean',
            'open_cash_drawer' => 'boolean',
            'mark_as_paid' => 'boolean',
            'rounding_increment' => 'decimal:4',
            'rounding_rule' => 'integer',
            'ordinal' => 'integer',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
