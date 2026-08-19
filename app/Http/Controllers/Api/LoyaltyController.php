<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\LoyaltyCard;
use App\Models\LoyaltyTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LoyaltyController extends Controller
{
    public function index(): JsonResponse
    {
        $cards = LoyaltyCard::with('customer')
            ->where('tenant_id', auth()->user()->tenant_id)
            ->orderBy('created_at', 'desc')
            ->paginate(25);

        return response()->json(['data' => $cards]);
    }

    public function show($id): JsonResponse
    {
        $card = LoyaltyCard::with(['customer', 'transactions' => fn($q) => $q->latest()->limit(50)])
            ->where('tenant_id', auth()->user()->tenant_id)
            ->findOrFail($id);

        return response()->json(['data' => $card]);
    }

    public function store(Request $request): JsonResponse
    {
        $tenantId = auth()->user()->tenant_id;

        $validated = $request->validate([
            'customer_id' => "required|exists:customers,id,tenant_id,$tenantId",
            'card_number' => 'nullable|string|max:255',
        ]);

        $exists = LoyaltyCard::where('tenant_id', $tenantId)->where('customer_id', $validated['customer_id'])->first();
        if ($exists) {
            return response()->json(['message' => 'Customer already has a loyalty card'], 422);
        }

        $card = LoyaltyCard::create([
            'tenant_id' => $tenantId,
            'customer_id' => $validated['customer_id'],
            'card_number' => $validated['card_number'] ?? 'LC-' . str_pad(LoyaltyCard::where('tenant_id', $tenantId)->count() + 1, 4, '0', STR_PAD_LEFT),
            'points_balance' => 0,
            'total_points_earned' => 0,
        ]);

        $card->load('customer');

        return response()->json(['data' => $card], 201);
    }

    public function earnPoints(Request $request, $cardId): JsonResponse
    {
        $validated = $request->validate([
            'points' => 'required|integer|min:1',
        ]);

        $card = LoyaltyCard::where('id', $cardId)
            ->where('tenant_id', auth()->user()->tenant_id)
            ->firstOrFail();

        $card->points_balance += $validated['points'];
        $card->total_points_earned += $validated['points'];
        $card->save();

        LoyaltyTransaction::create([
            'loyalty_card_id' => $card->id,
            'transaction_type' => 'earn',
            'points' => $validated['points'],
        ]);

        return response()->json(['data' => $card]);
    }

    public function redeemPoints(Request $request, $cardId): JsonResponse
    {
        $validated = $request->validate([
            'points' => 'required|integer|min:1',
        ]);

        $card = LoyaltyCard::where('id', $cardId)
            ->where('tenant_id', auth()->user()->tenant_id)
            ->firstOrFail();

        if ($card->points_balance < $validated['points']) {
            return response()->json(['message' => 'Insufficient points'], 422);
        }

        $card->points_balance -= $validated['points'];
        $card->save();

        LoyaltyTransaction::create([
            'loyalty_card_id' => $card->id,
            'transaction_type' => 'redeem',
            'points' => $validated['points'],
        ]);

        return response()->json(['data' => $card]);
    }

    public function transactionHistory($cardId): JsonResponse
    {
        $card = LoyaltyCard::where('id', $cardId)
            ->where('tenant_id', auth()->user()->tenant_id)
            ->firstOrFail();

        $transactions = LoyaltyTransaction::where('loyalty_card_id', $card->id)
            ->orderByDesc('created_at')
            ->paginate(25);

        return response()->json(['data' => $transactions]);
    }

    public function destroy($id): JsonResponse
    {
        $card = LoyaltyCard::where('id', $id)
            ->where('tenant_id', auth()->user()->tenant_id)
            ->firstOrFail();

        $card->delete();

        return response()->json(null, 204);
    }
}
