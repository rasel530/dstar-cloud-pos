<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PromotionItem extends Model
{
    use HasUuids;
    use HasFactory;

    protected $table = 'promotion_items';

    protected $fillable = [
        'promotion_id',
        'uid',
        'discount_type',
        'price_type',
        'value',
        'is_conditional',
        'quantity',
        'condition_type',
        'quantity_limit',
    ];

    protected function casts(): array
    {
        return [
            'discount_type' => 'integer',
            'price_type' => 'integer',
            'value' => 'decimal:4',
            'is_conditional' => 'boolean',
            'quantity' => 'decimal:4',
            'condition_type' => 'integer',
            'quantity_limit' => 'decimal:4',
        ];
    }

    public function promotion(): BelongsTo
    {
        return $this->belongsTo(Promotion::class);
    }
}
