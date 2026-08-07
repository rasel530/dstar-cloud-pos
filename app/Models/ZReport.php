<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ZReport extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'z_reports';

    protected $fillable = [
        'tenant_id',
        'user_id',
        'number',
        'from_document_id',
        'to_document_id',
        'starting_report_id',
        'report_date',
        'period_from',
        'period_to',
        'total_sales',
        'gross_revenue',
        'total_discount',
        'total_tax',
        'total_refunds',
        'net_revenue',
        'total_cash',
        'total_card',
        'total_digital_wallet',
        'total_bank_transfer',
        'payment_breakdown',
        'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'number' => 'integer',
            'total_sales' => 'integer',
            'gross_revenue' => 'decimal:4',
            'total_discount' => 'decimal:4',
            'total_tax' => 'decimal:4',
            'total_refunds' => 'decimal:4',
            'net_revenue' => 'decimal:4',
            'total_cash' => 'decimal:4',
            'total_card' => 'decimal:4',
            'total_digital_wallet' => 'decimal:4',
            'total_bank_transfer' => 'decimal:4',
            'payment_breakdown' => 'array',
            'report_date' => 'date',
            'period_from' => 'datetime',
            'period_to' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function fromDocument(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'from_document_id');
    }

    public function toDocument(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'to_document_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
