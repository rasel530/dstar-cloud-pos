<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ProductComment extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'product_comments';

    protected $fillable = [
        'product_id',
        'comment',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
