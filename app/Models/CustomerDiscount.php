<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class CustomerDiscount extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'customer_discounts';

    protected $fillable = [
        'customer_id',
        'type',
        'uid',
        'value',
    ];

    protected $casts = [
        'type'  => 'integer',
        'uid'   => 'integer',
        'value' => 'decimal:4',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
