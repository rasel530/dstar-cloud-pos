<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class PurchaseReturn extends Model
{
    use HasUuids;

    protected $fillable = [
        'tenant_id', 'purchase_id', 'supplier_id', 'warehouse_id',
        'return_number', 'return_date', 'subtotal', 'tax_total',
        'grand_total', 'reason', 'status', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'return_date' => 'datetime',
            'subtotal'    => 'decimal:4',
            'tax_total'   => 'decimal:4',
            'grand_total' => 'decimal:4',
        ];
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Customer::class, 'supplier_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseReturnItem::class, 'return_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function generateNumber(): string
    {
        $prefix = 'PR-' . now()->format('Ym') . '-';
        $last = static::where('return_number', 'like', $prefix . '%')
            ->orderBy('return_number', 'desc')
            ->first();

        if ($last) {
            $seq = (int) substr($last->return_number, strlen($prefix)) + 1;
        } else {
            $seq = 1;
        }

        return $prefix . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }
}
