<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PosVoid extends Model
{
    use HasUuids;
    use HasFactory;

    protected $table = 'pos_voids';

    protected $fillable = [
        'tenant_id',
        'order_number',
        'user_id',
        'user_name',
        'product_id',
        'product_name',
        'round_number',
        'quantity',
        'price',
        'discount',
        'discount_type',
        'total',
        'is_confirmed',
        'reason',
        'voided_by',
        'voided_by_name',
        'bundle',
        'date_created',
        'date_voided',
    ];

    protected function casts(): array
    {
        return [
            'round_number' => 'integer',
            'quantity' => 'decimal:4',
            'price' => 'decimal:4',
            'discount' => 'decimal:4',
            'discount_type' => 'integer',
            'total' => 'decimal:4',
            'is_confirmed' => 'boolean',
            'date_created' => 'datetime',
            'date_voided' => 'datetime',
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

    public function voidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'voided_by');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
