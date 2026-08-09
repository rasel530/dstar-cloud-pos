<?php

declare(strict_types=1);

namespace App\Services\Inventory;

use App\Models\Stock;
use App\Models\StockMovement;
use InvalidArgumentException;
use RuntimeException;

class StockService
{
    public function adjust(string $productId, string $warehouseId, float $newQuantity, string $userId, string $tenantId, string $note = null): array
    {
        if ($newQuantity < 0) {
            throw new InvalidArgumentException('Stock quantity cannot be negative.');
        }

        $stock = $this->findOrCreateStock($productId, $warehouseId, $tenantId);

        $previousQuantity = (float) $stock->quantity;
        $change = $newQuantity - $previousQuantity;

        $affected = Stock::where('id', $stock->id)
            ->where('version', $stock->version)
            ->update([
                'quantity' => $newQuantity,
                'version' => $stock->version + 1,
            ]);

        if ($affected === 0) {
            throw new RuntimeException("Stock record for product {$productId} was modified by another transaction. Please retry.");
        }

        $movementType = $change >= 0 ? 'increment' : 'decrement';

        $movement = $this->logMovement(
            $productId, $warehouseId, $movementType, $change, $newQuantity,
            $userId, $tenantId, null, null, $note ?? 'Manual adjustment'
        );

        $stock->refresh();

        return [
            'stock' => $stock,
            'movement' => $movement,
        ];
    }

    public function decrement(string $productId, string $warehouseId, float $quantity, string $userId, string $tenantId, string $referenceType = null, string $referenceId = null): array
    {
        if ($quantity <= 0) {
            throw new InvalidArgumentException('Decrement quantity must be greater than zero.');
        }

        $stock = $this->findOrCreateStock($productId, $warehouseId, $tenantId);

        if ((float) $stock->quantity < $quantity) {
            throw new RuntimeException("Insufficient stock for product {$productId}. Available: {$stock->quantity}, requested: {$quantity}.");
        }

        $previousQuantity = (float) $stock->quantity;
        $newQuantity = $previousQuantity - $quantity;

        $affected = Stock::where('id', $stock->id)
            ->where('version', $stock->version)
            ->update([
                'quantity' => $newQuantity,
                'version' => $stock->version + 1,
            ]);

        if ($affected === 0) {
            throw new RuntimeException("Stock record for product {$productId} was modified by another transaction. Please retry.");
        }

        $movement = $this->logMovement(
            $productId, $warehouseId, 'decrement', -$quantity, $newQuantity,
            $userId, $tenantId, $referenceType, $referenceId, 'Stock decrement'
        );

        $stock->refresh();

        return [
            'stock' => $stock,
            'movement' => $movement,
        ];
    }

    public function increment(string $productId, string $warehouseId, float $quantity, string $userId, string $tenantId): array
    {
        if ($quantity <= 0) {
            throw new InvalidArgumentException('Increment quantity must be greater than zero.');
        }

        $stock = $this->findOrCreateStock($productId, $warehouseId, $tenantId);

        $previousQuantity = (float) $stock->quantity;
        $newQuantity = $previousQuantity + $quantity;

        $affected = Stock::where('id', $stock->id)
            ->where('version', $stock->version)
            ->update([
                'quantity' => $newQuantity,
                'version' => $stock->version + 1,
            ]);

        if ($affected === 0) {
            throw new RuntimeException("Stock record for product {$productId} was modified by another transaction. Please retry.");
        }

        $movement = $this->logMovement(
            $productId, $warehouseId, 'increment', $quantity, $newQuantity,
            $userId, $tenantId, null, null, 'Stock increment'
        );

        $stock->refresh();

        return [
            'stock' => $stock,
            'movement' => $movement,
        ];
    }

    public function getCurrentStock(string $productId, string $warehouseId): float
    {
        $stock = Stock::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->first();

        if (!$stock) {
            return 0.0;
        }

        return (float) $stock->quantity;
    }

    public function logMovement(string $productId, string $warehouseId, string $movementType, float $quantityChange, float $quantityAfter, string $userId, string $tenantId, ?string $referenceType = null, ?string $referenceId = null, string $note = ''): StockMovement
    {
        return StockMovement::create([
            'tenant_id' => $tenantId,
            'product_id' => $productId,
            'warehouse_id' => $warehouseId,
            'movement_type' => $movementType,
            'quantity_change' => $quantityChange,
            'quantity_after' => $quantityAfter,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'user_id' => $userId,
            'note' => $note,
        ]);
    }

    private function findOrCreateStock(string $productId, string $warehouseId, string $tenantId): Stock
    {
        $stock = Stock::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->lockForUpdate()
            ->first();

        if (!$stock) {
            $stock = Stock::create([
                'tenant_id' => $tenantId,
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
                'quantity' => 0,
                'version' => 0,
            ]);
        }

        return $stock;
    }
}
