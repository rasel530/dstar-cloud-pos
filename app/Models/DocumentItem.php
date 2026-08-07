<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class DocumentItem extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'document_items';

    protected $fillable = [
        'document_id',
        'product_id',
        'quantity',
        'expected_quantity',
        'price',
        'price_before_tax',
        'price_before_tax_after_discount',
        'price_after_discount',
        'discount',
        'discount_type',
        'discount_apply_rule',
        'product_cost',
        'total',
        'total_after_document_discount',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:4',
            'expected_quantity' => 'decimal:4',
            'price' => 'decimal:4',
            'price_before_tax' => 'decimal:4',
            'price_before_tax_after_discount' => 'decimal:4',
            'price_after_discount' => 'decimal:4',
            'discount' => 'decimal:4',
            'discount_type' => 'integer',
            'discount_apply_rule' => 'integer',
            'product_cost' => 'decimal:4',
            'total' => 'decimal:4',
            'total_after_document_discount' => 'decimal:4',
        ];
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function documentItemTaxes(): HasMany
    {
        return $this->hasMany(DocumentItemTax::class);
    }

    public function documentItemExpirationDate(): HasOne
    {
        return $this->hasOne(DocumentItemExpirationDate::class);
    }
}
