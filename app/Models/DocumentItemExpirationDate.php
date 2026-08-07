<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class DocumentItemExpirationDate extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'document_item_expiration_dates';

    protected $fillable = [
        'document_item_id',
        'expiration_date',
    ];

    protected function casts(): array
    {
        return [
            'expiration_date' => 'date',
        ];
    }

    public function documentItem(): BelongsTo
    {
        return $this->belongsTo(DocumentItem::class);
    }
}
