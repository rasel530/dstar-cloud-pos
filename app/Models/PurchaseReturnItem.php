<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class PurchaseReturnItem extends Model
{
    use HasUuids;

    protected $fillable = [
        'return_id', 'purchase_item_id', 'product_id',
        'quantity', 'unit_cost', 'total', 'reason',
    ];

    protected function casts(): array
    {
        return [
            'quantity'  => 'decimal:4',
            'unit_cost' => 'decimal:4',
            'total'     => 'decimal:4',
        ];
    }

    public function purchaseReturn()
    {
        return $this->belongsTo(PurchaseReturn::class, 'return_id');
    }

    public function purchaseItem()
    {
        return $this->belongsTo(PurchaseItem::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
