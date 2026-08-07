<?php

declare(strict_types=1);

namespace App\Services\Pos;

use InvalidArgumentException;

class CartService
{
    public function addItem(array $cart, array $product, float $quantity = 1): array
    {
        if ($quantity <= 0) {
            throw new InvalidArgumentException('Quantity must be greater than zero.');
        }

        if (empty($product['id'])) {
            throw new InvalidArgumentException('Product must have an id.');
        }

        if (!isset($product['price'])) {
            throw new InvalidArgumentException('Product must have a price.');
        }

        $existingIndex = $this->findItemIndex($cart, $product['id']);

        if ($existingIndex !== null) {
            $cart[$existingIndex]['quantity'] += $quantity;
            $cart[$existingIndex]['line_total'] = $cart[$existingIndex]['quantity'] * (float) $cart[$existingIndex]['price'];

            return $cart;
        }

        $item = [
            'id' => $product['id'],
            'name' => $product['name'] ?? '',
            'price' => (float) $product['price'],
            'quantity' => $quantity,
            'line_total' => (float) $product['price'] * $quantity,
        ];

        $cart[] = $item;

        return $cart;
    }

    public function removeItem(array $cart, int $index): array
    {
        if (!isset($cart[$index])) {
            throw new InvalidArgumentException("Cart item at index {$index} does not exist.");
        }

        array_splice($cart, $index, 1);

        return array_values($cart);
    }

    public function subtotal(array $cart): float
    {
        $sum = 0.0;

        foreach ($cart as $item) {
            $sum += $item['line_total'] ?? 0.0;
        }

        return round($sum, 4);
    }

    public function tax(float $subtotal, float $taxRate = 0.10): float
    {
        if ($taxRate < 0) {
            throw new InvalidArgumentException('Tax rate cannot be negative.');
        }

        return round($subtotal * $taxRate, 4);
    }

    public function total(array $cart, float $discount = 0, float $taxRate = 0.10): float
    {
        $subtotal = $this->subtotal($cart);
        $taxAmount = $this->tax($subtotal, $taxRate);

        if ($discount > $subtotal) {
            $discount = $subtotal;
        }

        return round($subtotal - $discount + $taxAmount, 4);
    }

    public function validate(array $cart): bool
    {
        if (empty($cart)) {
            return false;
        }

        foreach ($cart as $item) {
            if (empty($item['id'])) {
                return false;
            }

            if (!isset($item['price']) || (float) $item['price'] < 0) {
                return false;
            }

            if (!isset($item['quantity']) || (float) $item['quantity'] <= 0) {
                return false;
            }
        }

        return true;
    }

    private function findItemIndex(array $cart, string $productId): ?int
    {
        foreach ($cart as $index => $item) {
            if (($item['id'] ?? null) === $productId) {
                return $index;
            }
        }

        return null;
    }
}
