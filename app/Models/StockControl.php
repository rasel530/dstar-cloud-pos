<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockControl extends Model
{
    use HasUuids;
    use HasFactory;

    protected $table = 'stock_controls';

    protected $fillable = [
        'tenant_id',
        'product_id',
        'customer_id',
        'reorder_point',
        'preferred_quantity',
        'is_low_stock_warning_enabled',
        'low_stock_warning_quantity',
        'minimum_stock',
        'maximum_stock',
        'opening_stock',
    ];

    protected function casts(): array
    {
        return [
            'reorder_point' => 'decimal:4',
            'preferred_quantity' => 'decimal:4',
            'is_low_stock_warning_enabled' => 'boolean',
            'low_stock_warning_quantity' => 'decimal:4',
            'minimum_stock' => 'decimal:4',
            'maximum_stock' => 'decimal:4',
            'opening_stock' => 'decimal:4',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
