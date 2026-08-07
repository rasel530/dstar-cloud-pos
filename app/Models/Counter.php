<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Counter extends Model
{
    use HasFactory;

    protected $table = 'counters';

    public $timestamps = false;

    public $incrementing = false;

    protected $fillable = [
        'tenant_id',
        'name',
        'value',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'integer',
        ];
    }

    protected function setKeysForSaveQuery($query)
    {
        return $query->where('tenant_id', $this->getAttribute('tenant_id'))
                     ->where('name', $this->getAttribute('name'));
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
