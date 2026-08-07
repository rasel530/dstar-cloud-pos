<?php

declare(strict_types=1);

namespace App\Services\Pricing;

use App\Models\Promotion;
use App\Models\PromotionItem;
use Carbon\Carbon;

class PromotionEngine
{
    public function getActivePromotions(string $tenantId): array
    {
        $now = Carbon::now();

        return Promotion::with('promotionItems.product')
            ->where('tenant_id', $tenantId)
            ->where('is_enabled', true)
            ->where(function ($query) use ($now) {
                $query->whereNull('start_date')
                    ->orWhere('start_date', '<=', $now);
            })
            ->where(function ($query) use ($now) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', $now);
            })
            ->get()
            ->toArray();
    }

    public function isPromotionApplicable(array $promotion, string $productId, float $quantity): bool
    {
        if (empty($promotion['promotion_items'])) {
            return false;
        }

        foreach ($promotion['promotion_items'] as $pp) {
            if ($pp['product_id'] === $productId) {
                $minQty = (float) ($pp['min_quantity'] ?? 0);

                if ($quantity >= $minQty) {
                    return true;
                }
            }
        }

        return false;
    }

    public function calculatePromotionDiscount(array $promotion, float $price, float $quantity): float
    {
        $discountType = (int) ($promotion['discount_type'] ?? 0);
        $discountValue = (float) ($promotion['discount_value'] ?? 0);

        if ($discountValue <= 0) {
            return 0.0;
        }

        $baseAmount = $price * $quantity;

        if ($discountType === 0) {
            return $baseAmount * ($discountValue / 100);
        }

        if ($discountType === 1) {
            return $discountValue * $quantity;
        }

        return 0.0;
    }
}
