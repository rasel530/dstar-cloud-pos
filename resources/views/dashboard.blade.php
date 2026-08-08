@extends('layouts.app')

@section('title', 'Dashboard')

@push('styles')
<style>
    [x-cloak] { display: none !important; }
    .chart-bar {
        transition: height 0.6s cubic-bezier(0.4, 0, 0.2, 1);
    }
</style>
@endpush

@section('content')
<div x-data="dashboard" x-init="init()" class="flex flex-col h-full">

    {{-- Welcome Header --}}
    <div class="py-4">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Welcome back</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Here's what's happening with your store today.</p>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Today's Sales --}}
        <div class="bg-white dark:bg-[#1a1f3d] rounded-xl shadow-sm border border-gray-200 dark:border-white/10 p-5">
            <div class="flex items-center justify-between mb-3">
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Today's Sales</span>
                <div class="h-10 w-10 rounded-lg bg-emerald-100 dark:bg-emerald-500/20 flex items-center justify-center">
                    <svg class="h-5 w-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-gray-900 dark:text-white" x-text="formatCurrency(stats.todaySales)"></p>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1" x-text="'vs ' + formatCurrency(stats.yesterdaySales) + ' yesterday'"></p>
        </div>

        {{-- Number of Orders --}}
        <div class="bg-white dark:bg-[#1a1f3d] rounded-xl shadow-sm border border-gray-200 dark:border-white/10 p-5">
            <div class="flex items-center justify-between mb-3">
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Orders</span>
                <div class="h-10 w-10 rounded-lg bg-blue-100 dark:bg-blue-500/20 flex items-center justify-center">
                    <svg class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-gray-900 dark:text-white" x-text="stats.todayOrders"></p>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1" x-text="stats.pendingOrders + ' pending'"></p>
        </div>

        {{-- Products Count --}}
        <div class="bg-white dark:bg-[#1a1f3d] rounded-xl shadow-sm border border-gray-200 dark:border-white/10 p-5">
            <div class="flex items-center justify-between mb-3">
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Products</span>
                <div class="h-10 w-10 rounded-lg bg-purple-100 dark:bg-purple-500/20 flex items-center justify-center">
                    <svg class="h-5 w-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-gray-900 dark:text-white" x-text="stats.totalProducts"></p>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1" x-text="stats.activeProducts + ' active'"></p>
        </div>

        {{-- Low Stock --}}
        <div class="bg-white dark:bg-[#1a1f3d] rounded-xl shadow-sm border border-gray-200 dark:border-white/10 p-5">
            <div class="flex items-center justify-between mb-3">
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Low Stock</span>
                <div class="h-10 w-10 rounded-lg bg-amber-100 dark:bg-amber-500/20 flex items-center justify-center">
                    <svg class="h-5 w-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.618 5.984A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016zM12 9v2m0 4h.01"/>
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-gray-900 dark:text-white" x-text="stats.lowStockItems"></p>
            <p class="text-xs text-red-500 dark:text-red-400 mt-1" x-text="'Needs restock'"></p>
        </div>
    </div>

    {{-- Revenue Chart + Quick Stats --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        {{-- Revenue Chart --}}
        <div class="lg:col-span-2 bg-white dark:bg-[#1a1f3d] rounded-xl shadow-sm border border-gray-200 dark:border-white/10 p-5">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-base font-semibold text-gray-900 dark:text-white">Revenue (Last 7 Days)</h2>
                <span class="text-sm text-gray-500 dark:text-gray-400" x-text="formatCurrency(stats.weekRevenue) + ' total'"></span>
            </div>

            {{-- Bar chart --}}
            <div x-show="!chartLoading" class="flex items-end justify-between gap-2 h-52 px-2" x-ref="chartContainer">
                <template x-for="(day, index) in chartData" :key="index">
                    <div class="flex-1 flex flex-col items-center gap-1.5 min-w-0">
                        <span class="text-xs font-medium text-gray-600 dark:text-gray-300" x-text="formatCurrency(day.revenue)"></span>
                        <div
                            class="chart-bar w-full rounded-t-md"
                            :style="'height: ' + barHeight(day.revenue) + 'px'"
                            :class="index === chartData.length - 1
                                ? 'bg-blue-500 dark:bg-blue-400'
                                : 'bg-blue-200 dark:bg-blue-500/30'"
                        ></div>
                        <span class="text-xs text-gray-400 dark:text-gray-500 pt-1" x-text="day.label"></span>
                    </div>
                </template>
            </div>

            <div x-show="chartLoading" class="h-52 flex items-center justify-center">
                <svg class="animate-spin h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
            </div>
        </div>

        {{-- Quick Stats Sidebar --}}
        <div class="bg-white dark:bg-[#1a1f3d] rounded-xl shadow-sm border border-gray-200 dark:border-white/10 p-5">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Quick Stats</h2>
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-500 dark:text-gray-400">Avg Order Value</span>
                    <span class="text-sm font-semibold text-gray-900 dark:text-white" x-text="formatCurrency(stats.avgOrderValue)"></span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-500 dark:text-gray-400">Top Seller</span>
                    <span class="text-sm font-semibold text-gray-900 dark:text-white truncate max-w-[120px]" x-text="stats.topSeller || '--'"></span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-500 dark:text-gray-400">Best Category</span>
                    <span class="text-sm font-semibold text-gray-900 dark:text-white truncate max-w-[120px]" x-text="stats.bestCategory || '--'"></span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-500 dark:text-gray-400">Customers Today</span>
                    <span class="text-sm font-semibold text-gray-900 dark:text-white" x-text="stats.customersToday"></span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-500 dark:text-gray-400">Refunds Today</span>
                    <span class="text-sm font-semibold text-red-600 dark:text-red-400" x-text="stats.refundsToday + ' (' + $store.currency.symbol + (stats.refundAmount || 0).toFixed(2) + ')'"></span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-500 dark:text-gray-400">Voided Orders</span>
                    <span class="text-sm font-semibold text-gray-900 dark:text-white" x-text="stats.voidedOrders"></span>
                </div>
            </div>

            {{-- Divider --}}
            <hr class="my-4 border-gray-200 dark:border-white/10">

            {{-- Profit margin --}}
            <div>
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm text-gray-500 dark:text-gray-400">Profit Margin</span>
                    <span class="text-sm font-semibold text-emerald-600 dark:text-emerald-400" x-text="(stats.profitMargin || 0).toFixed(1) + '%'"></span>
                </div>
                <div class="w-full bg-gray-200 dark:bg-[#0f1535] rounded-full h-2.5">
                    <div
                        class="bg-emerald-500 dark:bg-emerald-400 h-2.5 rounded-full transition-all duration-700"
                        :style="'width: ' + Math.min((stats.profitMargin || 0), 100) + '%'"
                    ></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Recent Orders Table --}}
    <div class="bg-white dark:bg-[#1a1f3d] rounded-xl shadow-sm border border-gray-200 dark:border-white/10 overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-white/10">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white">Recent Orders</h2>
            <a href="/orders" class="text-sm font-medium text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 transition-colors">View All</a>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-white/5">
                <thead class="bg-gray-50 dark:bg-white/5">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Order #</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Customer</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Amount</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Items</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Time</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-white/5">
                    <template x-if="recentOrdersLoading">
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-400 dark:text-gray-500">
                                <div class="flex items-center justify-center gap-2">
                                    <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                    </svg>
                                    Loading orders...
                                </div>
                            </td>
                        </tr>
                    </template>

                    <template x-if="!recentOrdersLoading && recentOrders.length === 0">
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-sm text-gray-500 dark:text-gray-400">
                                <svg class="mx-auto h-10 w-10 text-gray-300 dark:text-gray-600 mb-2" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                                No recent orders
                            </td>
                        </tr>
                    </template>

                    <template x-for="order in recentOrders" :key="order.id">
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/5/50 transition-colors">
                            <td class="px-6 py-3.5 whitespace-nowrap">
                                <span class="text-sm font-mono font-medium text-blue-600 dark:text-blue-400" x-text="'#' + order.order_number"></span>
                            </td>
                            <td class="px-6 py-3.5 whitespace-nowrap">
                                <span class="text-sm text-gray-700 dark:text-gray-300" x-text="order.customer_name || 'Walk-in'"></span>
                            </td>
                            <td class="px-6 py-3.5 whitespace-nowrap text-right">
                                <span class="text-sm font-medium text-gray-900 dark:text-white" x-text="formatCurrency(order.total_amount)"></span>
                            </td>
                            <td class="px-6 py-3.5 whitespace-nowrap text-right">
                                <span class="text-sm text-gray-600 dark:text-gray-400" x-text="order.items_count + ' item' + (order.items_count !== 1 ? 's' : '')"></span>
                            </td>
                            <td class="px-6 py-3.5 whitespace-nowrap text-center">
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                    :class="statusBadge(order.status)"
                                    x-text="order.status"
                                ></span>
                            </td>
                            <td class="px-6 py-3.5 whitespace-nowrap text-right">
                                <span class="text-xs text-gray-500 dark:text-gray-400" x-text="timeAgo(order.created_at)"></span>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('dashboard', () => ({
        stats: {
            todaySales: 0,
            yesterdaySales: 0,
            todayOrders: 0,
            pendingOrders: 0,
            totalProducts: 0,
            activeProducts: 0,
            lowStockItems: 0,
            weekRevenue: 0,
            avgOrderValue: 0,
            topSeller: '',
            bestCategory: '',
            customersToday: 0,
            refundsToday: 0,
            refundAmount: 0,
            voidedOrders: 0,
            profitMargin: 0,
        },
        chartData: [],
        chartLoading: true,
        recentOrders: [],
        recentOrdersLoading: true,

        init() {
            this.fetchDashboard();
            this.fetchRecentOrders();
        },

        async fetchDashboard() {
            try {
                const res = await fetch('/api/dashboard');
                const data = await res.json();

                if (data.stats) {
                    this.stats = { ...this.stats, ...data.stats };
                } else {
                    this.stats = { ...this.stats, ...data };
                }

                if (data.chartData) {
                    this.chartData = data.chartData;
                } else if (data.revenue_chart) {
                    this.chartData = data.revenue_chart;
                }
            } catch (e) {
                console.error('Failed to fetch dashboard data:', e);
            } finally {
                this.chartLoading = false;
            }
        },

        async fetchRecentOrders() {
            this.recentOrdersLoading = true;
            try {
                const res = await fetch('/api/orders?per_page=5&sort=created_at&direction=desc');
                const data = await res.json();
                this.recentOrders = data.data || [];
            } catch (e) {
                console.error('Failed to fetch recent orders:', e);
            } finally {
                this.recentOrdersLoading = false;
            }
        },

        formatCurrency(amount) {
            const num = parseFloat(amount) || 0;
            return (Alpine.store('currency')?.symbol || '$') + num.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        },

        barHeight(revenue) {
            const max = Math.max(...this.chartData.map(d => d.revenue || 0), 1);
            const ratio = (revenue || 0) / max;
            return Math.max(ratio * 160, 4);
        },

        statusBadge(status) {
            const s = (status || '').toLowerCase();
            if (s === 'completed' || s === 'paid') {
                return 'bg-emerald-100 dark:bg-emerald-500/20 text-emerald-700 dark:text-emerald-400';
            }
            if (s === 'pending' || s === 'processing') {
                return 'bg-amber-100 dark:bg-amber-500/20 text-amber-700 dark:text-amber-400';
            }
            if (s === 'cancelled' || s === 'voided') {
                return 'bg-red-100 dark:bg-red-500/20 text-red-700 dark:text-red-400';
            }
            return 'bg-gray-100 dark:bg-[#0f1535] text-gray-600 dark:text-gray-400';
        },

        timeAgo(dateStr) {
            if (!dateStr) return '';
            const now = new Date();
            const date = new Date(dateStr);
            const diffMs = now - date;
            const diffMins = Math.floor(diffMs / 60000);

            if (diffMins < 1) return 'Just now';
            if (diffMins < 60) return diffMins + 'm ago';

            const diffHrs = Math.floor(diffMins / 60);
            if (diffHrs < 24) return diffHrs + 'h ago';

            const diffDays = Math.floor(diffHrs / 24);
            if (diffDays < 7) return diffDays + 'd ago';

            return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
        },
    }));
});
</script>
@endpush
