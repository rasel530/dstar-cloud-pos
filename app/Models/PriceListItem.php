<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PriceListItem extends Model
{
    use HasUuids;
    use HasFactory;

    protected $table = 'price_list_items';

    protected $fillable = [
        'price_list_id',
        'product_id',
        'price',
        'markup',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:4',
            'markup' => 'decimal:4',
        ];
    }

    public function priceList(): BelongsTo
    {
        return $this->belongsTo(PriceList::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
