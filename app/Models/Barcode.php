<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Barcode extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'barcodes';

    protected $fillable = [
        'product_id',
        'value',
        'barcode_type',
        'is_primary',
        'is_enabled',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'is_enabled' => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
