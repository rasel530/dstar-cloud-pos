<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PosOrder extends Model
{
    use HasUuids;
    use HasFactory;

    protected $table = 'pos_orders';

    protected $fillable = [
        'tenant_id',
        'user_id',
        'customer_id',
        'branch_id',
        'number',
        'discount',
        'discount_type',
        'total',
        'paid_amount',
        'change_amount',
        'payment_method',
        'tax_amount',
        'service_type',
        'table_number',
        'status',
        'held_at',
        'closed_at',
        'expired_at',
    ];

    protected function casts(): array
    {
        return [
            'discount' => 'decimal:4',
            'discount_type' => 'integer',
            'total' => 'decimal:4',
            'paid_amount' => 'decimal:4',
            'change_amount' => 'decimal:4',
            'tax_amount' => 'decimal:4',
            'service_type' => 'integer',
            'held_at' => 'datetime',
            'closed_at' => 'datetime',
            'expired_at' => 'datetime',
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

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function posOrderItems(): HasMany
    {
        return $this->hasMany(PosOrderItem::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'branch_id');
    }

    public function posVoids(): HasMany
    {
        return $this->hasMany(PosVoid::class);
    }

    public function document(): HasOne
    {
        return $this->hasOne(Document::class, 'order_number', 'number');
    }
}
