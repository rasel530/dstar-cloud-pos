<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CashMovement;
use App\Models\CashRegister;
use App\Models\Payment;
use App\Models\PaymentType;
use App\Services\SystemModeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CashRegisterController extends Controller
{
    /**
     * Current open register status + live summary.
     */
    public function status(Request $request): JsonResponse
    {
        $user = auth()->user();
        $register = $this->currentRegister($user->id, $user->tenant_id, $request->header('X-Active-Branch'));

        if (!$register) {
            return response()->json([
                'data' => [
                    'is_open' => false,
                    'register' => null,
                    'summary' => null,
                ],
            ]);
        }

        return response()->json([
            'data' => [
                'is_open' => true,
                'register' => $register,
                'summary' => $this->summarize($register),
            ],
        ]);
    }

    /**
     * Open the register with opening cash.
     */
    public function open(Request $request): JsonResponse
    {
        $user = auth()->user();
        $validated = $request->validate([
            'opening_cash' => 'required|numeric|min:0',
            'shift_id' => 'nullable|uuid|exists:shifts,id',
            'note' => 'nullable|string|max:255',
        ]);

        $branchId = $request->header('X-Active-Branch') ?: null;
        if ($branchId && ! $user->canAccessBranch($branchId)) {
            $branchId = null;
        }

        try {
            $register = \Illuminate\Support\Facades\DB::transaction(function () use ($user, $validated, $branchId) {
                // Atomic check-then-create prevents two open registers for the same user/branch.
                $existing = $this->currentRegister($user->id, $user->tenant_id, $branchId, true);
                if ($existing) {
                    return null;
                }

                $shiftName = null;
                if (!empty($validated['shift_id'])) {
                    $shift = \App\Models\Shift::where(function ($q) use ($user) {
                        $q->where('tenant_id', $user->tenant_id)->orWhereNull('tenant_id');
                    })->find($validated['shift_id']);
                    $shiftName = $shift?->name;
                }

                $register = CashRegister::create([
                    'tenant_id' => $user->tenant_id,
                    'branch_id' => $branchId,
                    'shift_id' => $validated['shift_id'] ?? null,
                    'shift_name' => $shiftName,
                    'user_id' => $user->id,
                    'opening_cash' => $validated['opening_cash'],
                    'status' => 'open',
                    'opened_at' => now(),
                    'last_activity_at' => now(),
                    'note' => $validated['note'] ?? null,
                ]);

                \App\Models\RegisterSession::create([
                    'tenant_id' => $user->tenant_id,
                    'register_id' => $register->id,
                    'user_id' => $user->id,
                    'started_at' => now(),
                ]);

                return $register;
            });
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Register open failed: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to open register.'], 500);
        }

        if (! $register) {
            $existing = $this->currentRegister($user->id, $user->tenant_id, $branchId);
            return response()->json([
                'message' => 'Register is already open for this branch.',
                'data' => ['register' => $existing],
            ], 422);
        }

        return response()->json([
            'message' => 'Register opened.',
            'data' => ['register' => $register],
        ], 201);
    }

    /**
     * Close the register and store the final summary.
     */
    public function close(Request $request): JsonResponse
    {
        $user = auth()->user();
        $validated = $request->validate([
            'actual_cash' => 'required|numeric|min:0',
            'note' => 'nullable|string|max:255',
        ]);

        $branchId = $request->header('X-Active-Branch') ?: null;
        $register = $this->currentRegister($user->id, $user->tenant_id, $branchId);

        if (!$register) {
            return response()->json(['message' => 'No open register found.'], 422);
        }

        $summary = $this->summarize($register);
        $expected = $summary['expected_cash'];
        $actual = round((float) $validated['actual_cash'], 4);
        $difference = round($actual - $expected, 4);

        $register->update([
            'status' => 'closed',
            'closed_at' => now(),
            'closed_by' => $user->id,
            'last_activity_at' => now(),
            'expected_cash' => $expected,
            'actual_cash' => $actual,
            'difference' => $difference,
            'note' => $validated['note'] ?? $register->note,
        ]);

        \App\Models\RegisterSession::where('register_id', $register->id)
            ->whereNull('ended_at')
            ->update([
                'ended_at' => now(),
                'ended_reason' => 'end_shift',
            ]);

        $final = $this->summarize($register);

        return response()->json([
            'message' => 'Register closed.',
            'data' => [
                'register' => $register->fresh(),
                'summary' => $final,
            ],
        ]);
    }

    /**
     * Record a cash in / cash out movement on the open register.
     */
    public function cashInOut(Request $request): JsonResponse
    {
        $user = auth()->user();
        $validated = $request->validate([
            'type' => 'required|in:in,out',
            'amount' => 'required|numeric|gt:0',
            'reason' => 'nullable|string|max:255',
        ]);

        $branchId = $request->header('X-Active-Branch') ?: null;
        $register = $this->currentRegister($user->id, $user->tenant_id, $branchId);

        if (!$register) {
            return response()->json(['message' => 'No open register found.'], 422);
        }

        $movement = CashMovement::create([
            'tenant_id' => $user->tenant_id,
            'branch_id' => $branchId,
            'cash_register_id' => $register->id,
            'user_id' => $user->id,
            'type' => $validated['type'],
            'amount' => $validated['amount'],
            'reason' => $validated['reason'] ?? null,
            'date' => now()->toDateString(),
        ]);

        $register->update(['last_activity_at' => now()]);

        return response()->json([
            'message' => 'Cash ' . ($validated['type'] === 'in' ? 'in' : 'out') . ' recorded.',
            'data' => [
                'movement' => $movement,
                'summary' => $this->summarize($register),
            ],
        ], 201);
    }

    /**
     * List closed registers (history).
     */
    public function history(Request $request): JsonResponse
    {
        $user = auth()->user();
        $perPage = (int) $request->input('per_page', 20);

        $query = CashRegister::with(['user:id,first_name,last_name,username', 'sessions.user:id,first_name,last_name'])
            ->where('tenant_id', $user->tenant_id)
            ->orderByDesc('opened_at');

        if ($branchId = $request->header('X-Active-Branch')) {
            $query->where('branch_id', $branchId);
        }

        $registers = $query->paginate($perPage);

        return response()->json([
            'data' => $registers,
        ]);
    }

    /**
     * Single register detail with movements + payments.
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $user = auth()->user();
        $register = CashRegister::with(['user:id,first_name,last_name,username', 'movements'])
            ->where('tenant_id', $user->tenant_id)
            ->findOrFail($id);

        $cashPayments = Payment::with('paymentType:id,name,code')
            ->where('cash_register_id', $register->id)
            ->get();

        return response()->json([
            'data' => [
                'register' => $register,
                'summary' => $this->summarize($register),
                'payments' => $cashPayments,
            ],
        ]);
    }

    /**
     * Find the current open register for a user/branch.
     */
    private function currentRegister(string $userId, string $tenantId, ?string $branchId, bool $forUpdate = false): ?CashRegister
    {
        $query = CashRegister::where('user_id', $userId)
            ->where('tenant_id', $tenantId)
            ->where('status', 'open');

        if ($branchId && !SystemModeService::isSingleMode()) {
            $query->where('branch_id', $branchId);
        } else {
            $query->whereNull('branch_id');
        }

        if ($forUpdate) {
            $query->lockForUpdate();
        }

        return $query->latest('opened_at')->first();
    }

    /**
     * Compute the live register summary.
     */
    private function summarize(CashRegister $register): array
    {
        $cashTypeId = PaymentType::where(function ($q) use ($register) {
            $q->where('tenant_id', $register->tenant_id)->orWhereNull('tenant_id');
        })->where(function ($q) {
            $q->where('code', 'cash')->orWhere('name', 'ilike', 'cash');
        })->orderBy('code')->value('id');

        $payments = Payment::where('cash_register_id', $register->id)
            ->when($cashTypeId, fn($q) => $q->where('payment_type_id', $cashTypeId))
            ->selectRaw('COALESCE(SUM(CASE WHEN amount > 0 THEN amount ELSE 0 END), 0) as sales,
                         COALESCE(SUM(CASE WHEN amount < 0 THEN amount ELSE 0 END), 0) as refunds')
            ->first();

        $cashSales = round((float) ($payments->sales ?? 0), 4);
        $cashRefund = round(abs((float) ($payments->refunds ?? 0)), 4);

        $cashIn = round((float) CashMovement::where('cash_register_id', $register->id)
            ->where('type', 'in')->sum('amount'), 4);
        $cashOut = round((float) CashMovement::where('cash_register_id', $register->id)
            ->where('type', 'out')->sum('amount'), 4);

        $opening = round((float) $register->opening_cash, 4);
        $expected = round($opening + $cashSales + $cashIn - $cashRefund - $cashOut, 4);

        return [
            'opening_cash' => $opening,
            'cash_sales' => $cashSales,
            'cash_in' => $cashIn,
            'cash_out' => $cashOut,
            'cash_refund' => $cashRefund,
            'expected_cash' => $expected,
            'actual_cash' => $register->actual_cash !== null ? round((float) $register->actual_cash, 4) : null,
            'difference' => $register->difference !== null ? round((float) $register->difference, 4) : null,
        ];
    }
}
