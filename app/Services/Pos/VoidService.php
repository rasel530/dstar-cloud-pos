<?php

declare(strict_types=1);

namespace App\Services\Pos;

use App\Models\PosOrder;
use App\Models\PosOrderItem;
use App\Models\PosVoid;
use App\Models\User;
use App\Models\BranchInventory;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\Warehouse;
use InvalidArgumentException;
use RuntimeException;

class VoidService
{
    public function voidItem(string $orderId, string $itemId, string $userId, string $reason = null): array
    {
        $order = PosOrder::with('posOrderItems')->find($orderId);
        if (!$order) {
            throw new InvalidArgumentException("Order {$orderId} not found.");
        }

        $orderItem = $order->posOrderItems()->where('id', $itemId)->first();
        if (!$orderItem) {
            throw new InvalidArgumentException("Order item {$itemId} not found in order {$orderId}.");
        }

        if ($orderItem->voided_by) {
            throw new RuntimeException("Order item {$itemId} has already been voided.");
        }

        $user = User::find($userId);
        if (!$user) {
            throw new InvalidArgumentException("User {$userId} not found.");
        }

        $posVoid = PosVoid::create([
            'tenant_id' => $order->tenant_id,
            'order_number' => $order->number,
            'user_id' => $order->user_id,
            'user_name' => $user->name ?? '',
            'product_id' => $orderItem->product_id,
            'product_name' => $orderItem->product?->name ?? '',
            'round_number' => $orderItem->round_number,
            'quantity' => $orderItem->quantity,
            'price' => $orderItem->price,
            'discount' => $orderItem->discount,
            'discount_type' => $orderItem->discount_type,
            'total' => (float) $orderItem->quantity * (float) $orderItem->price,
            'is_confirmed' => true,
            'reason' => $reason,
            'voided_by' => $userId,
            'voided_by_name' => $user->name ?? '',
            'bundle' => $orderItem->bundle,
            'date_created' => $orderItem->date_created ?? now(),
            'date_voided' => now(),
        ]);

        $orderItem->update(['voided_by' => $userId]);

        $this->restoreStock($orderItem, $userId);

        return [
            'void' => $posVoid,
            'order_item' => $orderItem,
            'order' => $order,
        ];
    }

    public function voidOrder(string $orderId, string $userId, string $reason = null): array
    {
        $order = PosOrder::with('posOrderItems.product')->find($orderId);
        if (!$order) {
            throw new InvalidArgumentException("Order {$orderId} not found.");
        }

        $user = User::find($userId);
        if (!$user) {
            throw new InvalidArgumentException("User {$userId} not found.");
        }

        $unvoidedItems = $order->posOrderItems()->whereNull('voided_by')->get();

        if ($unvoidedItems->isEmpty()) {
            throw new RuntimeException("All items in order {$orderId} have already been voided.");
        }

        $voids = [];

        foreach ($unvoidedItems as $orderItem) {
            $posVoid = PosVoid::create([
                'tenant_id' => $order->tenant_id,
                'order_number' => $order->number,
                'user_id' => $order->user_id,
                'user_name' => $user->name ?? '',
                'product_id' => $orderItem->product_id,
                'product_name' => $orderItem->product?->name ?? '',
                'round_number' => $orderItem->round_number,
                'quantity' => $orderItem->quantity,
                'price' => $orderItem->price,
                'discount' => $orderItem->discount,
                'discount_type' => $orderItem->discount_type,
                'total' => (float) $orderItem->quantity * (float) $orderItem->price,
                'is_confirmed' => true,
                'reason' => $reason,
                'voided_by' => $userId,
                'voided_by_name' => $user->name ?? '',
                'bundle' => $orderItem->bundle,
                'date_created' => $orderItem->date_created ?? now(),
                'date_voided' => now(),
            ]);

            $orderItem->update(['voided_by' => $userId]);

            $this->restoreStock($orderItem, $userId);

            $voids[] = $posVoid;
        }

        return [
            'voids' => $voids,
            'order' => $order,
        ];
    }

    private function restoreStock(PosOrderItem $orderItem, string $userId): void
    {
        $product = $orderItem->product;
        if (! $product || $product->is_service || ! $product->track_inventory) {
            return;
        }

        $qty = (float) ($orderItem->quantity ?? 1);
        $tenantId = $orderItem->posOrder?->tenant_id;
        $branchId = $orderItem->posOrder?->branch_id;

        $warehouse = Warehouse::where('tenant_id', $tenantId)->where('is_default', true)->first();
        if ($warehouse) {
            $stock = Stock::where('product_id', $product->id)
                ->where('warehouse_id', $warehouse->id)
                ->where('tenant_id', $tenantId)
                ->lockForUpdate()
                ->first();
            if ($stock) {
                $prevQty = (float) $stock->quantity;
                $stock->quantity = $prevQty + $qty;
                $stock->version += 1;
                $stock->save();

                StockMovement::create([
                    'tenant_id' => $tenantId,
                    'product_id' => $product->id,
                    'warehouse_id' => $warehouse->id,
                    'movement_type' => 'increment',
                    'quantity_change' => $qty,
                    'quantity_after' => $stock->quantity,
                    'reference_type' => 'void',
                    'reference_id' => $orderItem->id,
                    'user_id' => $userId,
                    'note' => 'Void restoration',
                ]);
            }
        }

        if ($branchId) {
            $bi = BranchInventory::where('product_id', $product->id)
                ->where('branch_id', $branchId)->first();
            if ($bi) {
                $bi->updateStock($qty);
            }
        }
    }
}
