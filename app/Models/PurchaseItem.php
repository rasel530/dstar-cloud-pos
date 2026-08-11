<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class PurchaseItem extends Model
{
    use HasUuids;

    protected $fillable = [
        'purchase_id', 'product_id', 'quantity', 'received_quantity',
        'unit_cost', 'tax_id', 'tax_amount', 'discount', 'discount_type', 'total',
    ];

    protected function casts(): array
    {
        return [
            'quantity'          => 'decimal:4',
            'received_quantity' => 'decimal:4',
            'unit_cost'         => 'decimal:4',
            'tax_amount'        => 'decimal:4',
            'discount'          => 'decimal:4',
            'total'             => 'decimal:4',
            'discount_type'     => 'integer',
        ];
    }

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function tax()
    {
        return $this->belongsTo(Tax::class);
    }
}
