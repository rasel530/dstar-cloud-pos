<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PosOrderItem extends Model
{
    use HasUuids;
    use HasFactory;

    protected $table = 'pos_order_items';

    protected $fillable = [
        'pos_order_id',
        'product_id',
        'round_number',
        'quantity',
        'price',
        'cost',
        'discount',
        'discount_type',
        'discount_applied_type',
        'is_locked',
        'is_featured',
        'voided_by',
        'comment',
        'bundle',
        'date_created',
    ];

    protected function casts(): array
    {
        return [
            'round_number' => 'integer',
            'quantity' => 'decimal:4',
            'price' => 'decimal:4',
            'cost' => 'decimal:4',
            'discount' => 'decimal:4',
            'discount_type' => 'integer',
            'discount_applied_type' => 'integer',
            'is_locked' => 'boolean',
            'is_featured' => 'boolean',
            'date_created' => 'date',
            'bundle' => 'array',
        ];
    }

    public function posOrder(): BelongsTo
    {
        return $this->belongsTo(PosOrder::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function voidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'voided_by');
    }
}
