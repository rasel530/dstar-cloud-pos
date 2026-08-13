<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegisterSession extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'register_sessions';

    protected $fillable = [
        'tenant_id',
        'register_id',
        'user_id',
        'started_at',
        'ended_at',
        'ended_reason',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function register(): BelongsTo
    {
        return $this->belongsTo(CashRegister::class, 'register_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
