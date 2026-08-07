<?php

return [
    'default_side_visible_min_level' => 0,

    'roles' => [
        0 => ['label' => 'Cashier', 'description' => 'Sales only'],
        5 => ['label' => 'Manager', 'description' => 'Management + reports'],
        9 => ['label' => 'Admin', 'description' => 'Full access'],
    ],

    'modules' => [
        'pos'        => ['label' => 'POS',     'view' => 0, 'create' => 0, 'edit' => 0, 'delete' => 5],
        'orders'     => ['label' => 'Orders',  'view' => 0, 'create' => 0, 'edit' => 0, 'delete' => 0],
        'products'   => ['label' => 'Products','view' => 0, 'create' => 5, 'edit' => 5, 'delete' => 9],
        'customers'  => ['label' => 'Customers','view' => 0, 'create' => 5, 'edit' => 5, 'delete' => 9],
        'reports'    => ['label' => 'Reports', 'view' => 5, 'create' => 9, 'edit' => 9, 'delete' => 9],
        'promotions' => ['label' => 'Promos',  'view' => 5, 'create' => 9, 'edit' => 9, 'delete' => 9],
        'loyalty'    => ['label' => 'Loyalty', 'view' => 5, 'create' => 9, 'edit' => 9, 'delete' => 9],
        'dashboard'  => ['label' => 'Dashboard','view' => 5, 'create' => 9, 'edit' => 9, 'delete' => 9],
        'users'      => ['label' => 'Users',   'view' => 9, 'create' => 9, 'edit' => 9, 'delete' => 9],
        'roles'      => ['label' => 'Roles',   'view' => 9, 'create' => 9, 'edit' => 9, 'delete' => 9],
        'settings'   => ['label' => 'Settings','view' => 9, 'create' => 9, 'edit' => 9, 'delete' => 9],
        'branches'   => ['label' => 'Branches','view' => 9, 'create' => 9, 'edit' => 9, 'delete' => 9],
        'taxes'      => ['label' => 'Taxes',   'view' => 9, 'create' => 9, 'edit' => 9, 'delete' => 9],
        'printers'   => ['label' => 'Printers','view' => 9, 'create' => 9, 'edit' => 9, 'delete' => 9],
        'fiscal'     => ['label' => 'Fiscal',  'view' => 9, 'create' => 9, 'edit' => 9, 'delete' => 9],
    ],
];
