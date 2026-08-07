<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tax extends Model
{
    use HasUuids;
    use HasFactory;

    protected $table = 'taxes';

    protected $fillable = [
        'tenant_id',
        'name',
        'rate',
        'code',
        'is_fixed',
        'is_tax_on_total',
        'is_enabled',
    ];

    protected function casts(): array
    {
        return [
            'rate' => 'decimal:4',
            'is_fixed' => 'boolean',
            'is_tax_on_total' => 'boolean',
            'is_enabled' => 'boolean',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_taxes');
    }

    public function documentItemTaxes(): HasMany
    {
        return $this->hasMany(DocumentItemTax::class);
    }
}
