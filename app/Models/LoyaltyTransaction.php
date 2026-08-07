<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class LoyaltyTransaction extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'loyalty_transactions';

    protected $fillable = [
        'loyalty_card_id',
        'points',
        'transaction_type',
        'reference_type',
        'reference_id',
    ];

    protected $casts = [
        'points' => 'integer',
    ];

    public function loyaltyCard()
    {
        return $this->belongsTo(LoyaltyCard::class);
    }
}
