<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FiscalItem extends Model
{
    use HasFactory;

    protected $table = 'fiscal_items';

    protected $primaryKey = 'plu';

    public $incrementing = false;

    protected $fillable = [
        'tenant_id',
        'plu',
        'name',
        'vat',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
