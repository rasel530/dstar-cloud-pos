<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Shift extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'shifts';

    protected $fillable = [
        'tenant_id',
        'name',
        'start_time',
        'end_time',
        'ordinal',
        'is_enabled',
    ];

    protected function casts(): array
    {
        return [
            'ordinal' => 'integer',
            'is_enabled' => 'boolean',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
