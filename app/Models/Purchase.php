<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasUuids;

    protected $fillable = [
        'tenant_id', 'supplier_id', 'warehouse_id', 'branch_id',
        'purchase_number', 'reference_number', 'purchase_date',
        'expected_date', 'received_date',
        'subtotal', 'discount', 'discount_type', 'tax_total',
        'shipping_cost', 'grand_total', 'paid_amount', 'due_amount',
        'status', 'payment_status', 'notes',
        'created_by', 'received_by',
    ];

    protected function casts(): array
    {
        return [
            'purchase_date' => 'datetime',
            'expected_date' => 'datetime',
            'received_date' => 'datetime',
            'subtotal'      => 'decimal:4',
            'discount'      => 'decimal:4',
            'tax_total'     => 'decimal:4',
            'shipping_cost' => 'decimal:4',
            'grand_total'   => 'decimal:4',
            'paid_amount'   => 'decimal:4',
            'due_amount'    => 'decimal:4',
            'discount_type' => 'integer',
        ];
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Customer::class, 'supplier_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function branch()
    {
        return $this->belongsTo(Tenant::class, 'branch_id');
    }

    public function items()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function returns()
    {
        return $this->hasMany(PurchaseReturn::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public static function generateNumber(): string
    {
        $prefix = 'PO-' . now()->format('Ym') . '-';
        $last = static::where('purchase_number', 'like', $prefix . '%')
            ->orderBy('purchase_number', 'desc')
            ->first();

        if ($last) {
            $seq = (int) substr($last->purchase_number, strlen($prefix)) + 1;
        } else {
            $seq = 1;
        }

        return $prefix . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }
}
