<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BranchInventory extends Model
{
    use HasUuids;
    use HasFactory;

    protected $table = 'branch_inventories';

    protected $fillable = [
        'tenant_id',
        'product_id',
        'branch_id',
        'stock',
        'reserved_stock',
        'minimum_stock',
        'maximum_stock',
        'last_purchase_price',
        'selling_price',
    ];

    protected function casts(): array
    {
        return [
            'stock' => 'decimal:4',
            'reserved_stock' => 'decimal:4',
            'minimum_stock' => 'decimal:4',
            'maximum_stock' => 'decimal:4',
            'last_purchase_price' => 'decimal:4',
            'selling_price' => 'decimal:4',
            'version' => 'integer',
        ];
    }

    public function updateStock(float $delta): bool
    {
        $newStock = (float) $this->stock + $delta;

        $affected = static::where('id', $this->id)
            ->where('version', $this->version)
            ->update([
                'stock' => $newStock,
                'version' => $this->version + 1,
            ]);

        if ($affected === 0) {
            throw new \RuntimeException('Branch inventory was modified by another transaction. Retry.');
        }

        $this->stock = $newStock;
        $this->version += 1;

        return true;
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'branch_id');
    }
}
