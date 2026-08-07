<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VoidReason extends Model
{
    use HasUuids;
    use HasFactory;

    protected $table = 'void_reasons';

    protected $fillable = [
        'tenant_id',
        'name',
        'rank',
    ];

    protected function casts(): array
    {
        return [
            'rank' => 'integer',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
