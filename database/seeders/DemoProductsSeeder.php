<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductGroup;
use App\Models\Stock;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoProductsSeeder extends Seeder
{
    public function run(): void
    {
        $tenantId = '019fd8fe-ccc9-721a-b4db-5bcda6d66dc4';
        $warehouse = Warehouse::first();
        $warehouseId = $warehouse?->id;

        // Create or find product groups
        $groups = $this->createGroups($tenantId);

        $products = [
            // ===== BEVERAGES (ebc8052e-35a7-47b1-b237-8e1cedd7dc54) =====
            ['group' => 'beverages', 'name' => 'Orange Juice',       'code' => 'BEV007', 'plu' => 2007, 'price' => 3.00, 'cost' => 0.90, 'mrp' => 3.50, 'unit' => 'ml', 'color' => '#f97316', 'desc' => 'Freshly squeezed orange juice, 300ml'],
            ['group' => 'beverages', 'name' => 'Apple Juice',        'code' => 'BEV008', 'plu' => 2008, 'price' => 2.80, 'cost' => 0.85, 'mrp' => 3.20, 'unit' => 'ml', 'color' => '#84cc16', 'desc' => 'Pure apple juice, no added sugar, 300ml'],
            ['group' => 'beverages', 'name' => 'Mango Lassi',        'code' => 'BEV009', 'plu' => 2009, 'price' => 4.00, 'cost' => 1.50, 'mrp' => 4.50, 'unit' => 'ml', 'color' => '#eab308', 'desc' => 'Creamy mango yogurt drink, 350ml'],
            ['group' => 'beverages', 'name' => 'Lemonade',           'code' => 'BEV010', 'plu' => 2010, 'price' => 2.50, 'cost' => 0.60, 'mrp' => 3.00, 'unit' => 'ml', 'color' => '#fef08a', 'desc' => 'Fresh homemade lemonade, 350ml'],
            ['group' => 'beverages', 'name' => 'Coconut Water',      'code' => 'BEV011', 'plu' => 2011, 'price' => 2.00, 'cost' => 0.70, 'mrp' => 2.50, 'unit' => 'ml', 'color' => '#f5f5f4', 'desc' => 'Natural tender coconut water, 300ml'],

            // ===== COFFEE (ac92dc59-e378-4c8e-ab19-0f69c57f4031) =====
            ['group' => 'coffee', 'name' => 'Flat White',           'code' => 'COF006', 'plu' => 1006, 'price' => 4.00, 'cost' => 1.40, 'mrp' => 4.50, 'unit' => 'ml', 'color' => '#a8a29e', 'desc' => 'Double espresso with velvety microfoam, 200ml'],
            ['group' => 'coffee', 'name' => 'Macchiato',            'code' => 'COF007', 'plu' => 1007, 'price' => 3.50, 'cost' => 1.10, 'mrp' => 4.00, 'unit' => 'ml', 'color' => '#78716c', 'desc' => 'Espresso marked with a dollop of foam, 60ml'],
            ['group' => 'coffee', 'name' => 'Affogato',             'code' => 'COF008', 'plu' => 1008, 'price' => 5.50, 'cost' => 2.20, 'mrp' => 6.00, 'unit' => 'pc',  'color' => '#d6d3d1', 'desc' => 'Espresso poured over vanilla ice cream'],
            ['group' => 'coffee', 'name' => 'Cold Brew',            'code' => 'COF009', 'plu' => 1009, 'price' => 4.50, 'cost' => 1.30, 'mrp' => 5.00, 'unit' => 'ml', 'color' => '#57534e', 'desc' => 'Slow-steeped iced coffee, 350ml'],
            ['group' => 'coffee', 'name' => 'Turkish Coffee',       'code' => 'COF010', 'plu' => 1010, 'price' => 3.00, 'cost' => 0.80, 'mrp' => 3.50, 'unit' => 'ml', 'color' => '#292524', 'desc' => 'Traditional thick coffee served in cezve, 90ml'],

            // ===== FOOD (fd48d572-348d-40f3-b6ef-8e8372f938da) =====
            ['group' => 'food', 'name' => 'Club Sandwich',         'code' => 'FOD010', 'plu' => 3010, 'price' => 9.00, 'cost' => 4.00, 'mrp' => 10.00, 'unit' => 'pc', 'color' => '#d97706', 'desc' => 'Triple-decker with chicken, bacon, lettuce, tomato'],
            ['group' => 'food', 'name' => 'Caesar Salad',          'code' => 'FOD011', 'plu' => 3011, 'price' => 7.50, 'cost' => 3.00, 'mrp' => 8.50, 'unit' => 'pc', 'color' => '#65a30d', 'desc' => 'Romaine lettuce, croutons, parmesan, caesar dressing'],
            ['group' => 'food', 'name' => 'Margherita Pizza',      'code' => 'FOD012', 'plu' => 3012, 'price' => 10.00, 'cost' => 4.50, 'mrp' => 12.00, 'unit' => 'pc', 'color' => '#dc2626', 'desc' => 'Classic tomato, mozzarella, fresh basil, 10 inch'],
            ['group' => 'food', 'name' => 'Chicken Wrap',          'code' => 'FOD013', 'plu' => 3013, 'price' => 8.00, 'cost' => 3.50, 'mrp' => 9.00, 'unit' => 'pc', 'color' => '#fbbf24', 'desc' => 'Grilled chicken, veggies, garlic sauce in tortilla'],
            ['group' => 'food', 'name' => 'French Fries',          'code' => 'FOD014', 'plu' => 3014, 'price' => 4.00, 'cost' => 1.20, 'mrp' => 5.00, 'unit' => 'pc', 'color' => '#eab308', 'desc' => 'Crispy golden fries with seasoning, 250g'],

            // ===== CHOCOLATE (019fc74c-9cf3-7147-bcc5-706f85f3101a) =====
            ['group' => 'chocolate', 'name' => 'Dark Truffle',        'code' => 'CHO001', 'plu' => 4001, 'price' => 3.50, 'cost' => 1.50, 'mrp' => 4.00, 'unit' => 'pc', 'color' => '#451a03', 'desc' => 'Handmade 70% dark chocolate truffle'],
            ['group' => 'chocolate', 'name' => 'Milk Chocolate Bar',  'code' => 'CHO002', 'plu' => 4002, 'price' => 2.50, 'cost' => 1.00, 'mrp' => 3.00, 'unit' => 'pc', 'color' => '#78350f', 'desc' => 'Creamy milk chocolate bar, 50g'],
            ['group' => 'chocolate', 'name' => 'White Chocolate',     'code' => 'CHO003', 'plu' => 4003, 'price' => 2.80, 'cost' => 1.10, 'mrp' => 3.20, 'unit' => 'pc', 'color' => '#fef3c7', 'desc' => 'Smooth white chocolate with vanilla, 50g'],
            ['group' => 'chocolate', 'name' => 'Hazelnut Praline',    'code' => 'CHO004', 'plu' => 4004, 'price' => 4.00, 'cost' => 1.80, 'mrp' => 4.50, 'unit' => 'pc', 'color' => '#92400e', 'desc' => 'Roasted hazelnut in milk chocolate shell'],
            ['group' => 'chocolate', 'name' => 'Mint Chocolate',     'code' => 'CHO005', 'plu' => 4005, 'price' => 3.00, 'cost' => 1.30, 'mrp' => 3.50, 'unit' => 'pc', 'color' => '#064e3b', 'desc' => 'Refreshing mint cream in dark chocolate, 40g'],

            // ===== NEW GROUP: Pastries =====
            ['group' => 'pastries', 'name' => 'Butter Croissant',  'code' => 'PAS001', 'plu' => 5001, 'price' => 2.50, 'cost' => 0.80, 'mrp' => 3.00, 'unit' => 'pc', 'color' => '#fbbf24', 'desc' => 'Flaky French butter croissant, freshly baked'],
            ['group' => 'pastries', 'name' => 'Pain au Chocolat',  'code' => 'PAS002', 'plu' => 5002, 'price' => 3.00, 'cost' => 1.00, 'mrp' => 3.50, 'unit' => 'pc', 'color' => '#78350f', 'desc' => 'Layered pastry with dark chocolate batons'],
            ['group' => 'pastries', 'name' => 'Cinnamon Roll',     'code' => 'PAS003', 'plu' => 5003, 'price' => 3.50, 'cost' => 1.20, 'mrp' => 4.00, 'unit' => 'pc', 'color' => '#d97706', 'desc' => 'Swirled cinnamon pastry with cream cheese glaze'],
            ['group' => 'pastries', 'name' => 'Blueberry Muffin',  'code' => 'PAS004', 'plu' => 5004, 'price' => 3.00, 'cost' => 1.00, 'mrp' => 3.50, 'unit' => 'pc', 'color' => '#7c3aed', 'desc' => 'Moist muffin loaded with wild blueberries'],
            ['group' => 'pastries', 'name' => 'Cheese Danish',     'code' => 'PAS005', 'plu' => 5005, 'price' => 3.50, 'cost' => 1.20, 'mrp' => 4.00, 'unit' => 'pc', 'color' => '#fef3c7', 'desc' => 'Cream cheese filled flaky danish pastry'],

            // ===== NEW GROUP: Tea =====
            ['group' => 'tea', 'name' => 'Earl Grey Tea',          'code' => 'TEA001', 'plu' => 6001, 'price' => 2.00, 'cost' => 0.50, 'mrp' => 2.50, 'unit' => 'ml', 'color' => '#854d0e', 'desc' => 'Classic bergamot-infused black tea, 250ml'],
            ['group' => 'tea', 'name' => 'Jasmine Tea',            'code' => 'TEA002', 'plu' => 6002, 'price' => 2.00, 'cost' => 0.50, 'mrp' => 2.50, 'unit' => 'ml', 'color' => '#a3e635', 'desc' => 'Fragrant jasmine-scented green tea, 250ml'],
            ['group' => 'tea', 'name' => 'Peppermint Tea',         'code' => 'TEA003', 'plu' => 6003, 'price' => 2.00, 'cost' => 0.45, 'mrp' => 2.50, 'unit' => 'ml', 'color' => '#4ade80', 'desc' => 'Refreshing pure peppermint herbal infusion, 250ml'],
            ['group' => 'tea', 'name' => 'Masala Chai',            'code' => 'TEA004', 'plu' => 6004, 'price' => 3.00, 'cost' => 0.90, 'mrp' => 3.50, 'unit' => 'ml', 'color' => '#c2410c', 'desc' => 'Spiced Indian milk tea with cardamom & ginger, 250ml'],
            ['group' => 'tea', 'name' => 'Matcha Latte',           'code' => 'TEA005', 'plu' => 6005, 'price' => 4.50, 'cost' => 2.00, 'mrp' => 5.00, 'unit' => 'ml', 'color' => '#65a30d', 'desc' => 'Premium Japanese matcha whisked with steamed milk, 250ml'],

            // ===== NEW GROUP: Snacks =====
            ['group' => 'snacks', 'name' => 'Trail Mix',              'code' => 'SNK001', 'plu' => 7001, 'price' => 3.00, 'cost' => 1.20, 'mrp' => 3.50, 'unit' => 'pc', 'color' => '#a16207', 'desc' => 'Roasted nuts, dried fruits, and chocolate chips, 100g'],
            ['group' => 'snacks', 'name' => 'Granola Bar',            'code' => 'SNK002', 'plu' => 7002, 'price' => 2.50, 'cost' => 0.90, 'mrp' => 3.00, 'unit' => 'pc', 'color' => '#d97706', 'desc' => 'Oats & honey granola bar with almonds, 60g'],
            ['group' => 'snacks', 'name' => 'Potato Chips',           'code' => 'SNK003', 'plu' => 7003, 'price' => 1.50, 'cost' => 0.50, 'mrp' => 2.00, 'unit' => 'pc', 'color' => '#fbbf24', 'desc' => 'Classic salted potato chips, 50g'],
            ['group' => 'snacks', 'name' => 'Mixed Nuts',             'code' => 'SNK004', 'plu' => 7004, 'price' => 4.00, 'cost' => 1.80, 'mrp' => 4.50, 'unit' => 'pc', 'color' => '#78350f', 'desc' => 'Premium roasted cashews, almonds & pecans, 120g'],
            ['group' => 'snacks', 'name' => 'Popcorn',                'code' => 'SNK005', 'plu' => 7005, 'price' => 2.00, 'cost' => 0.60, 'mrp' => 2.50, 'unit' => 'pc', 'color' => '#fef3c7', 'desc' => 'Freshly popped butter popcorn, 80g'],

            // ===== NEW GROUP: Ice Cream =====
            ['group' => 'icecream', 'name' => 'Vanilla Scoop',        'code' => 'ICE001', 'plu' => 8001, 'price' => 2.00, 'cost' => 0.60, 'mrp' => 2.50, 'unit' => 'pc', 'color' => '#fef3c7', 'desc' => 'Madagascar vanilla bean ice cream, 1 scoop'],
            ['group' => 'icecream', 'name' => 'Chocolate Scoop',      'code' => 'ICE002', 'plu' => 8002, 'price' => 2.00, 'cost' => 0.65, 'mrp' => 2.50, 'unit' => 'pc', 'color' => '#451a03', 'desc' => 'Rich Belgian chocolate ice cream, 1 scoop'],
            ['group' => 'icecream', 'name' => 'Strawberry Scoop',     'code' => 'ICE003', 'plu' => 8003, 'price' => 2.00, 'cost' => 0.60, 'mrp' => 2.50, 'unit' => 'pc', 'color' => '#be123c', 'desc' => 'Fresh strawberry ice cream with real fruit, 1 scoop'],
            ['group' => 'icecream', 'name' => 'Sundae Special',       'code' => 'ICE004', 'plu' => 8004, 'price' => 6.00, 'cost' => 2.50, 'mrp' => 7.00, 'unit' => 'pc', 'color' => '#e11d48', 'desc' => '3 scoops, whipped cream, chocolate sauce, cherry'],
            ['group' => 'icecream', 'name' => 'Banana Split',         'code' => 'ICE005', 'plu' => 8005, 'price' => 7.00, 'cost' => 3.00, 'mrp' => 8.00, 'unit' => 'pc', 'color' => '#fbbf24', 'desc' => '3 scoops on banana, topped with sauces & nuts'],

            // ===== NEW GROUP: Bakery =====
            ['group' => 'bakery', 'name' => 'Sourdough Bread',        'code' => 'BAK001', 'plu' => 9001, 'price' => 4.00, 'cost' => 1.50, 'mrp' => 5.00, 'unit' => 'pc', 'color' => '#d6d3d1', 'desc' => 'Crusty artisan sourdough loaf, 500g'],
            ['group' => 'bakery', 'name' => 'Bagel Everything',       'code' => 'BAK002', 'plu' => 9002, 'price' => 2.50, 'cost' => 0.70, 'mrp' => 3.00, 'unit' => 'pc', 'color' => '#a8a29e', 'desc' => 'Everything-seasoned fresh bagel with cream cheese'],
            ['group' => 'bakery', 'name' => 'Banana Bread',           'code' => 'BAK003', 'plu' => 9003, 'price' => 3.50, 'cost' => 1.20, 'mrp' => 4.00, 'unit' => 'pc', 'color' => '#92400e', 'desc' => 'Moist banana bread slice with walnuts'],
            ['group' => 'bakery', 'name' => 'Focaccia',               'code' => 'BAK004', 'plu' => 9004, 'price' => 3.00, 'cost' => 1.00, 'mrp' => 3.50, 'unit' => 'pc', 'color' => '#a3a3a3', 'desc' => 'Italian olive oil flatbread with rosemary, 150g'],
            ['group' => 'bakery', 'name' => 'Chocolate Chip Cookie',  'code' => 'BAK005', 'plu' => 9005, 'price' => 2.00, 'cost' => 0.60, 'mrp' => 2.50, 'unit' => 'pc', 'color' => '#78350f', 'desc' => 'Warm gooey cookie with Belgian chocolate chips'],

            // ===== BEVERAGES (more cold drinks) =====
            ['group' => 'beverages', 'name' => 'Strawberry Smoothie', 'code' => 'BEV012', 'plu' => 2012, 'price' => 5.50, 'cost' => 2.00, 'mrp' => 6.00, 'unit' => 'ml', 'color' => '#e11d48', 'desc' => 'Fresh strawberry & banana smoothie, 400ml'],
            ['group' => 'beverages', 'name' => 'Iced Tea',            'code' => 'BEV013', 'plu' => 2013, 'price' => 2.50, 'cost' => 0.70, 'mrp' => 3.00, 'unit' => 'ml', 'color' => '#ca8a04', 'desc' => 'Freshly brewed iced tea with lemon, 350ml'],

            // ===== FOOD (more meals) =====
            ['group' => 'food', 'name' => 'Grilled Cheese',        'code' => 'FOD015', 'plu' => 3015, 'price' => 5.00, 'cost' => 2.00, 'mrp' => 6.00, 'unit' => 'pc', 'color' => '#eab308', 'desc' => 'Toasted sourdough with melted cheddar & mozzarella'],
            ['group' => 'food', 'name' => 'Pasta Alfredo',         'code' => 'FOD016', 'plu' => 3016, 'price' => 10.00, 'cost' => 4.00, 'mrp' => 12.00, 'unit' => 'pc', 'color' => '#fef3c7', 'desc' => 'Fettuccine in creamy parmesan Alfredo sauce'],
        ];

        $count = 0;
        $stockData = [];
        $productIds = [];

        foreach ($products as $data) {
            $groupId = $groups[$data['group']] ?? null;
            if (! $groupId) continue;

            // Skip if code already exists
            if (Product::where('code', $data['code'])->exists()) continue;

            $product = Product::create([
                'tenant_id'                => $tenantId,
                'product_group_id'         => $groupId,
                'name'                     => $data['name'],
                'code'                     => $data['code'],
                'plu'                      => $data['plu'],
                'price'                    => $data['price'],
                'cost'                     => $data['cost'],
                'mrp'                      => $data['mrp'],
                'measurement_unit'         => $data['unit'],
                'description'              => $data['desc'],
                'color'                    => $data['color'],
                'is_enabled'               => true,
                'is_tax_inclusive_price'   => true,
                'track_inventory'          => true,
                'is_global'                => true,
            ]);

            $productIds[] = $product->id;
            $count++;

            // Prepare stock data
            $stockQty = rand(30, 120);
            if ($warehouseId) {
                $stockData[] = [
                    'id'           => (string) Str::uuid(),
                    'tenant_id'    => $tenantId,
                    'product_id'   => $product->id,
                    'warehouse_id' => $warehouseId,
                    'quantity'     => $stockQty,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ];
            }
        }

        // Bulk insert stock
        if (! empty($stockData) && $warehouseId) {
            Stock::insert($stockData);
        }

        $this->command->info("Created {$count} demo products with stock records.");
    }

    private function createGroups(string $tenantId): array
    {
        $newGroups = [
            'pastries'  => 'Pastries',
            'tea'       => 'Tea',
            'snacks'    => 'Snacks',
            'icecream'  => 'Ice Cream',
            'bakery'    => 'Bakery',
        ];

        // Load ALL existing groups (not filtered by tenant_id, since groups have mixed tenant_ids)
        $existing = ProductGroup::pluck('id', 'name')
            ->mapWithKeys(fn ($id, $name) => [Str::slug($name) => $id])
            ->toArray();

        foreach ($newGroups as $key => $name) {
            if (! isset($existing[$key])) {
                $group = ProductGroup::create([
                    'tenant_id' => $tenantId,
                    'name'      => $name,
                ]);
                $existing[$key] = $group->id;
                $this->command->info("Created product group: {$name}");
            }
        }

        // Map group keys to IDs
        $map = [
            'coffee'     => $existing['coffee'] ?? null,
            'food'       => $existing['food'] ?? null,
            'beverages'  => $existing['beverages'] ?? null,
            'chocolate'  => $existing['chocolate'] ?? null,
            'pastries'   => $existing['pastries'] ?? null,
            'tea'        => $existing['tea'] ?? null,
            'snacks'     => $existing['snacks'] ?? null,
            'icecream'   => $existing['icecream'] ?? null,
            'bakery'     => $existing['bakery'] ?? null,
        ];

        return $map;
    }
}
