<?php

declare(strict_types=1);

namespace App\Services\Pricing;

use App\Models\Customer;
use App\Models\CustomerDiscount;
use App\Models\Promotion;
use App\Models\PromotionItem;
use Carbon\Carbon;

class DiscountEngine
{
    public function calculateItemDiscount(float $price, float $discountValue, int $discountType): float
    {
        if ($discountValue <= 0) {
            return 0.0;
        }

        if ($discountType === 0) {
            $discount = $price * ($discountValue / 100);
        } elseif ($discountType === 1) {
            $discount = $discountValue;
        } else {
            $discount = 0.0;
        }

        return min($discount, $price);
    }

    public function calculateOrderDiscount(float $subtotal, float $discountValue, int $discountType): float
    {
        if ($discountValue <= 0) {
            return 0.0;
        }

        if ($discountType === 0) {
            $discount = $subtotal * ($discountValue / 100);
        } elseif ($discountType === 1) {
            $discount = $discountValue;
        } else {
            $discount = 0.0;
        }

        return min($discount, $subtotal);
    }

    public function applyCustomerDiscounts(float $price, string $customerId): float
    {
        $customer = Customer::with('discounts')->find($customerId);

        if (!$customer || !$customer->discounts) {
            return 0.0;
        }

        $totalDiscount = 0.0;

        foreach ($customer->discounts as $discount) {
            $now = Carbon::now();

            if (!$discount->is_enabled) {
                continue;
            }

            if ($discount->start_date && Carbon::parse($discount->start_date)->gt($now)) {
                continue;
            }

            if ($discount->end_date && Carbon::parse($discount->end_date)->lt($now)) {
                continue;
            }

            $discountAmount = $this->calculateItemDiscount(
                $price,
                (float) $discount->discount_value,
                (int) $discount->discount_type
            );

            $totalDiscount += $discountAmount;
        }

        return min($totalDiscount, $price);
    }

    public function applyPromotions(float $price, string $productId, string $tenantId): float
    {
        $now = Carbon::now();

        $promotions = Promotion::where('tenant_id', $tenantId)
            ->where('is_enabled', true)
            ->where(function ($query) use ($now) {
                $query->whereNull('start_date')
                    ->orWhere('start_date', '<=', $now);
            })
            ->where(function ($query) use ($now) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', $now);
            })
            ->get();

        $totalDiscount = 0.0;

        foreach ($promotions as $promotion) {
            $applicable = PromotionItem::where('promotion_id', $promotion->id)
                ->where('product_id', $productId)
                ->exists();

            if (!$applicable) {
                continue;
            }

            $discountAmount = $this->calculateItemDiscount(
                $price,
                (float) $promotion->discount_value,
                (int) $promotion->discount_type
            );

            $totalDiscount += $discountAmount;
        }

        return min($totalDiscount, $price);
    }
}
