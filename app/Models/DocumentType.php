<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentType extends Model
{
    use HasUuids;
    use HasFactory;

    protected $table = 'document_types';

    protected $fillable = [
        'tenant_id',
        'name',
        'code',
        'document_category_id',
        'warehouse_id',
        'stock_direction',
        'editor_type',
        'print_template',
        'price_type',
        'language_key',
    ];

    protected function casts(): array
    {
        return [
            'stock_direction' => 'integer',
            'editor_type' => 'integer',
            'price_type' => 'integer',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function documentCategory(): BelongsTo
    {
        return $this->belongsTo(DocumentCategory::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }
}
