<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserActivityLog extends Model
{
    use HasUuids;

    protected $table = 'user_activity_logs';

    protected $fillable = [
        'user_id', 'tenant_id', 'branch_id', 'module', 'action', 'event', 'reference',
        'url', 'method', 'ip_address', 'device', 'details',
    ];

    protected function casts(): array
    {
        return [
            'details' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'branch_id');
    }
}
