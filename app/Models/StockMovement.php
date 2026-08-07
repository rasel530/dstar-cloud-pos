<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    use HasUuids;
    use HasFactory;

    protected $table = 'stock_movements';

    protected $fillable = [
        'tenant_id',
        'product_id',
        'warehouse_id',
        'movement_type',
        'quantity_change',
        'quantity_after',
        'reference_type',
        'reference_id',
        'user_id',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'quantity_change' => 'decimal:4',
            'quantity_after' => 'decimal:4',
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

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
