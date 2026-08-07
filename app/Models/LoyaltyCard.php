<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class LoyaltyCard extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'loyalty_cards';

    protected $fillable = [
        'tenant_id',
        'customer_id',
        'card_number',
        'points_balance',
        'total_points_earned',
    ];

    protected $casts = [
        'points_balance'       => 'integer',
        'total_points_earned'  => 'integer',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function loyaltyTransactions()
    {
        return $this->hasMany(LoyaltyTransaction::class);
    }
}
