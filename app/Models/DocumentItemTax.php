<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class DocumentItemTax extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'document_item_taxes';

    public $incrementing = false;

    protected $fillable = [
        'document_item_id',
        'tax_id',
        'amount',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:4',
        ];
    }

    public function documentItem(): BelongsTo
    {
        return $this->belongsTo(DocumentItem::class);
    }

    public function tax(): BelongsTo
    {
        return $this->belongsTo(Tax::class);
    }
}
