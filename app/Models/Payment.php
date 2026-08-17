<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Payment extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'payments';

    protected $fillable = [
        'tenant_id',
        'document_id',
        'payment_type_id',
        'user_id',
        'amount',
        'rounding_adjustment',
        'date',
        'z_report_id',
        'cash_register_id',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:4',
            'rounding_adjustment' => 'decimal:4',
            'date' => 'date:Y-m-d',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function paymentType(): BelongsTo
    {
        return $this->belongsTo(PaymentType::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function zReport(): BelongsTo
    {
        return $this->belongsTo(ZReport::class);
    }

    public function cashRegister(): BelongsTo
    {
        return $this->belongsTo(CashRegister::class);
    }
}
