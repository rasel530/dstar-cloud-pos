<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IncomeExpense extends Model
{
    use HasUuids;

    protected $table = 'income_expenses';

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'category_id',
        'user_id',
        'document_id',
        'reference_number',
        'type',
        'amount',
        'description',
        'payment_method',
        'date',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:4',
            'date' => 'date',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'branch_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(IncomeExpenseCategory::class, 'category_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public static function generateNumber(string $type): string
    {
        $prefix = ($type === 'income' ? 'INC-' : 'EXP-') . now()->format('Ym') . '-';
        $last = static::where('reference_number', 'like', $prefix . '%')
            ->orderBy('reference_number', 'desc')
            ->first();
        if ($last) {
            $seq = (int) substr($last->reference_number, strlen($prefix)) + 1;
        } else {
            $seq = 1;
        }
        return $prefix . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }
}
