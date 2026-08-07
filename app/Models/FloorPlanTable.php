<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class FloorPlanTable extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'floor_plan_tables';

    protected $fillable = [
        'floor_plan_id',
        'name',
        'position_x',
        'position_y',
        'width',
        'height',
        'is_round',
    ];

    protected function casts(): array
    {
        return [
            'position_x' => 'float',
            'position_y' => 'float',
            'width' => 'float',
            'height' => 'float',
            'is_round' => 'boolean',
        ];
    }

    public function floorPlan(): BelongsTo
    {
        return $this->belongsTo(FloorPlan::class);
    }
}
