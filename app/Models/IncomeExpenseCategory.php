<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IncomeExpenseCategory extends Model
{
    use HasUuids;

    protected $table = 'income_expense_categories';

    protected $fillable = [
        'tenant_id',
        'name',
        'type',
        'color',
        'icon',
        'rank',
        'is_enabled',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'rank' => 'integer',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function entries(): HasMany
    {
        return $this->hasMany(IncomeExpense::class, 'category_id');
    }
}
