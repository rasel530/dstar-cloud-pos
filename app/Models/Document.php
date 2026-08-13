<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Document extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'documents';

    protected $fillable = [
        'tenant_id',
        'number',
        'user_id',
        'customer_id',
        'order_number',
        'date',
        'stock_date',
        'total',
        'paid_amount',
        'due_amount',
        'tax_amount',
        'discount',
        'discount_type',
        'discount_apply_rule',
        'is_clocked_out',
        'document_type_id',
        'warehouse_id',
        'reference_document_number',
        'internal_note',
        'note',
        'due_date',
        'paid_status',
        'service_type',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'stock_date' => 'datetime',
            'total' => 'decimal:4',
            'paid_amount' => 'decimal:4',
            'due_amount' => 'decimal:4',
            'tax_amount' => 'decimal:4',
            'discount' => 'decimal:4',
            'discount_type' => 'integer',
            'discount_apply_rule' => 'integer',
            'is_clocked_out' => 'boolean',
            'paid_status' => 'integer',
            'service_type' => 'integer',
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

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function documentType(): BelongsTo
    {
        return $this->belongsTo(DocumentType::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function documentItems(): HasMany
    {
        return $this->hasMany(DocumentItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function fromZReports(): HasMany
    {
        return $this->hasMany(ZReport::class, 'from_document_id');
    }

    public function toZReports(): HasMany
    {
        return $this->hasMany(ZReport::class, 'to_document_id');
    }
}
