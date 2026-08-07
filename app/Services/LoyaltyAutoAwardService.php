<?php

namespace App\Services;

use App\Models\LoyaltyCard;
use App\Models\LoyaltyTransaction;
use App\Models\PosOrder;

class LoyaltyAutoAwardService
{
    /**
     * Auto-award loyalty points when an order is completed.
     * Rules:
     *  - Only awards if order has a customer assigned
     *  - Only awards if customer has a loyalty card
     *  - Only awards ONCE (checks loyalty_points_earned = 0)
     *  - Only awards when order status transitions to 'closed'
     *  - 1 point per $1 spent (configurable)
     */
    public function awardForOrder(PosOrder $order): ?LoyaltyTransaction
    {
        if (! $order->customer_id) return null;
        if ($order->loyalty_points_earned > 0) return null;
        if ($order->status !== 'closed') return null;

        $card = LoyaltyCard::where('customer_id', $order->customer_id)->first();
        if (! $card) return null;

        $points = $this->calculatePoints($order->total);

        if ($points <= 0) return null;

        $card->points_balance += $points;
        $card->total_points_earned += $points;
        $card->save();

        $order->loyalty_points_earned = $points;
        $order->save();

        return LoyaltyTransaction::create([
            'loyalty_card_id' => $card->id,
            'transaction_type' => 'earn',
            'points' => $points,
        ]);
    }

    /**
     * 1 point per full dollar spent. Minimum 1 point per order.
     */
    public function calculatePoints(float $total): int
    {
        return max(1, (int) floor($total));
    }
}
