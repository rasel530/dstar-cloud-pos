<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class PosPrinterSelection extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'pos_printer_selections';

    protected $fillable = [
        'tenant_id',
        'key',
        'printer_name',
        'is_enabled',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function posPrinterSelectionSettings(): HasMany
    {
        return $this->hasMany(PosPrinterSelectionSetting::class);
    }
}
