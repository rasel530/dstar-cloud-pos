<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class FloorPlan extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'floor_plans';

    protected $fillable = [
        'tenant_id',
        'name',
        'color',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function floorPlanTables(): HasMany
    {
        return $this->hasMany(FloorPlanTable::class);
    }
}
