<?php

namespace App\Services\Pricing;

use App\Models\PosOrder;

class TaxCalculator
{
    /**
     * Calculate total tax for an order using per-product PLU fiscal VAT.
     * Falls back to global tax rate for products without PLU.
     */
    public function calculate(PosOrder $order): array
    {
        $items = $order->posOrderItems()->with('product')->get();
        $calculatedTotal = $items->sum(fn($i) => $i->quantity * $i->price);
        $discountAmount = (float) ($order->discount ?? 0);

        $taxConfig = \App\Models\Tax::where('tenant_id', $order->tenant_id)
            ->where('is_enabled', true)->first();
        $globalRate = $taxConfig?->rate ?? 0;
        $isFixed = (bool) ($taxConfig?->is_fixed ?? false);

        $totalTax = 0;

        if ($isFixed) {
            $totalTax = round((float) $globalRate, 2);
        } else {
            foreach ($items as $item) {
                $productPlu = $item->product?->plu;
                $fiscalVat = $productPlu ? \App\Models\FiscalItem::find($productPlu)?->vat : null;
                $rate = $fiscalVat
                    ? floatval(preg_replace('/[^0-9.]/', '', (string) $fiscalVat))
                    : (float) $globalRate;

                // If the product's fiscal VAT is 0/invalid, apply the global tax rate instead.
                if ($rate <= 0) $rate = (float) $globalRate;
                if ($rate <= 0) continue;

                $itemTotal = $item->quantity * $item->price;
                $itemDiscountShare = $calculatedTotal > 0
                    ? $discountAmount * ($itemTotal / $calculatedTotal)
                    : 0;
                $itemSubtotal = max(0, $itemTotal - $itemDiscountShare);
                $totalTax += round($itemSubtotal * ($rate / 100), 2);
            }
        }

        return [
            'tax' => round($totalTax, 2),
            'subtotal' => round($calculatedTotal - $discountAmount, 2),
            'total' => round($calculatedTotal - $discountAmount + $totalTax, 2),
            'discount' => $discountAmount,
        ];
    }
}
