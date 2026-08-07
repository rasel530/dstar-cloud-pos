<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BranchSetting extends Model
{
    use HasUuids;
    use HasFactory;

    protected $table = 'branch_settings';

    protected $fillable = [
        'branch_id',
        'key',
        'value',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'array',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'branch_id');
    }
}
