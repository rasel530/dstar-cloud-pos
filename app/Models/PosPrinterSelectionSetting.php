<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PosPrinterSelectionSetting extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'pos_printer_selection_settings';

    protected $fillable = [
        'pos_printer_selection_id',
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
        'margin',
        'left_margin',
        'top_margin',
        'right_margin',
        'bottom_margin',
        'print_barcode',
        'font_name',
        'font_size_percent',
        'print_logo_full_width',
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
            'margin' => 'integer',
            'left_margin' => 'decimal:4',
            'top_margin' => 'decimal:4',
            'right_margin' => 'decimal:4',
            'bottom_margin' => 'decimal:4',
            'print_barcode' => 'boolean',
            'font_size_percent' => 'decimal:4',
            'print_logo_full_width' => 'boolean',
        ];
    }

    public function posPrinterSelection(): BelongsTo
    {
        return $this->belongsTo(PosPrinterSelection::class, 'pos_printer_selection_id');
    }
}
