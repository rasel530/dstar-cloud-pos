<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;
    use HasUuids;
    use SoftDeletes;

    protected $table = 'users';

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'first_name',
        'last_name',
        'username',
        'employee_number',
        'email',
        'password',
        'access_level',
        'is_enabled',
        'can_edit_price',
        'pin_code',
        'pin_attempts',
        'pin_locked_until',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'pin_code',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'pin_code'          => 'hashed',
            'pin_attempts'      => 'integer',
            'pin_locked_until'  => 'datetime',
            'access_level'      => 'integer',
            'is_enabled'        => 'boolean',
            'can_edit_price'    => 'boolean',
            'last_login_at'     => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function branches(): BelongsToMany
    {
        return $this->belongsToMany(Tenant::class, 'user_branches', 'user_id', 'branch_id')
            ->withTimestamps()
            ->withPivot([]);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function posOrders(): HasMany
    {
        return $this->hasMany(PosOrder::class);
    }

    public function posOrderItems(): HasMany
    {
        return $this->hasMany(PosOrderItem::class);
    }

    public function posVoids(): HasMany
    {
        return $this->hasMany(PosVoid::class);
    }

    public function voidedPosVoids(): HasMany
    {
        return $this->hasMany(PosVoid::class, 'voided_by');
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function startingCash(): HasMany
    {
        return $this->hasMany(StartingCash::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
