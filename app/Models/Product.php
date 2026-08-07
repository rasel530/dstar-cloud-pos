<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    protected $table = 'products';

    protected $fillable = [
        'tenant_id',
        'product_group_id',
        'name',
        'code',
        'plu',
        'measurement_unit',
        'price',
        'mrp',
        'cost',
        'markup',
        'last_purchase_price',
        'is_tax_inclusive_price',
        'is_price_change_allowed',
        'is_service',
        'is_using_default_quantity',
        'track_inventory',
        'is_global',
        'is_enabled',
        'description',
        'image',
        'color',
        'age_restriction',
        'rank',
    ];

    protected $casts = [
        'price'                     => 'decimal:4',
        'mrp'                       => 'decimal:4',
        'cost'                      => 'decimal:4',
        'markup'                    => 'decimal:4',
        'last_purchase_price'       => 'decimal:4',
        'is_tax_inclusive_price'    => 'boolean',
        'is_price_change_allowed'   => 'boolean',
        'is_service'                => 'boolean',
        'is_using_default_quantity' => 'boolean',
        'track_inventory'           => 'boolean',
        'is_enabled'                => 'boolean',
        'rank'                      => 'integer',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function productGroup()
    {
        return $this->belongsTo(ProductGroup::class);
    }

    public function barcodes()
    {
        return $this->hasMany(Barcode::class);
    }

    public function taxes()
    {
        return $this->belongsToMany(Tax::class, 'product_taxes');
    }

    public function productComments()
    {
        return $this->hasMany(ProductComment::class);
    }

    public function stocks()
    {
        return $this->hasMany(Stock::class);
    }

    public function stockControls()
    {
        return $this->hasMany(StockControl::class);
    }

    public function priceListItems()
    {
        return $this->hasMany(PriceListItem::class);
    }

    public function posOrderItems()
    {
        return $this->hasMany(PosOrderItem::class);
    }

    public function documentItems()
    {
        return $this->hasMany(DocumentItem::class);
    }

    public function branchInventories()
    {
        return $this->hasMany(BranchInventory::class);
    }

    protected $appends = ['stock', 'branch_stocks'];

    public function getStockAttribute(): float
    {
        if (!$this->relationLoaded('stocks')) return 0;
        return round((float) $this->stocks->sum('quantity'), 2);
    }

    public function getBranchStocksAttribute(): array
    {
        if (!$this->relationLoaded('branchInventories')) return [];
        return $this->branchInventories->map(function ($bi) {
            return [
                'branch_id'   => $bi->branch_id,
                'branch_name' => $bi->branch?->name,
                'stock'       => round((float) $bi->stock, 2),
                'reserved'    => round((float) $bi->reserved_stock, 2),
                'minimum'     => round((float) $bi->minimum_stock, 2),
                'maximum'     => round((float) $bi->maximum_stock, 2),
            ];
        })->values()->toArray();
    }
}
