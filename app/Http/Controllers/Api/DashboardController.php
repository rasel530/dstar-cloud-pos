<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\Stock;
use App\Models\StockControl;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(): JsonResponse
    {
        $tenantId = auth()->user()->tenant_id;
        $branchId = $request->header('X-Active-Branch');

        $todaySales = Document::where('tenant_id', $tenantId)
            ->whereDate('date', today())
            ->sum('total');

        $todayOrders = Document::where('tenant_id', $tenantId)
            ->whereDate('date', today())
            ->count();

        $lowStockCount = StockControl::where('tenant_id', $tenantId)
            ->where('is_low_stock_warning_enabled', true)
            ->count();

        $recentOrders = Document::where('tenant_id', $tenantId)
            ->with(['customer:id,name', 'user:id,first_name,last_name'])
            ->orderByDesc('date')
            ->limit(10)
            ->get()
            ->map(fn($o) => [
                'id' => $o->id,
                'customer' => $o->customer?->name ?? 'Walk-in',
                'status' => $o->paid_status ? 'completed' : 'pending',
                'total' => (float) $o->total,
                'date' => $o->date?->format('Y-m-d'),
            ]);

        $productsCount = \App\Models\Product::where('tenant_id', $tenantId)->count();

        return response()->json([
            'todays_sales' => (float) $todaySales,
            'orders_count' => $todayOrders,
            'products_count' => $productsCount,
            'low_stock_count' => $lowStockCount,
            'recent_orders' => $recentOrders,
            'revenue_chart' => [],
            'avg_order_value' => $todayOrders > 0 ? round($todaySales / $todayOrders, 2) : 0,
            'customers_count' => 1,
            'pending_orders' => 0,
            'completed_today' => $todayOrders,
        ]);
    }
}
