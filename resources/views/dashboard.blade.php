@extends('layouts.app')

@section('title', 'Dashboard')

@push('styles')
<style>
    [x-cloak] { display: none !important; }

    .chart-bar {
        transition: height 0.7s cubic-bezier(0.4, 0, 0.2, 1);
    }

    @keyframes fade-in-up {
        from { opacity: 0; transform: translateY(10px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .fade-in-up {
        animation: fade-in-up 0.45s ease-out both;
    }

    .stat-card:hover .stat-icon {
        transform: scale(1.08) rotate(-4deg);
    }
    .stat-icon {
        transition: transform 0.25s ease;
    }

    .chart-gridlines {
        background-image: repeating-linear-gradient(
            to bottom,
            transparent 0,
            transparent calc(25% - 1px),
            rgba(148, 163, 184, 0.18) calc(25% - 1px),
            rgba(148, 163, 184, 0.18) 25%
        );
    }
    .dark .chart-gridlines {
        background-image: repeating-linear-gradient(
            to bottom,
            transparent 0,
            transparent calc(25% - 1px),
            rgba(255, 255, 255, 0.07) calc(25% - 1px),
            rgba(255, 255, 255, 0.07) 25%
        );
    }
</style>
@endpush

@section('content')
<div x-data="dashboard" class="flex flex-col h-full">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 px-4 sm:px-6 py-3 sm:py-4 shrink-0">
        <div class="min-w-0">
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2.5">
                <span x-text="greeting() + (firstName ? ', ' + firstName : '')"></span>
            </h1>
            <p class="text-gray-500 dark:text-gray-400 text-sm mt-1 flex flex-wrap items-center gap-x-2 gap-y-1">
                <span x-text="todayLabel"></span>
                <span class="hidden sm:inline text-gray-300 dark:text-white/15">•</span>
                <span class="hidden sm:inline">Here's what's happening with your store today.</span>
            </p>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            <button
                @click="refresh()"
                :disabled="refreshing"
                class="inline-flex items-center gap-2 bg-white dark:bg-[#1a1f3d] hover:bg-gray-50 dark:hover:bg-[#232a52] text-gray-700 dark:text-gray-200 text-sm font-medium px-4 py-2 rounded-lg border border-gray-200 dark:border-white/10 transition disabled:opacity-60"
            >
                <svg class="w-4 h-4" :class="refreshing ? 'animate-spin' : ''" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/>
                </svg>
                <span x-text="refreshing ? 'Refreshing...' : 'Refresh'"></span>
            </button>
            <a href="{{ route('pos.index') }}" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition shadow-sm shadow-blue-600/20">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/>
                </svg>
                <span class="hidden sm:inline">Open POS</span>
                <span class="sm:hidden">POS</span>
            </a>
        </div>
    </div>

    <div class="px-4 sm:px-6 pb-4 flex flex-col gap-4 flex-1">

        {{-- Low stock alert --}}
        <div x-show="stats.lowStockItems > 0 && !statsLoading" x-cloak class="fade-in-up flex flex-col sm:flex-row sm:items-center justify-between gap-3 rounded-xl border border-amber-200 dark:border-amber-500/30 bg-amber-50 dark:bg-amber-500/10 px-4 sm:px-5 py-3.5">
            <div class="flex items-center gap-3 min-w-0">
                <div class="h-9 w-9 rounded-lg bg-amber-100 dark:bg-amber-500/20 flex items-center justify-center shrink-0">
                    <svg class="h-5 w-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-amber-800 dark:text-amber-300">
                        <span x-text="stats.lowStockItems"></span> product<span x-text="stats.lowStockItems !== 1 ? 's' : ''"></span> low on stock
                    </p>
                    <p class="text-xs text-amber-700 dark:text-amber-400/80 mt-0.5 truncate">
                        <span x-text="stats.outOfStockItems + ' out of stock'"></span> · Restock before they run out.
                    </p>
                </div>
            </div>
            <a href="{{ route('web.warehouses.index') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-amber-800 dark:text-amber-300 hover:text-amber-900 dark:hover:text-amber-200 transition shrink-0">
                View inventory
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
            </a>
        </div>

        {{-- Summary Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
            <template x-if="statsLoading">
                <template x-for="i in 4" :key="i">
                    <div class="h-[132px] rounded-2xl bg-gray-100 dark:bg-white/5 animate-pulse"></div>
                </template>
            </template>

            <template x-if="!statsLoading">
                <template x-for="card in statCards" :key="card.key">
                    <div class="fade-in-up stat-card relative bg-white dark:bg-[#1a1f3d] rounded-2xl border border-gray-100 dark:border-white/10 p-5 overflow-hidden hover:shadow-md hover:border-gray-200 dark:hover:border-white/20 transition-all" :style="'animation-delay: ' + card.delay + 'ms'">
                        <div class="absolute -top-8 -right-8 w-28 h-28 rounded-full opacity-[0.07]" :class="card.blob" style="filter: blur(2px);"></div>
                        <div class="relative flex items-start justify-between">
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400" x-text="card.label"></p>
                                <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white tabular-nums truncate" x-text="card.display"></p>
                            </div>
                            <div class="stat-icon h-11 w-11 rounded-xl flex items-center justify-center shrink-0" :class="card.iconBg">
                                <svg class="h-5.5 w-5.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" :d="card.iconPath"/>
                                </svg>
                            </div>
                        </div>
                        <div class="relative mt-3 flex items-center gap-2 text-xs">
                            <template x-if="card.trend">
                                <span
                                    class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded-md font-semibold"
                                    :class="card.trend.up
                                        ? 'bg-emerald-50 dark:bg-emerald-500/15 text-emerald-600 dark:text-emerald-400'
                                        : 'bg-red-50 dark:bg-red-500/15 text-red-600 dark:text-red-400'"
                                >
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                        <path x-show="card.trend.up" stroke-linecap="round" stroke-linejoin="round" d="M4.5 19.5l15-15m0 0H8.25m11.25 0v11.25"/>
                                        <path x-show="!card.trend.up" stroke-linecap="round" stroke-linejoin="round" d="M19.5 4.5l-15 15m0 0h11.25m-11.25 0V8.25"/>
                                    </svg>
                                    <span x-text="card.trend.pct + '%'"></span>
                                </span>
                            </template>
                            <span class="text-gray-500 dark:text-gray-400 truncate" x-text="card.footer"></span>
                        </div>
                    </div>
                </template>
            </template>
        </div>

        {{-- Revenue Chart + Quick Stats --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            {{-- Revenue Chart --}}
            <div class="lg:col-span-2 fade-in-up bg-white dark:bg-[#1a1f3d] rounded-2xl border border-gray-100 dark:border-white/10 p-4 sm:p-5" style="animation-delay: 120ms">
                <div class="flex flex-wrap items-center justify-between gap-2 mb-4">
                    <div>
                        <h2 class="text-base font-semibold text-gray-900 dark:text-white">Revenue</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Last 7 days performance</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-blue-700 dark:text-blue-300 bg-blue-50 dark:bg-blue-500/15 px-2.5 py-1 rounded-lg">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span x-text="formatCurrency(stats.weekRevenue)"></span>
                        </span>
                    </div>
                </div>

                <div x-show="chartLoading" class="h-56 flex items-center justify-center">
                    <svg class="animate-spin h-7 w-7 text-gray-400 dark:text-white/30" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                </div>

                <div x-show="!chartLoading" x-cloak>
                    <template x-if="chartData.length === 0">
                        <div class="h-56 flex flex-col items-center justify-center text-center text-gray-400 dark:text-gray-500">
                            <svg class="w-10 h-10 mb-2" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/></svg>
                            <p class="text-sm">No revenue data for the last 7 days</p>
                        </div>
                    </template>

                    <template x-if="chartData.length > 0">
                        <div class="relative h-56 sm:h-60">
                            <div class="absolute inset-0 chart-gridlines rounded-lg pointer-events-none"></div>
                            <div class="relative h-full flex items-end gap-2 sm:gap-3 px-1 pt-2 pb-6">
                                <template x-for="(day, index) in chartData" :key="day.label">
                                    <div class="flex-1 flex flex-col items-center justify-end gap-1 min-w-0 h-full group">
                                        <span class="text-[10px] sm:text-xs font-semibold text-gray-500 dark:text-gray-400 tabular-nums transition-opacity duration-150" x-text="formatCompact(day.revenue)"></span>
                                        <div
                                            class="chart-bar w-full max-w-[44px] sm:max-w-[52px] rounded-t-lg"
                                            :style="'height: ' + barHeight(day.revenue) + '%'"
                                            :class="index === chartData.length - 1
                                                ? 'bg-gradient-to-t from-blue-600 to-blue-400 shadow-lg shadow-blue-500/25'
                                                : 'bg-gradient-to-t from-blue-500/70 to-blue-400/50 dark:from-blue-600/70 dark:to-blue-400/40 group-hover:from-blue-600 group-hover:to-blue-400'"
                                        ></div>
                                        <span class="absolute bottom-0 text-[10px] sm:text-xs font-medium text-gray-500 dark:text-gray-400 whitespace-nowrap" x-text="dayShortLabel(day.label)"></span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Quick Stats Sidebar --}}
            <div class="fade-in-up bg-white dark:bg-[#1a1f3d] rounded-2xl border border-gray-100 dark:border-white/10 p-5" style="animation-delay: 200ms">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-base font-semibold text-gray-900 dark:text-white">Quick Stats</h2>
                    <span class="text-[11px] text-gray-400 dark:text-gray-500">Today</span>
                </div>

                <div class="space-y-3">
                    <div class="flex items-center justify-between gap-3 rounded-lg px-3 py-2.5 bg-gray-50 dark:bg-white/5">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <div class="h-8 w-8 rounded-lg bg-blue-100 dark:bg-blue-500/20 flex items-center justify-center shrink-0">
                                <svg class="h-4 w-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs text-gray-500 dark:text-gray-400">Avg Order Value</p>
                                <p class="text-sm font-semibold text-gray-900 dark:text-white tabular-nums" x-text="formatCurrency(stats.avgOrderValue)"></p>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between gap-3 rounded-lg px-3 py-2.5 bg-gray-50 dark:bg-white/5">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <div class="h-8 w-8 rounded-lg bg-emerald-100 dark:bg-emerald-500/20 flex items-center justify-center shrink-0">
                                <svg class="h-4 w-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs text-gray-500 dark:text-gray-400">Customers Today</p>
                                <p class="text-sm font-semibold text-gray-900 dark:text-white tabular-nums" x-text="stats.customersToday"></p>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between gap-3 rounded-lg px-3 py-2.5 bg-gray-50 dark:bg-white/5">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <div class="h-8 w-8 rounded-lg bg-violet-100 dark:bg-violet-500/20 flex items-center justify-center shrink-0">
                                <svg class="h-4 w-4 text-violet-600 dark:text-violet-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z"/></svg>
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs text-gray-500 dark:text-gray-400">Top Seller</p>
                                <p class="text-sm font-semibold text-gray-900 dark:text-white truncate" x-text="stats.topSeller || '--'"></p>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between gap-3 rounded-lg px-3 py-2.5 bg-gray-50 dark:bg-white/5">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <div class="h-8 w-8 rounded-lg bg-cyan-100 dark:bg-cyan-500/20 flex items-center justify-center shrink-0">
                                <svg class="h-4 w-4 text-cyan-600 dark:text-cyan-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z"/><path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z"/></svg>
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs text-gray-500 dark:text-gray-400">Best Category</p>
                                <p class="text-sm font-semibold text-gray-900 dark:text-white truncate" x-text="stats.bestCategory || '--'"></p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Divider --}}
                <hr class="my-4 border-gray-100 dark:border-white/10">

                {{-- Profit margin donut --}}
                <div class="flex items-center gap-4">
                    <div class="relative h-20 w-20 rounded-full shrink-0" :style="donutStyle()">
                        <div class="absolute inset-[7px] rounded-full bg-white dark:bg-[#1a1f3d] flex items-center justify-center">
                            <span class="text-sm font-bold text-emerald-600 dark:text-emerald-400 tabular-nums" x-text="(stats.profitMargin || 0).toFixed(1) + '%'"></span>
                        </div>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">Profit Margin</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Across all closed orders</p>
                        <span class="inline-flex items-center gap-1 text-xs font-medium text-emerald-600 dark:text-emerald-400 mt-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Healthy margin
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Recent Orders + Today's Insights --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            {{-- Recent Orders Table --}}
            <div class="lg:col-span-2 fade-in-up bg-white dark:bg-[#1a1f3d] rounded-2xl border border-gray-100 dark:border-white/10 overflow-hidden" style="animation-delay: 280ms">
                <div class="flex items-center justify-between px-4 sm:px-6 py-4 border-b border-gray-100 dark:border-white/10">
                    <div>
                        <h2 class="text-base font-semibold text-gray-900 dark:text-white">Recent Orders</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Latest transactions across the store</p>
                    </div>
                    <a href="{{ route('orders.index') }}" class="inline-flex items-center gap-1 text-sm font-medium text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 transition-colors">
                        View All
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                    </a>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100 dark:divide-white/5">
                        <thead class="bg-gray-50 dark:bg-white/5">
                            <tr>
                                <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Order #</th>
                                <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Customer</th>
                                <th class="px-4 sm:px-6 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Amount</th>
                                <th class="px-4 sm:px-6 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Due</th>
                                <th class="px-4 sm:px-6 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden sm:table-cell">Items</th>
                                <th class="px-4 sm:px-6 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                <th class="px-4 sm:px-6 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Time</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                            <template x-if="recentOrdersLoading">
                                <template x-for="i in 4" :key="i">
                                    <tr>
                                        <td colspan="7" class="px-6 py-3">
                                            <div class="h-8 rounded-lg bg-gray-100 dark:bg-white/5 animate-pulse"></div>
                                        </td>
                                    </tr>
                                </template>
                            </template>

                            <template x-if="!recentOrdersLoading && recentOrders.length === 0">
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center text-sm text-gray-500 dark:text-gray-400">
                                        <svg class="mx-auto h-10 w-10 text-gray-300 dark:text-gray-600 mb-2" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                        </svg>
                                        No recent orders
                                    </td>
                                </tr>
                            </template>

                            <template x-for="order in recentOrders" :key="order.id">
                                <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                                    <td class="px-4 sm:px-6 py-3.5 whitespace-nowrap">
                                        <span class="text-sm font-mono font-medium text-blue-600 dark:text-blue-400" x-text="'#' + order.number"></span>
                                    </td>
                                    <td class="px-4 sm:px-6 py-3.5 whitespace-nowrap">
                                        <div class="flex items-center gap-2.5">
                                            <div class="h-7 w-7 rounded-full bg-gray-100 dark:bg-white/10 flex items-center justify-center shrink-0">
                                                <span class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase" x-text="(order.customer_name || 'W').charAt(0)"></span>
                                            </div>
                                            <span class="text-sm text-gray-700 dark:text-gray-300 truncate max-w-[120px] sm:max-w-[160px]" x-text="order.customer_name"></span>
                                        </div>
                                    </td>
                                    <td class="px-4 sm:px-6 py-3.5 whitespace-nowrap text-right">
                                        <span class="text-sm font-semibold text-gray-900 dark:text-white tabular-nums" x-text="formatCurrency(order.total_amount)"></span>
                                    </td>
                                    <td class="px-4 sm:px-6 py-3.5 whitespace-nowrap text-right">
                                        <span class="inline-flex items-center gap-1 text-sm font-semibold tabular-nums" :class="order.due_amount > 0.005 ? 'text-amber-600 dark:text-amber-400' : 'text-gray-400 dark:text-gray-500'">
                                            <svg class="w-3.5 h-3.5" x-show="order.due_amount > 0.005" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                                            <span x-text="formatCurrency(order.due_amount)"></span>
                                        </span>
                                    </td>
                                    <td class="px-4 sm:px-6 py-3.5 whitespace-nowrap text-center hidden sm:table-cell">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-gray-100 dark:bg-white/10 text-xs font-medium text-gray-600 dark:text-gray-300 tabular-nums" x-text="order.items_count"></span>
                                    </td>
                                    <td class="px-4 sm:px-6 py-3.5 whitespace-nowrap text-center">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium" :class="statusInfo(order.status).cls">
                                            <span class="h-1.5 w-1.5 rounded-full" :class="statusInfo(order.status).dot"></span>
                                            <span x-text="statusInfo(order.status).label"></span>
                                        </span>
                                    </td>
                                    <td class="px-4 sm:px-6 py-3.5 whitespace-nowrap text-right">
                                        <span class="text-xs text-gray-500 dark:text-gray-400" x-text="timeAgo(order.created_at)"></span>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Today's Insights --}}
            <div class="fade-in-up bg-white dark:bg-[#1a1f3d] rounded-2xl border border-gray-100 dark:border-white/10 p-5" style="animation-delay: 360ms">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-base font-semibold text-gray-900 dark:text-white">Order Insights</h2>
                    <span class="inline-flex items-center gap-1.5 text-[11px] font-medium text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-500/15 px-2 py-1 rounded-full">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        Live
                    </span>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="rounded-xl bg-gray-50 dark:bg-white/5 p-4">
                        <div class="h-9 w-9 rounded-lg bg-emerald-100 dark:bg-emerald-500/20 flex items-center justify-center mb-3">
                            <svg class="h-5 w-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white tabular-nums" x-text="stats.todayOrders"></p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Completed Today</p>
                    </div>

                    <a href="{{ route('orders.index') }}" class="rounded-xl bg-gray-50 dark:bg-white/5 p-4 hover:bg-amber-50 dark:hover:bg-amber-500/10 transition group block">
                        <div class="h-9 w-9 rounded-lg bg-amber-100 dark:bg-amber-500/20 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                            <svg class="h-5 w-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white tabular-nums" x-text="stats.pendingOrders"></p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Open / Held Orders</p>
                    </a>

                    <div class="rounded-xl bg-gray-50 dark:bg-white/5 p-4">
                        <div class="h-9 w-9 rounded-lg bg-rose-100 dark:bg-rose-500/20 flex items-center justify-center mb-3">
                            <svg class="h-5 w-5 text-rose-600 dark:text-rose-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3"/></svg>
                        </div>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white tabular-nums" x-text="stats.refundsToday"></p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 truncate" x-text="formatCurrency(stats.refundAmount) + ' refunded'"></p>
                        <p class="text-[11px] text-gray-400 dark:text-gray-500 mt-0.5">Refunded Today</p>
                    </div>

                    <div class="rounded-xl bg-gray-50 dark:bg-white/5 p-4">
                        <div class="h-9 w-9 rounded-lg bg-gray-200 dark:bg-white/10 flex items-center justify-center mb-3">
                            <svg class="h-5 w-5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                        </div>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white tabular-nums" x-text="stats.voidedOrders"></p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Voided Today</p>
                    </div>
                </div>

                <hr class="my-4 border-gray-100 dark:border-white/10">

                <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                    <span>Last updated</span>
                    <span class="font-medium tabular-nums" x-text="lastUpdated || '--:--'"></span>
                </div>
            </div>
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
                    ownProducts: 0,
                    sharedProducts: 0,
                    lowStockItems: 0,
                    outOfStockItems: 0,
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
        statsLoading: true,
        recentOrders: [],
        recentOrdersLoading: true,
        firstName: '',
        lastUpdated: '',
        refreshing: false,

        get todayLabel() {
            return new Date().toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
        },

        get statCards() {
            const sym = Alpine.store('currency')?.symbol || '$';
            const cards = [
                {
                    key: 'sales',
                    label: "Today's Sales",
                    display: this.formatCurrency(this.stats.todaySales),
                    iconBg: 'bg-emerald-100 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400',
                    blob: 'bg-emerald-500',
                    delay: 0,
                    iconPath: 'M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 01-.75.75h-.75m1.5-1.5H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z',
                },
                {
                    key: 'orders',
                    label: 'Orders Today',
                    display: this.stats.todayOrders,
                    iconBg: 'bg-blue-100 dark:bg-blue-500/20 text-blue-600 dark:text-blue-400',
                    blob: 'bg-blue-500',
                    delay: 60,
                    iconPath: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4',
                },
                {
                    key: 'products',
                    label: 'Total Products',
                    display: this.stats.totalProducts,
                    iconBg: 'bg-violet-100 dark:bg-violet-500/20 text-violet-600 dark:text-violet-400',
                    blob: 'bg-violet-500',
                    delay: 120,
                    iconPath: 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
                },
                {
                    key: 'stock',
                    label: 'Low Stock',
                    display: this.stats.lowStockItems,
                    iconBg: 'bg-amber-100 dark:bg-amber-500/20 text-amber-600 dark:text-amber-400',
                    blob: 'bg-amber-500',
                    delay: 180,
                    iconPath: 'M20.618 5.984A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016zM12 9v2m0 4h.01',
                },
            ];

            if (this.stats.yesterdaySales > 0 && this.stats.todaySales > 0) {
                const pct = ((this.stats.todaySales - this.stats.yesterdaySales) / this.stats.yesterdaySales) * 100;
                cards[0].trend = { up: pct >= 0, pct: Math.abs(pct).toFixed(1) };
                cards[0].footer = 'vs ' + this.formatCurrency(this.stats.yesterdaySales) + ' yesterday';
            } else {
                cards[0].footer = 'vs ' + this.formatCurrency(this.stats.yesterdaySales) + ' yesterday';
            }

            cards[1].footer = this.stats.pendingOrders + ' open / held (all time)';
            cards[2].footer = this.stats.ownProducts + ' own · ' + this.stats.sharedProducts + ' shared';
            cards[3].footer = this.stats.outOfStockItems > 0
                ? this.stats.outOfStockItems + ' out of stock'
                : 'Needs restock';
            return cards;
        },

        async init() {
            this.fetchProfile();
            await Promise.all([this.fetchDashboard(), this.fetchRecentOrders()]);
        },

        async fetchProfile() {
            try {
                const d = await window.POS.api('/api/auth/me');
                const u = d?.data || d;
                this.firstName = (u?.first_name || u?.username || '').split(' ')[0];
            } catch (e) { this.firstName = ''; }
        },

        async fetchDashboard() {
            try {
                const data = await window.POS.api('/api/dashboard');
                this.stats = {
                    todaySales: parseFloat(data.todays_sales) || 0,
                    yesterdaySales: parseFloat(data.yesterday_sales) || 0,
                    todayOrders: data.orders_count || 0,
                    pendingOrders: data.pending_orders || 0,
                    totalProducts: data.products_count || 0,
                    activeProducts: data.active_products || 0,
                    ownProducts: data.own_products_count || 0,
                    sharedProducts: data.shared_products_count || 0,
                    lowStockItems: data.low_stock_count || 0,
                    outOfStockItems: data.out_of_stock_count || 0,
                    weekRevenue: parseFloat(data.week_revenue) || 0,
                    avgOrderValue: parseFloat(data.avg_order_value) || 0,
                    topSeller: data.top_seller || '',
                    bestCategory: data.best_category || '',
                    customersToday: data.customers_count || 0,
                    refundsToday: data.refunds_today || 0,
                    refundAmount: parseFloat(data.refund_amount) || 0,
                    voidedOrders: data.voided_orders || 0,
                    profitMargin: parseFloat(data.profit_margin) || 0,
                };
                this.chartData = (data.revenue_chart || []).map(d => ({
                    label: d.label,
                    revenue: parseFloat(d.value) || 0,
                }));
                this.lastUpdated = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            } catch (e) {
                console.error('Failed to fetch dashboard data:', e);
            } finally {
                this.statsLoading = false;
                this.chartLoading = false;
            }
        },

        async fetchRecentOrders() {
            this.recentOrdersLoading = true;
            try {
                const data = await window.POS.api('/api/orders?per_page=6');
                const list = data?.data?.data || data?.data || [];
                this.recentOrders = list.slice(0, 6).map(o => ({
                    id: o.id,
                    number: o.number || o.id,
                    customer_name: o.customer?.name || 'Walk-in',
                    total_amount: parseFloat(o.total) || 0,
                    due_amount: Math.max(0, (parseFloat(o.total) || 0) - (parseFloat(o.paid_amount) || 0)),
                    items_count: o.pos_order_items_count ?? 0,
                    status: o.status || '',
                    created_at: o.created_at,
                }));
            } catch (e) {
                console.error('Failed to fetch recent orders:', e);
            } finally {
                this.recentOrdersLoading = false;
            }
        },

        async refresh() {
            if (this.refreshing) return;
            this.refreshing = true;
            await Promise.all([this.fetchDashboard(), this.fetchRecentOrders()]);
            this.refreshing = false;
        },

        greeting() {
            const h = new Date().getHours();
            if (h < 12) return 'Good morning';
            if (h < 17) return 'Good afternoon';
            return 'Good evening';
        },

        formatCurrency(amount) {
            const num = parseFloat(amount) || 0;
            const sym = Alpine.store('currency')?.symbol || '$';
            const dec = Alpine.store('currency')?.decimalPlaces ?? 2;
            return sym + num.toLocaleString('en-US', { minimumFractionDigits: dec, maximumFractionDigits: dec });
        },

        formatCompact(amount) {
            const num = parseFloat(amount) || 0;
            const sym = Alpine.store('currency')?.symbol || '$';
            const abs = Math.abs(num);
            if (abs >= 1000000) return sym + (num / 1000000).toFixed(1) + 'M';
            if (abs >= 1000) return sym + (num / 1000).toFixed(1) + 'k';
            return sym + num.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        },

        barHeight(revenue) {
            const max = Math.max(...this.chartData.map(d => d.revenue || 0), 1);
            const ratio = (revenue || 0) / max;
            return Math.max(ratio * 100, 4);
        },

        dayShortLabel(label) {
            const d = new Date(label + 'T00:00:00');
            if (isNaN(d)) return label;
            return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
        },

        donutStyle() {
            const pct = Math.max(0, Math.min(100, this.stats.profitMargin || 0));
            const track = 'rgba(148,163,184,0.25)';
            return 'background: conic-gradient(#10b981 0% ' + pct + '%, ' + track + ' ' + pct + '% 100%);';
        },

        statusInfo(status) {
            const s = (status || '').toLowerCase();
            if (s === 'closed') {
                return { label: 'Completed', cls: 'bg-emerald-100 dark:bg-emerald-500/15 text-emerald-700 dark:text-emerald-400', dot: 'bg-emerald-500' };
            }
            if (s === 'open' || s === 'held') {
                return { label: s === 'held' ? 'Held' : 'Open', cls: 'bg-amber-100 dark:bg-amber-500/15 text-amber-700 dark:text-amber-400', dot: 'bg-amber-500' };
            }
            if (s === 'cancelled') {
                return { label: 'Cancelled', cls: 'bg-red-100 dark:bg-red-500/15 text-red-700 dark:text-red-400', dot: 'bg-red-500' };
            }
            if (s === 'refunded') {
                return { label: 'Refunded', cls: 'bg-rose-100 dark:bg-rose-500/15 text-rose-700 dark:text-rose-400', dot: 'bg-rose-500' };
            }
            return { label: s || 'Unknown', cls: 'bg-gray-100 dark:bg-white/10 text-gray-600 dark:text-gray-300', dot: 'bg-gray-400' };
        },

        timeAgo(dateStr) {
            if (!dateStr) return '';
            const date = new Date(dateStr);
            if (isNaN(date)) return '';
            const diffMs = Date.now() - date.getTime();
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
