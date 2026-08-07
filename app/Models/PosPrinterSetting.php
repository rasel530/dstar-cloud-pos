<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PosPrinterSetting extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'pos_printer_settings';

    protected $fillable = [
        'tenant_id',
        'printer_name',
        'paper_width',
        'header',
        'footer',
        'feed_lines',
        'cut_paper',
        'print_bitmap',
        'open_cash_drawer',
        'cash_drawer_command',
        'header_alignment',
        'footer_alignment',
        'is_formatting_enabled',
        'printer_type',
        'number_of_copies',
        'code_page',
        'character_set',
    ];

    protected function casts(): array
    {
        return [
            'paper_width' => 'integer',
            'feed_lines' => 'integer',
            'cut_paper' => 'boolean',
            'print_bitmap' => 'boolean',
            'open_cash_drawer' => 'boolean',
            'header_alignment' => 'integer',
            'footer_alignment' => 'integer',
            'is_formatting_enabled' => 'boolean',
            'printer_type' => 'integer',
            'number_of_copies' => 'integer',
            'code_page' => 'integer',
            'character_set' => 'integer',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
