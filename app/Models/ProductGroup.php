<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ProductGroup extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'product_groups';

    protected $fillable = [
        'tenant_id',
        'name',
        'parent_group_id',
        'color',
        'image',
        'rank',
    ];

    protected $casts = [
        'rank' => 'integer',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function parentGroup()
    {
        return $this->belongsTo(ProductGroup::class, 'parent_group_id');
    }

    public function parent()
    {
        return $this->belongsTo(ProductGroup::class, 'parent_group_id');
    }

    public function children()
    {
        return $this->hasMany(ProductGroup::class, 'parent_group_id');
    }

    public function productGroups()
    {
        return $this->hasMany(ProductGroup::class, 'parent_group_id');
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
