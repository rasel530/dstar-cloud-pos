<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ProductTax extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'product_taxes';

    protected $fillable = [
        'product_id',
        'tax_id',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function tax()
    {
        return $this->belongsTo(Tax::class);
    }
}
