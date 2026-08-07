<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Product;
use App\Services\Reporting\ReportExportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    protected ReportExportService $exportService;

    public function __construct(ReportExportService $exportService)
    {
        $this->exportService = $exportService;
    }

    public function salesSummary(Request $request): JsonResponse
    {
        $page = (int)($request->input('page', 1));
        $perPage = (int)($request->input('per_page', 25));
        $tenantId = auth()->user()->tenant_id;

        $baseQuery = function () use ($tenantId, $request) {
            $q = \App\Models\PosOrder::with('customer')->where('tenant_id', $tenantId);
            if ($request->filled('start_date') && $request->filled('end_date')) {
                $q->whereBetween('created_at', [$request->start_date . ' 00:00:00', $request->end_date . ' 23:59:59']);
            }
            return $q;
        };

        $allOrders = $baseQuery()->get();
        $paginator = $baseQuery()->with('posOrderItems.product')->orderBy('created_at', 'desc')->paginate($perPage, ['*'], 'page', $page);

        $calc = new \App\Services\Pricing\TaxCalculator;
        $records = $paginator->map(function ($order) use ($calc) {
            $tc = $calc->calculate($order);
            return [
                'id' => $order->number,
                'date' => $order->created_at?->format('Y-m-d') ?? '—',
                'customer' => $order->customer?->name ?? 'Walk-in',
                'subtotal' => $tc['subtotal'],
                'tax' => $tc['tax'],
                'total' => $tc['total'],
            ];
        })->values()->toArray();

        $totalSales = $allOrders->sum('total');
        $totalTax = collect($records)->sum('tax');

        return response()->json([
            'data' => [
                'records' => $records,
                'total_sales' => round((float) $totalSales, 2),
                'total_orders' => $allOrders->count(),
                'total_tax' => round((float) $totalTax, 2),
                'avg_order' => $allOrders->count() > 0 ? round((float) $totalSales / $allOrders->count(), 2) : 0,
                'chart_data' => $allOrders->groupBy(fn($d) => $d->created_at?->format('M d'))
                    ->map(fn($group, $label) => ['label' => $label, 'value' => round((float) $group->sum('total'), 2)])
                    ->values()->toArray(),
                'pagination' => [
                    'current_page' => $paginator->currentPage(),
                    'last_page'    => $paginator->lastPage(),
                    'per_page'     => $paginator->perPage(),
                    'total'        => $paginator->total(),
                ],
            ]
        ]);
    }

    public function bestSellingItems(Request $request): JsonResponse
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $page = (int)($request->input('page', 1));
        $perPage = (int)($request->input('per_page', 25));

        $query = DB::table('pos_order_items')
            ->join('pos_orders', 'pos_orders.id', '=', 'pos_order_items.pos_order_id')
            ->join('products', 'products.id', '=', 'pos_order_items.product_id')
            ->where('pos_orders.tenant_id', auth()->user()->tenant_id)
            ->select(
                'products.name',
                DB::raw('SUM(pos_order_items.quantity) as quantity'),
                DB::raw('SUM(pos_order_items.quantity * pos_order_items.price) as revenue'),
                DB::raw('SUM(pos_order_items.quantity * pos_order_items.price) - SUM(COALESCE(products.cost, 0) * pos_order_items.quantity) as profit')
            )
            ->groupBy('pos_order_items.product_id', 'products.name')
            ->orderByDesc('revenue');

        if ($startDate && $endDate) {
            $query->whereBetween('pos_orders.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        }

        $items = $query->get();
        $totalCount = $items->count();
        $lastPage = max(1, (int) ceil($totalCount / $perPage));
        $pagedItems = $items->forPage($page, $perPage);

        $records = $pagedItems->map(function ($item) {
            return [
                'name' => $item->name,
                'quantity' => (int) $item->quantity,
                'revenue' => round((float) $item->revenue, 2),
                'profit' => round((float) $item->profit, 2),
            ];
        })->values()->toArray();

        $totalRevenue = $items->sum('revenue');
        $totalProfit = $items->sum('profit');

        return response()->json([
            'data' => [
                'records' => $records,
                'total_revenue' => round((float) $totalRevenue, 2),
                'total_profit' => round((float) $totalProfit, 2),
                'pagination' => [
                    'current_page' => $page,
                    'last_page'    => $lastPage,
                    'per_page'     => $perPage,
                    'total'        => $totalCount,
                ],
            ]
        ]);
    }

    public function customerAnalytics(Request $request): JsonResponse
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $page = (int)($request->input('page', 1));
        $perPage = (int)($request->input('per_page', 25));

        $query = DB::table('pos_orders')
            ->join('customers', 'customers.id', '=', 'pos_orders.customer_id')
            ->where('pos_orders.tenant_id', auth()->user()->tenant_id)
            ->whereNotNull('pos_orders.customer_id')
            ->select(
                'customers.name',
                DB::raw('COUNT(*) as order_count'),
                DB::raw('SUM(pos_orders.total) as total_spent')
            )
            ->groupBy('pos_orders.customer_id', 'customers.name')
            ->orderByDesc('total_spent');

        if ($startDate && $endDate) {
            $query->whereBetween('pos_orders.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        }

        $items = $query->get();
        $totalCount = $items->count();
        $lastPage = max(1, (int) ceil($totalCount / $perPage));
        $pagedItems = $items->forPage($page, $perPage);

        $records = $pagedItems->map(function ($item) {
            return [
                'name' => $item->name,
                'order_count' => (int) $item->order_count,
                'total_spent' => round((float) $item->total_spent, 2),
            ];
        })->values()->toArray();

        $totalSpent = $items->sum('total_spent');
        $totalOrders = $items->sum('order_count');

        return response()->json([
            'data' => [
                'records' => $records,
                'total_spent' => round((float) $totalSpent, 2),
                'total_orders' => (int) $totalOrders,
                'pagination' => [
                    'current_page' => $page,
                    'last_page'    => $lastPage,
                    'per_page'     => $perPage,
                    'total'        => $totalCount,
                ],
            ]
        ]);
    }

    public function discountReport(Request $request): JsonResponse
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $query = DB::table('pos_orders')
            ->where('tenant_id', auth()->user()->tenant_id)
            ->where('discount', '>', 0)
            ->select('created_at as date', 'number', 'discount', 'discount_type');

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        }

        $items = $query->orderBy('created_at', 'desc')->take(100)->get();

        $records = $items->map(function ($item) {
            return [
                'date' => $item->date ? date('Y-m-d', strtotime($item->date)) : '—',
                'id' => $item->number,
                'discount_type' => $item->discount_type ?: 'Discount',
                'discount_amount' => round((float) $item->discount, 2),
            ];
        })->values()->toArray();

        $totalDiscount = $items->sum('discount');

        return response()->json([
            'data' => [
                'records' => $records,
                'total_discount' => round((float) $totalDiscount, 2),
            ]
        ]);
    }

    public function taxReport(Request $request): JsonResponse
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $page = (int)($request->input('page', 1));
        $perPage = (int)($request->input('per_page', 25));

        $baseQuery = \App\Models\PosOrder::with('posOrderItems.product')
            ->where('tenant_id', auth()->user()->tenant_id)
            ->where('total', '>', 0)
            ->orderBy('created_at', 'desc');

        if ($startDate && $endDate) {
            $baseQuery->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        }

        $allOrders = (clone $baseQuery)->get();
        $calc = new \App\Services\Pricing\TaxCalculator;
        $grouped = [];

        foreach ($allOrders as $order) {
            $tc = $calc->calculate($order);
            $date = $order->created_at?->format('Y-m-d') ?? '--';
            if (!isset($grouped[$date])) {
                $grouped[$date] = ['subtotal' => 0, 'tax' => 0];
            }
            $grouped[$date]['subtotal'] += $tc['subtotal'];
            $grouped[$date]['tax'] += $tc['tax'];
        }

        $allRecords = [];
        foreach ($grouped as $date => $data) {
            $allRecords[] = [
                'date' => $date,
                'taxable_amount' => round((float) $data['subtotal'], 2),
                'tax_amount' => round((float) $data['tax'], 2),
            ];
        }

        usort($allRecords, fn($a, $b) => strcmp($b['date'], $a['date']));

        $totalCount = count($allRecords);
        $lastPage = max(1, (int) ceil($totalCount / $perPage));
        $records = array_slice($allRecords, ($page - 1) * $perPage, $perPage);
        $totalTax = round((float) array_sum(array_column($allRecords, 'tax_amount')), 2);

        $records = [];
        foreach ($grouped as $date => $data) {
            $records[] = [
                'date' => $date,
                'taxable_amount' => round((float) $data['subtotal'], 2),
                'tax_amount' => round((float) $data['tax'], 2),
            ];
        }

        usort($records, fn($a, $b) => strcmp($b['date'], $a['date']));

        $totalTax = round((float) array_sum(array_column($records, 'tax_amount')), 2);

        return response()->json([
            'data' => [
                'records' => $records,
                'total_tax' => $totalTax,
                'pagination' => [
                    'current_page' => $page,
                    'last_page'    => $lastPage,
                    'per_page'     => $perPage,
                    'total'        => $totalCount,
                ],
            ]
        ]);
    }

    public function paymentTypeBreakdown(Request $request): JsonResponse
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $query = DB::table('payments')
            ->join('payment_types', 'payment_types.id', '=', 'payments.payment_type_id')
            ->where('payments.tenant_id', auth()->user()->tenant_id)
            ->select(
                'payment_types.name',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(payments.amount) as total_amount')
            )
            ->groupBy('payment_types.id', 'payment_types.name')
            ->orderByDesc('total_amount');

        if ($startDate && $endDate) {
            $query->whereBetween('payments.date', [$startDate, $endDate]);
        }

        $items = $query->get();

        $records = $items->map(function ($item) {
            return [
                'name' => $item->name,
                'count' => (int) $item->count,
                'total_amount' => round((float) $item->total_amount, 2),
            ];
        })->values()->toArray();

        $grandTotal = $items->sum('total_amount');

        return response()->json([
            'data' => [
                'records' => $records,
                'grand_total' => round((float) $grandTotal, 2),
            ]
        ]);
    }

    public function employeeSalesReport(Request $request): JsonResponse
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $query = DB::table('pos_orders')
            ->join('users', 'users.id', '=', 'pos_orders.user_id')
            ->where('pos_orders.tenant_id', auth()->user()->tenant_id)
            ->select(
                DB::raw("CONCAT(COALESCE(users.first_name, ''), ' ', COALESCE(users.last_name, '')) as name"),
                DB::raw('COUNT(*) as order_count'),
                DB::raw('SUM(pos_orders.total) as total_sales')
            )
            ->groupBy('pos_orders.user_id', 'users.first_name', 'users.last_name')
            ->orderByDesc('total_sales');

        if ($startDate && $endDate) {
            $query->whereBetween('pos_orders.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        }

        $items = $query->get();

        $records = $items->map(function ($item) {
            return [
                'name' => trim($item->name) ?: 'Unknown',
                'order_count' => (int) $item->order_count,
                'total_sales' => round((float) $item->total_sales, 2),
            ];
        })->values()->toArray();

        $totalSales = $items->sum('total_sales');

        return response()->json([
            'data' => [
                'records' => $records,
                'total_sales' => round((float) $totalSales, 2),
            ]
        ]);
    }

    public function profitMarginReport(Request $request): JsonResponse
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $query = DB::table('pos_order_items')
            ->join('pos_orders', 'pos_orders.id', '=', 'pos_order_items.pos_order_id')
            ->join('products', 'products.id', '=', 'pos_order_items.product_id')
            ->where('pos_orders.tenant_id', auth()->user()->tenant_id)
            ->select(
                'products.name',
                DB::raw('SUM(COALESCE(products.cost, 0) * pos_order_items.quantity) as cost'),
                DB::raw('SUM(pos_order_items.quantity * pos_order_items.price) as revenue'),
                DB::raw('SUM(pos_order_items.quantity * pos_order_items.price) - SUM(COALESCE(products.cost, 0) * pos_order_items.quantity) as profit')
            )
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('profit');

        if ($startDate && $endDate) {
            $query->whereBetween('pos_orders.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        }

        $items = $query->take(100)->get();

        $records = $items->map(function ($item) {
            $profitMargin = $item->revenue > 0 ? round(((float) $item->profit / (float) $item->revenue) * 100, 2) : 0;
            return [
                'name' => $item->name,
                'cost' => round((float) $item->cost, 2),
                'revenue' => round((float) $item->revenue, 2),
                'profit_margin' => $profitMargin,
            ];
        })->values()->toArray();

        $totalRevenue = $items->sum('revenue');
        $totalProfit = $items->sum('profit');

        return response()->json([
            'data' => [
                'records' => $records,
                'total_revenue' => round((float) $totalRevenue, 2),
                'total_profit' => round((float) $totalProfit, 2),
            ]
        ]);
    }

    public function inventoryValuation(Request $request): JsonResponse
    {
        try {
            $products = \App\Models\Product::where('tenant_id', auth()->user()->tenant_id)
                ->where('is_enabled', true)
                ->get();

            $records = [];
            $totalValue = 0;
            foreach ($products as $product) {
                $stock = \App\Models\Stock::where('product_id', $product->id)->sum('quantity') ?? 0;
                $cost = (float) ($product->cost ?? 0);
                $value = $stock * $cost;
                $totalValue += $value;
                $records[] = [
                    'name' => $product->name,
                    'quantity_in_stock' => $stock,
                    'unit_cost' => round($cost, 2),
                    'total_value' => round($value, 2),
                ];
            }

            return response()->json([
                'data' => [
                    'records' => $records,
                    'total_value' => round($totalValue, 2),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['data' => ['records' => [], 'total_value' => 0]]);
        }
    }

    public function exportReport(Request $request): Response
    {
        $format = $request->query('format', 'csv');
        $type = $request->segment(3);

        $methodMap = [
            'sales-summary'     => 'salesSummary',
            'best-selling'      => 'bestSellingItems',
            'customers'         => 'customerAnalytics',
            'discounts'         => 'discountReport',
            'taxes'             => 'taxReport',
            'payments'          => 'paymentTypeBreakdown',
            'employees'         => 'employeeSalesReport',
            'profit-margin'     => 'profitMarginReport',
            'inventory-valuation' => 'inventoryValuation',
        ];

        if (!isset($methodMap[$type])) {
            return new Response(json_encode(['message' => 'Invalid report type']), 404, [
                'Content-Type' => 'application/json',
            ]);
        }

        $response = $this->{$methodMap[$type]}($request);
        $responseData = $response->getData(true);

        $data = $responseData['data'] ?? $responseData;

        $filename = $type . '-' . date('Y-m-d');

        return $this->exportService->download((array) $data, $format, $filename);
    }

    public function customerSalesDetail(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'start_date'  => 'nullable|date',
            'end_date'    => 'nullable|date',
            'page'        => 'nullable|integer|min:1',
            'per_page'    => 'nullable|integer|min:5|max:50',
        ]);

        $tenantId = auth()->user()->tenant_id;
        $customer = \App\Models\Customer::find($validated['customer_id']);
        $page = (int)($validated['page'] ?? 1);
        $perPage = (int)($validated['per_page'] ?? 10);

        $dateFilter = function ($q) use ($validated) {
            if (!empty($validated['start_date']) && !empty($validated['end_date'])) {
                $q->whereBetween('created_at', [$validated['start_date'] . ' 00:00:00', $validated['end_date'] . ' 23:59:59']);
            }
        };

        $getBaseQuery = function () use ($tenantId, $customer, $dateFilter) {
            return \App\Models\PosOrder::where('tenant_id', $tenantId)
                ->where('customer_id', $customer->id)
                ->where('status', 'closed')
                ->where($dateFilter);
        };

        $totalSpent = (float) $getBaseQuery()->sum('total');
        $orderCount = $getBaseQuery()->count();
        $allOrderIds = $getBaseQuery()->pluck('id');
        $itemCount = \App\Models\PosOrderItem::whereIn('pos_order_id', $allOrderIds)->sum('quantity');

        $paginator = $getBaseQuery()->with(['posOrderItems.product'])->orderByDesc('created_at')->paginate($perPage, ['*'], 'page', $page);

        $items = $paginator->map(function ($order) {
            return [
                'id'            => $order->id,
                'number'        => $order->number,
                'date'          => $order->created_at->format('Y-m-d H:i'),
                'item_count'    => (int) $order->posOrderItems->sum('quantity'),
                'discount'      => round((float) $order->discount, 2),
                'tax'           => round((float) $order->tax_amount, 2),
                'total'         => round((float) $order->total, 2),
                'payment'       => $order->payment_method ?? 'cash',
                'service_type'  => (int) $order->service_type,
                'table_number'  => $order->table_number ?? '',
            ];
        });

        $topProducts = \App\Models\PosOrderItem::whereIn('pos_order_id', $allOrderIds)
            ->join('products', 'pos_order_items.product_id', '=', 'products.id')
            ->selectRaw('products.name as product_name, SUM(pos_order_items.quantity) as total_qty, SUM(pos_order_items.quantity * pos_order_items.price) as total_amount')
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_amount')
            ->take(10)
            ->get()
            ->map(fn($p) => [
                'product_name' => $p->product_name,
                'total_qty'    => round((float) $p->total_qty, 2),
                'total_amount' => round((float) $p->total_amount, 2),
            ]);

        return response()->json([
            'data' => [
                'customer' => [
                    'name'  => $customer->name,
                    'email' => $customer->email ?? '',
                    'phone' => $customer->phone_number ?? ($customer->phone ?? ''),
                ],
                'summary' => [
                    'order_count'  => $orderCount,
                    'total_spent'  => round($totalSpent, 2),
                    'item_count'   => (int) $itemCount,
                    'avg_order'    => $orderCount > 0 ? round($totalSpent / $orderCount, 2) : 0,
                    'top_products' => $topProducts,
                ],
                'orders' => $items,
                'pagination' => [
                    'current_page' => $paginator->currentPage(),
                    'last_page'    => $paginator->lastPage(),
                    'per_page'     => $paginator->perPage(),
                    'total'        => $paginator->total(),
                ],
            ]
        ]);
    }
}