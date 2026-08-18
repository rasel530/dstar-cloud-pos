<?php

declare(strict_types=1);

namespace App\Services\Pos;

use App\Models\Document;
use App\Models\DocumentItem;
use App\Models\DocumentItemTax;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\Tax;
use Exception;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

class CheckoutService
{
    protected CartService $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    public function checkout(array $cart, array $paymentData, string $userId, string $tenantId): array
    {
        if (!$this->cartService->validate($cart)) {
            throw new InvalidArgumentException('Cart is invalid or empty.');
        }

        $paymentTypeId = $paymentData['payment_type_id'] ?? null;
        if (empty($paymentTypeId)) {
            throw new InvalidArgumentException('Payment type is required.');
        }

        $amount = (float) ($paymentData['amount'] ?? $this->cartService->total($cart));
        $discount = (float) ($paymentData['discount'] ?? 0);
        $customerId = $paymentData['customer_id'] ?? null;
        $warehouseId = $paymentData['warehouse_id'] ?? null;
        $documentTypeId = $paymentData['document_type_id'] ?? null;

        return DB::transaction(function () use ($cart, $amount, $discount, $paymentTypeId, $userId, $tenantId, $customerId, $warehouseId, $documentTypeId) {
            $subtotal = $this->cartService->subtotal($cart);

            $document = Document::create([
                'tenant_id' => $tenantId,
                'user_id' => $userId,
                'customer_id' => $customerId,
                'number' => $this->generateDocumentNumber($tenantId),
                'date' => now(),
                'stock_date' => now(),
                'total' => $subtotal - $discount,
                'discount' => $discount,
                'discount_type' => 0,
                'discount_apply_rule' => 0,
                'is_clocked_out' => true,
                'document_type_id' => $documentTypeId,
                'warehouse_id' => $warehouseId,
                'paid_status' => 1,
                'service_type' => 0,
            ]);

            foreach ($cart as $cartItem) {
                $product = Product::find($cartItem['id']);
                if (!$product) {
                    throw new RuntimeException("Product {$cartItem['id']} not found.");
                }

                $quantity = (float) ($cartItem['quantity'] ?? 1);
                $price = (float) ($cartItem['price'] ?? 0);
                $lineTotal = $price * $quantity;

                $documentItem = DocumentItem::create([
                    'document_id' => $document->id,
                    'product_id' => $cartItem['id'],
                    'quantity' => $quantity,
                    'expected_quantity' => $quantity,
                    'price' => $price,
                    'price_before_tax' => $price,
                    'price_before_tax_after_discount' => $price,
                    'price_after_discount' => $price,
                    'discount' => 0,
                    'discount_type' => 0,
                    'discount_apply_rule' => 0,
                    'product_cost' => $product->cost ?? 0,
                    'total' => $lineTotal,
                    'total_after_document_discount' => $lineTotal,
                ]);

                $productTaxes = $product->taxes()->where('is_enabled', true)->get();
                foreach ($productTaxes as $tax) {
                    $taxAmount = $this->calculateTaxAmount($price * $quantity, $tax);
                    DocumentItemTax::create([
                        'document_item_id' => $documentItem->id,
                        'tax_id' => $tax->id,
                        'amount' => $taxAmount,
                    ]);
                }

                if ($warehouseId && !$product->is_service) {
                    $this->decrementStock($cartItem['id'], $warehouseId, $quantity, $userId, $tenantId, Document::class, $document->id);
                }

                $branchId = session('active_branch_id');
                if ($branchId && !$product->is_service) {
                    $bi = \App\Models\BranchInventory::where('product_id', $cartItem['id'])
                        ->where('branch_id', $branchId)->first();
                    if ($bi) {
                        $bi->updateStock(-$quantity);
                    } else {
                        \App\Models\BranchInventory::create([
                            'tenant_id' => $tenantId,
                            'product_id' => $cartItem['id'],
                            'branch_id' => $branchId,
                            'stock' => -$quantity,
                        ]);
                    }
                }
            }

            $payment = Payment::create([
                'tenant_id' => $tenantId,
                'document_id' => $document->id,
                'payment_type_id' => $paymentTypeId,
                'user_id' => $userId,
                'amount' => $amount,
                'rounding_adjustment' => 0,
                'date' => now(),
            ]);

            return [
                'document' => $document,
                'payment' => $payment,
            ];
        });
    }

    public function processRefund(string $documentId, string $userId, string $reason = null): array
    {
        $originalDocument = Document::with('documentItems')->find($documentId);
        if (!$originalDocument) {
            throw new InvalidArgumentException("Document {$documentId} not found.");
        }

        $alreadyRefunded = Document::where('reference_document_number', $originalDocument->number)
            ->where('tenant_id', $originalDocument->tenant_id)
            ->where('total', '<=', -$originalDocument->total)
            ->exists();
        if ($alreadyRefunded) {
            throw new InvalidArgumentException('This document has already been fully refunded.');
        }

        return DB::transaction(function () use ($originalDocument, $userId, $reason) {
            $refundDocument = Document::create([
                'tenant_id' => $originalDocument->tenant_id,
                'user_id' => $userId,
                'customer_id' => $originalDocument->customer_id,
                'number' => $this->generateDocumentNumber($originalDocument->tenant_id),
                'date' => now(),
                'stock_date' => now(),
                'total' => -$originalDocument->total,
                'discount' => 0,
                'discount_type' => 0,
                'discount_apply_rule' => 0,
                'is_clocked_out' => true,
                'document_type_id' => $originalDocument->document_type_id,
                'warehouse_id' => $originalDocument->warehouse_id,
                'reference_document_number' => $originalDocument->number,
                'internal_note' => $reason,
                'paid_status' => 1,
                'service_type' => 0,
            ]);

            foreach ($originalDocument->documentItems as $item) {
                $refundItem = DocumentItem::create([
                    'document_id' => $refundDocument->id,
                    'product_id' => $item->product_id,
                    'quantity' => -$item->quantity,
                    'expected_quantity' => -$item->quantity,
                    'price' => $item->price,
                    'price_before_tax' => $item->price_before_tax,
                    'price_before_tax_after_discount' => $item->price_before_tax_after_discount,
                    'price_after_discount' => $item->price_after_discount,
                    'discount' => 0,
                    'discount_type' => 0,
                    'discount_apply_rule' => 0,
                    'product_cost' => $item->product_cost,
                    'total' => -$item->total,
                    'total_after_document_discount' => -$item->total_after_document_discount,
                ]);

                foreach ($item->documentItemTaxes as $itemTax) {
                    DocumentItemTax::create([
                        'document_item_id' => $refundItem->id,
                        'tax_id' => $itemTax->tax_id,
                        'amount' => -$itemTax->amount,
                    ]);
                }

                $product = Product::find($item->product_id);
                if ($originalDocument->warehouse_id && $product && !$product->is_service) {
                    $this->incrementStock($item->product_id, $originalDocument->warehouse_id, (float) $item->quantity, $userId, $originalDocument->tenant_id, Document::class, $refundDocument->id);
                }
            }

            Payment::create([
                'tenant_id' => $originalDocument->tenant_id,
                'document_id' => $refundDocument->id,
                'payment_type_id' => $originalDocument->payments()->first()?->payment_type_id,
                'user_id' => $userId,
                'amount' => -$originalDocument->total,
                'rounding_adjustment' => 0,
                'date' => now(),
            ]);

            return [
                'document' => $refundDocument,
                'original_document' => $originalDocument,
            ];
        });
    }

    private function decrementStock(string $productId, string $warehouseId, float $quantity, string $userId, string $tenantId, string $referenceType, string $referenceId): void
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

        StockMovement::create([
            'tenant_id' => $tenantId,
            'product_id' => $productId,
            'warehouse_id' => $warehouseId,
            'movement_type' => 'decrement',
            'quantity_change' => -$quantity,
            'quantity_after' => $newQuantity,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'user_id' => $userId,
            'note' => 'Sale',
        ]);
    }

    private function incrementStock(string $productId, string $warehouseId, float $quantity, string $userId, string $tenantId, string $referenceType, string $referenceId): void
    {
        // A product can only have ONE stock row per (product_id, warehouse_id),
        // so lookup by product+warehouse only (matching StockService::findOrCreateStock).
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

        StockMovement::create([
            'tenant_id' => $tenantId,
            'product_id' => $productId,
            'warehouse_id' => $warehouseId,
            'movement_type' => 'increment',
            'quantity_change' => $quantity,
            'quantity_after' => $newQuantity,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'user_id' => $userId,
            'note' => 'Refund',
        ]);
    }

    private function generateDocumentNumber(string $tenantId): string
    {
        $lastDocument = Document::where('tenant_id', $tenantId)
            ->orderBy('number', 'desc')
            ->first();

        if (!$lastDocument || !$lastDocument->number) {
            return '1';
        }

        $lastNumber = (int) $lastDocument->number;

        return (string) ($lastNumber + 1);
    }

    private function calculateTaxAmount(float $amount, Tax $tax): float
    {
        if ($tax->is_fixed) {
            return round((float) $tax->rate, 4);
        }

        return round($amount * (float) $tax->rate, 4);
    }
}
