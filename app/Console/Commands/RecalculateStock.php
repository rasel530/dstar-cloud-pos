<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\Stock;
use App\Models\StockMovement;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RecalculateStock extends Command
{
    protected $signature = 'pos:recalculate-stock {tenant_id?} {--fix}';
    protected $description = 'Recalculate stock quantities from stock movements and fix discrepancies';

    public function handle(): int
    {
        $tenantId = $this->argument('tenant_id');

        if (! $tenantId) {
            $this->error('tenant_id is required.');
            return 1;
        }

        $shouldFix = $this->option('fix');

        $products = Product::where('tenant_id', $tenantId)->get();

        if ($products->isEmpty()) {
            $this->warn('No products found for this tenant.');
            return 0;
        }

        $this->info("Recalculating stock for {$products->count()} products...");

        $fixed = 0;
        $verified = 0;
        $rows = [];

        $this->withProgressBar($products, function (Product $product) use ($tenantId, $shouldFix, &$fixed, &$verified, &$rows) {
            $stocks = Stock::where('tenant_id', $tenantId)
                ->where('product_id', $product->id)
                ->get();

            $movements = StockMovement::where('tenant_id', $tenantId)
                ->where('product_id', $product->id)
                ->select('warehouse_id', DB::raw('SUM(quantity_change) as total_movement'))
                ->groupBy('warehouse_id')
                ->pluck('total_movement', 'warehouse_id');

            foreach ($stocks as $stock) {
                $expectedQty = (float) ($movements[$stock->warehouse_id] ?? 0);
                $currentQty = (float) $stock->quantity;

                if (abs($currentQty - $expectedQty) < 0.0001) {
                    $verified++;
                    $rows[] = [$product->name, $stock->warehouse->name ?? 'N/A', number_format($currentQty, 4), 'OK'];
                } else {
                    $this->warn("Discrepancy: {$product->name} \u2014 expected {$expectedQty}, found {$currentQty}");

                    if ($shouldFix) {
                        $stock->update(['quantity' => $expectedQty]);
                        $fixed++;
                        $rows[] = [$product->name, $stock->warehouse->name ?? 'N/A', number_format($expectedQty, 4), 'FIXED'];
                    } else {
                        $rows[] = [$product->name, $stock->warehouse->name ?? 'N/A', number_format($currentQty, 4), 'MISMATCH'];
                    }
                }
            }

            foreach ($movements as $warehouseId => $expectedQty) {
                $exists = $stocks->firstWhere('warehouse_id', $warehouseId);
                if (! $exists && abs($expectedQty) > 0.0001) {
                    $this->warn("Missing stock record: {$product->name} warehouse {$warehouseId}");

                    if ($shouldFix) {
                        Stock::create([
                            'tenant_id' => $tenantId,
                            'product_id' => $product->id,
                            'warehouse_id' => $warehouseId,
                            'quantity' => $expectedQty,
                            'version' => 1,
                        ]);
                        $fixed++;
                        $rows[] = [$product->name, 'warehouse:' . $warehouseId, number_format($expectedQty, 4), 'CREATED'];
                    }
                }
            }
        });

        $this->newLine();
        $this->newLine();

        $this->table(
            ['Product', 'Warehouse', 'Quantity', 'Status'],
            $rows
        );

        $this->newLine();
        $this->info("Verified: {$verified}  |  Fixed: {$fixed}  |  Total records: " . count($rows));

        return 0;
    }
}
