<?php

return [
    'product_grid' => [
        'columns' => 5,
        'rows' => 4,
    ],
    'description' => [
        'heading_font_size' => 28,
        'character_spacing' => 0,
        'horizontal_scale' => 100,
        'line_spacing' => 1.0,
        'print_mode' => 0,
    ],
    'defaults' => [
        'tax_rate' => 10.0,
        'currency' => 'USD',
        'decimal_places' => 2,
        'price_decimal_places' => 4,
        'rounding_rule' => 'nearest_5_cents',
    ],
    'payment' => [
        'confirmation_dialog' => true,
        'allow_negative_price' => false,
        'cost_price_validation' => true,
    ],
    'notifications' => [
        'duration' => 2500,
        'position' => 'bottom-center',
        'slide_in' => true,
    ],
    'sounds' => [
        'item_added' => false,
        'item_not_found' => false,
        'navigation' => false,
    ],
    'measurement_units' => [
        'pcs' => 'Pieces',
        'kg' => 'Kilograms',
        'g' => 'Grams',
        'l' => 'Liters',
        'ml' => 'Milliliters',
        'm' => 'Meters',
        'cm' => 'Centimeters',
        'oz' => 'Ounces',
        'lb' => 'Pounds',
        'box' => 'Box',
        'pack' => 'Pack',
        'bottle' => 'Bottle',
        'can' => 'Can',
        'cup' => 'Cup',
        'serving' => 'Serving',
    ],
];
