<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\PosOrder;
use App\Models\PosOrderItem;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $tenantId = auth()->user()->tenant_id;
        $branchId = $request->header('X-Active-Branch');
        $today = today()->toDateString();
        $yesterday = now()->subDay()->toDateString();
        $weekStart = now()->subDays(6)->toDateString();

        $docQuery = function () use ($tenantId, $branchId) {
            $q = Document::where('tenant_id', $tenantId);
            if ($branchId) $q->where('warehouse_id', $branchId);
            return $q;
        };

        $todaySales = round((float) $docQuery()->whereDate('date', $today)->sum('total'), 2);
        $yesterdaySales = round((float) $docQuery()->whereDate('date', $yesterday)->sum('total'), 2);
        // Completed orders = sale documents today (excludes refund documents, which have negative totals)
        $todayOrders = $docQuery()->whereDate('date', $today)->where('total', '>=', 0)->count();
        $pendingOrders = PosOrder::where('tenant_id', $tenantId)->whereIn('status', ['open', 'held'])->count();
        // Products visible in the catalog (own + shared global products), matching the Products page / POS
        $totalProducts = Product::where(fn($q) => $q->where('tenant_id', $tenantId)->orWhere('is_global', true))->count();
        $activeProducts = Product::where(fn($q) => $q->where('tenant_id', $tenantId)->orWhere('is_global', true))->where('is_enabled', true)->count();
        $ownProducts = Product::where('tenant_id', $tenantId)->count();
        $sharedProducts = $totalProducts - $ownProducts;

        // Low stock mirrors the POS frontend badge logic exactly:
        //   - "Low Stock" = trackable product with 0 < stock <= 10 (the amber "Low:" badge)
        //   - "Out of Stock" = trackable product with no stock row or stock <= 0
        // Uses the active branch inventory (multi-branch) or the default warehouse
        // stock (single mode / no active branch) — same source as the POS grid.
        $lowStockThreshold = 10;
        $systemMode = \App\Services\SystemModeService::isSingleMode();
        $activeBranchId = $systemMode ? null : $branchId;
        $defaultWarehouseId = \App\Models\Warehouse::where('is_default', true)->value('id');

        $productBase = Product::where(fn($q) => $q->where('tenant_id', $tenantId)->orWhere('is_global', true))
            ->where('track_inventory', true);

        // Out of stock: no stock row for the active location, or stock <= 0
        $outOfStockQuery = function () use ($productBase, $activeBranchId, $defaultWarehouseId) {
            $q = (clone $productBase);
            return $q->where(function ($q) use ($activeBranchId, $defaultWarehouseId) {
                if ($activeBranchId) {
                    $q->whereDoesntHave('branchInventories', fn($b) => $b->where('branch_id', $activeBranchId))
                      ->orWhereHas('branchInventories', fn($b) => $b->where('branch_id', $activeBranchId)->where('stock', '<=', 0));
                } else {
                    $q->whereDoesntHave('stocks', fn($s) => $s->where('warehouse_id', $defaultWarehouseId))
                      ->orWhereHas('stocks', fn($s) => $s->where('warehouse_id', $defaultWarehouseId)->where('quantity', '<=', 0));
                }
            });
        };

        // Low stock: has a stock row with 0 < stock <= threshold (still available, running low)
        $lowStockQuery = function (int $threshold) use ($productBase, $activeBranchId, $defaultWarehouseId) {
            $q = (clone $productBase);
            return $q->where(function ($q) use ($threshold, $activeBranchId, $defaultWarehouseId) {
                if ($activeBranchId) {
                    $q->whereHas('branchInventories', fn($b) => $b->where('branch_id', $activeBranchId)->where('stock', '>', 0)->where('stock', '<=', $threshold));
                } else {
                    $q->whereHas('stocks', fn($s) => $s->where('warehouse_id', $defaultWarehouseId)->where('quantity', '>', 0)->where('quantity', '<=', $threshold));
                }
            });
        };

        $lowStockCount = $lowStockQuery($lowStockThreshold)->count();
        $outOfStockCount = $outOfStockQuery()->count();

        $weekRevenue = round((float) $docQuery()->whereBetween('date', [$weekStart, $today])->sum('total'), 2);
        $avgOrderValue = $todayOrders > 0 ? round($todaySales / $todayOrders, 2) : 0;
        $customersToday = $docQuery()->whereDate('date', $today)->whereNotNull('customer_id')->distinct('customer_id')->count('customer_id');

        // Top seller & best category (today's closed orders, by quantity sold)
        $itemBase = function () use ($tenantId, $today) {
            return PosOrderItem::join('pos_orders', 'pos_orders.id', '=', 'pos_order_items.pos_order_id')
                ->join('products', 'products.id', '=', 'pos_order_items.product_id')
                ->where('pos_orders.tenant_id', $tenantId)
                ->whereDate('pos_orders.created_at', $today)
                ->where('pos_orders.status', 'closed');
        };
        $topSeller = $itemBase()->selectRaw('products.name, SUM(pos_order_items.quantity) as qty')
            ->groupBy('products.id', 'products.name')->orderByDesc('qty')->value('name');
        $bestCategory = $itemBase()->join('product_groups', 'product_groups.id', '=', 'products.product_group_id')
            ->selectRaw('product_groups.name, SUM(pos_order_items.quantity) as qty')
            ->groupBy('product_groups.id', 'product_groups.name')->orderByDesc('qty')->value('name');

        // Refunds (negative-total refund documents created today)
        $refundsToday = $docQuery()->whereDate('date', $today)->where('total', '<', 0)->count();
        $refundAmount = round(abs((float) $docQuery()->whereDate('date', $today)->where('total', '<', 0)->sum('total')), 2);

        // Voided (cancelled) orders today
        $voidedOrders = PosOrder::where('tenant_id', $tenantId)->whereDate('created_at', $today)->where('status', 'cancelled')->count();

        // Profit margin across all closed orders (accrual, uses sale-time cost)
        $profitData = PosOrderItem::join('pos_orders', 'pos_orders.id', '=', 'pos_order_items.pos_order_id')
            ->where('pos_orders.tenant_id', $tenantId)
            ->where('pos_orders.status', 'closed')
            ->selectRaw('COALESCE(SUM(pos_order_items.price * pos_order_items.quantity),0) as rev, COALESCE(SUM(COALESCE(pos_order_items.cost,0) * pos_order_items.quantity),0) as cogs')
            ->first();
        $profitMargin = $profitData && $profitData->rev > 0 ? round((($profitData->rev - $profitData->cogs) / $profitData->rev) * 100, 1) : 0;

        $revenueChart = $docQuery()->whereBetween('date', [$weekStart, $today])
            ->selectRaw('date, SUM(total) as value')
            ->groupBy('date')->orderBy('date')->get()
            ->map(fn($r) => [
                'label' => $r->date instanceof \Carbon\CarbonInterface ? $r->date->format('Y-m-d') : (string) $r->date,
                'value' => round((float) $r->value, 2),
            ])
            ->values();

        $recentOrders = $docQuery()->with(['customer:id,name', 'user:id,first_name,last_name'])
            ->orderByDesc('date')->limit(10)->get()
            ->map(fn($o) => [
                'id' => $o->id,
                'number' => $o->number,
                'customer' => $o->customer?->name ?? 'Walk-in',
                'status' => $o->paid_status ? 'completed' : 'pending',
                'total' => (float) $o->total,
                'date' => $o->date?->format('Y-m-d'),
            ]);

        return response()->json([
            'todays_sales' => $todaySales,
            'yesterday_sales' => $yesterdaySales,
            'orders_count' => $todayOrders,
            'pending_orders' => $pendingOrders,
            'products_count' => $totalProducts,
            'active_products' => $activeProducts,
            'own_products_count' => $ownProducts,
            'shared_products_count' => $sharedProducts,
            'low_stock_count' => $lowStockCount,
            'out_of_stock_count' => $outOfStockCount,
            'week_revenue' => $weekRevenue,
            'avg_order_value' => $avgOrderValue,
            'customers_count' => $customersToday,
            'top_seller' => $topSeller ?? '',
            'best_category' => $bestCategory ?? '',
            'refunds_today' => $refundsToday,
            'refund_amount' => $refundAmount,
            'voided_orders' => $voidedOrders,
            'profit_margin' => $profitMargin,
            'completed_today' => $todayOrders,
            'recent_orders' => $recentOrders,
            'revenue_chart' => $revenueChart,
        ]);
    }
}
