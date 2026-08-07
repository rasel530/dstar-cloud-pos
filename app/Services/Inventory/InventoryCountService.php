<?php

declare(strict_types=1);

namespace App\Services\Inventory;

use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;

class InventoryCountService
{
    private array $countSessions = [];

    public function startCount(string $tenantId, string $warehouseId, string $userId): array
    {
        $warehouse = Warehouse::find($warehouseId);
        if (!$warehouse) {
            throw new InvalidArgumentException("Warehouse {$warehouseId} not found.");
        }

        $stockRecords = Stock::where('tenant_id', $tenantId)
            ->where('warehouse_id', $warehouseId)
            ->with('product')
            ->get();

        $products = [];

        foreach ($stockRecords as $stock) {
            if (!$stock->product) {
                continue;
            }

            $products[] = [
                'product_id' => $stock->product_id,
                'product_name' => $stock->product->name,
                'product_code' => $stock->product->code,
                'expected_quantity' => (float) $stock->quantity,
                'counted_quantity' => 0.0,
                'difference' => null,
            ];
        }

        $countId = (string) Str::uuid();

        $this->countSessions[$countId] = [
            'id' => $countId,
            'tenant_id' => $tenantId,
            'warehouse_id' => $warehouseId,
            'warehouse_name' => $warehouse->name,
            'user_id' => $userId,
            'status' => 'in_progress',
            'started_at' => now()->toDateTimeString(),
            'products' => $products,
        ];

        return [
            'count_id' => $countId,
            'warehouse_id' => $warehouseId,
            'warehouse_name' => $warehouse->name,
            'products' => $products,
        ];
    }

    public function scanItem(string $countId, string $productId, float $countedQty): array
    {
        $session = $this->getSession($countId);

        if ($session['status'] !== 'in_progress') {
            throw new RuntimeException("Count session {$countId} is not in progress.");
        }

        if ($countedQty < 0) {
            throw new InvalidArgumentException('Counted quantity cannot be negative.');
        }

        $found = false;
        foreach ($session['products'] as &$product) {
            if ($product['product_id'] === $productId) {
                $product['counted_quantity'] = $countedQty;
                $product['difference'] = $countedQty - $product['expected_quantity'];
                $found = true;
                break;
            }
        }
        unset($product);

        if (!$found) {
            $productModel = Product::find($productId);
            if (!$productModel) {
                throw new InvalidArgumentException("Product {$productId} not found.");
            }

            $session['products'][] = [
                'product_id' => $productId,
                'product_name' => $productModel->name,
                'product_code' => $productModel->code,
                'expected_quantity' => 0.0,
                'counted_quantity' => $countedQty,
                'difference' => $countedQty,
            ];
        }

        $this->countSessions[$countId] = $session;

        return [
            'count_id' => $countId,
            'product_id' => $productId,
            'counted_quantity' => $countedQty,
            'status' => 'in_progress',
        ];
    }

    public function review(string $countId): array
    {
        $session = $this->getSession($countId);

        if ($session['status'] !== 'in_progress') {
            throw new RuntimeException("Count session {$countId} cannot be reviewed. Current status: {$session['status']}.");
        }

        $discrepancies = [];
        $matched = 0;
        $unmatched = 0;
        $notCounted = 0;

        foreach ($session['products'] as $product) {
            $difference = $product['difference'];

            if ($difference === null) {
                $notCounted++;
                $discrepancies[] = [
                    'product_id' => $product['product_id'],
                    'product_name' => $product['product_name'],
                    'expected_quantity' => $product['expected_quantity'],
                    'counted_quantity' => null,
                    'difference' => null,
                    'status' => 'not_counted',
                ];
            } elseif ($difference === 0.0 || abs($difference) < 0.0001) {
                $matched++;
                $discrepancies[] = [
                    'product_id' => $product['product_id'],
                    'product_name' => $product['product_name'],
                    'expected_quantity' => $product['expected_quantity'],
                    'counted_quantity' => $product['counted_quantity'],
                    'difference' => 0.0,
                    'status' => 'matched',
                ];
            } else {
                $unmatched++;
                $discrepancies[] = [
                    'product_id' => $product['product_id'],
                    'product_name' => $product['product_name'],
                    'expected_quantity' => $product['expected_quantity'],
                    'counted_quantity' => $product['counted_quantity'],
                    'difference' => $difference,
                    'status' => $difference > 0 ? 'surplus' : 'shortage',
                ];
            }
        }

        $session['status'] = 'reviewed';
        $this->countSessions[$countId] = $session;

        return [
            'count_id' => $countId,
            'summary' => [
                'total_products' => count($session['products']),
                'matched' => $matched,
                'unmatched' => $unmatched,
                'not_counted' => $notCounted,
            ],
            'discrepancies' => $discrepancies,
        ];
    }

    public function commit(string $countId, string $userId): array
    {
        $session = $this->getSession($countId);

        if ($session['status'] === 'committed') {
            throw new RuntimeException("Count session {$countId} has already been committed.");
        }

        return DB::transaction(function () use ($countId, $userId) {
            $session = $this->getSession($countId);
            $tenantId = $session['tenant_id'];
            $warehouseId = $session['warehouse_id'];
            $movements = [];
            $adjustments = [];

            foreach ($session['products'] as $product) {
                if ($product['difference'] === null || abs($product['difference']) < 0.0001) {
                    continue;
                }

                $stock = Stock::where('product_id', $product['product_id'])
                    ->where('warehouse_id', $warehouseId)
                    ->where('tenant_id', $tenantId)
                    ->lockForUpdate()
                    ->first();

                if (!$stock) {
                    $stock = Stock::create([
                        'tenant_id' => $tenantId,
                        'product_id' => $product['product_id'],
                        'warehouse_id' => $warehouseId,
                        'quantity' => 0,
                        'version' => 0,
                    ]);
                }

                $newQuantity = $product['counted_quantity'];
                $quantityChange = $newQuantity - (float) $stock->quantity;

                $affected = Stock::where('id', $stock->id)
                    ->where('version', $stock->version)
                    ->update([
                        'quantity' => $newQuantity,
                        'version' => $stock->version + 1,
                    ]);

                if ($affected === 0) {
                    throw new RuntimeException("Stock record for product {$product['product_id']} was modified by another transaction. Please retry.");
                }

                $movementType = $quantityChange >= 0 ? 'increment' : 'decrement';

                $movement = StockMovement::create([
                    'tenant_id' => $tenantId,
                    'product_id' => $product['product_id'],
                    'warehouse_id' => $warehouseId,
                    'movement_type' => $movementType,
                    'quantity_change' => $quantityChange,
                    'quantity_after' => $newQuantity,
                    'reference_type' => 'inventory_count',
                    'reference_id' => $countId,
                    'user_id' => $userId,
                    'note' => 'Inventory count adjustment',
                ]);

                $movements[] = $movement;
                $adjustments[] = [
                    'product_id' => $product['product_id'],
                    'product_name' => $product['product_name'],
                    'expected_quantity' => $product['expected_quantity'],
                    'counted_quantity' => $product['counted_quantity'],
                    'quantity_change' => $quantityChange,
                ];
            }

            $session['status'] = 'committed';
            $session['committed_at'] = now()->toDateTimeString();
            $this->countSessions[$countId] = $session;

            return [
                'count_id' => $countId,
                'status' => 'committed',
                'adjustments' => $adjustments,
                'movements' => $movements,
            ];
        });
    }

    private function getSession(string $countId): array
    {
        if (!isset($this->countSessions[$countId])) {
            throw new InvalidArgumentException("Count session {$countId} not found.");
        }

        return $this->countSessions[$countId];
    }
}
