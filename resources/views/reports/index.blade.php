@extends('layouts.app')

@section('title', 'Reports')

@push('styles')
<style>
    .tab-active { border-bottom-color: #3b82f6; color: #3b82f6; }
    .dark .tab-active { color: #93c5fd; }
</style>
@endpush

@section('content')
<div x-data="reportsManager" class="flex flex-col h-full">
    <div class="flex items-center justify-between px-4 sm:px-6 py-3 sm:py-4 shrink-0">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white">Reports</h1>
            <p class="text-gray-500 dark:text-gray-400 text-sm mt-1">Analytics and business insights</p>
        </div>
        <div class="flex items-center gap-2 sm:gap-3 flex-wrap">
            <input
                type="date"
                x-model="dateFrom"
                class="bg-gray-50 dark:bg-[#0f1535] border border-gray-300 dark:border-white/10 rounded-lg px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
            <span class="text-gray-500 dark:text-white/50">to</span>
            <input
                type="date"
                x-model="dateTo"
                class="bg-gray-50 dark:bg-[#0f1535] border border-gray-300 dark:border-white/10 rounded-lg px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
            <button @click="fetchTabData()" class="bg-blue-500 hover:bg-blue-600 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                Apply
            </button>
            <select x-model="customerId" @change="fetchTabData()" x-show="activeTab === 'customer-detail'" class="bg-gray-50 dark:bg-[#0f1535] border border-gray-300 dark:border-white/10 rounded-lg px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 min-w-[180px]">
                <option value="">Select Customer</option>
                <template x-for="c in customers" :key="c.id">
                    <option :value="c.id" x-text="c.name"></option>
                </template>
            </select>
        </div>
    </div>

    <div class="px-4 sm:px-6 flex-1 overflow-hidden flex flex-col pb-4">
        <div class="bg-white dark:bg-[#1a1f3d] rounded-xl border border-gray-100 dark:border-white/5 flex flex-col flex-1 overflow-hidden">
            <div class="border-b border-gray-100 dark:border-white/5">
                <nav class="flex gap-0 -mb-px overflow-x-auto hide-scrollbar">
                    <template x-for="tab in tabs" :key="tab.key">
                    <button
                        @click="activeTab = tab.key; fetchTabData()"
                        class="px-5 py-3 text-sm font-medium transition border-b-2"
                        :class="activeTab === tab.key
                            ? 'tab-active border-blue-500 text-blue-600 dark:text-blue-400'
                            : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:border-gray-300 dark:hover:border-gray-700'"
                        x-text="tab.label"
                    ></button>
                    </template>
                </nav>
            </div>

            <div x-show="loading" class="flex justify-center py-20 flex-1 items-center">
                <svg class="animate-spin h-8 w-8 text-gray-400 dark:text-white/30" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>

            <template x-if="!loading">
                <div class="flex flex-col flex-1 overflow-auto">
                    <template x-if="activeTab === 'sales'">
                        <div class="flex flex-col flex-1 overflow-auto">
                            <div class="p-4 sm:p-6">
                                <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-5 mb-4 shrink-0">
                                    <div class="bg-gray-50 dark:bg-[#0f1535] rounded-lg p-4 border border-gray-100 dark:border-white/5">
                                        <span class="text-xs text-gray-500 dark:text-white/50">Total Sales</span>
                                        <div class="text-xl font-bold mt-1 text-gray-900 dark:text-white" x-text="$store.currency.symbol + parseFloat(tabData.total_sales || 0).toLocaleString('en-US', {minimumFractionDigits: 2})"></div>
                                    </div>
                                    <div class="bg-gray-50 dark:bg-[#0f1535] rounded-lg p-4 border border-gray-100 dark:border-white/5">
                                        <span class="text-xs text-gray-500 dark:text-white/50">Orders</span>
                                        <div class="text-xl font-bold mt-1 text-gray-900 dark:text-white" x-text="tabData.total_orders || 0"></div>
                                    </div>
                                    <div class="bg-gray-50 dark:bg-[#0f1535] rounded-lg p-4 border border-gray-100 dark:border-white/5">
                                        <span class="text-xs text-gray-500 dark:text-white/50">Avg. Order</span>
                                        <div class="text-xl font-bold mt-1 text-gray-900 dark:text-white" x-text="$store.currency.symbol + parseFloat(tabData.avg_order || 0).toFixed(2)"></div>
                                    </div>
                                    <div class="bg-gray-50 dark:bg-[#0f1535] rounded-lg p-4 border border-gray-100 dark:border-white/5">
                                        <span class="text-xs text-gray-500 dark:text-white/50">Tax Collected</span>
                                        <div class="text-xl font-bold mt-1 text-gray-900 dark:text-white" x-text="$store.currency.symbol + parseFloat(tabData.total_tax || 0).toFixed(2)"></div>
                                    </div>
                                </div>

                                <div class="bg-gray-50 dark:bg-[#0f1535] rounded-lg p-6 border border-gray-100 dark:border-white/5 mb-6">
                                    <h3 class="text-sm font-semibold text-gray-700 dark:text-white/70 mb-4">Revenue Trend</h3>
                                    <template x-if="tabData.chart_data && tabData.chart_data.length">
                                        <div class="flex items-end gap-2 h-48 px-2">
                                            <template x-for="(item, i) in tabData.chart_data" :key="i">
                                                <div class="flex-1 flex flex-col items-center gap-1.5 h-full justify-end">
                                                    <span class="text-xs text-gray-500 dark:text-white/50" x-text="$store.currency.symbol + item.value"></span>
                                                    <div
                                                        class="w-full bg-blue-500/60 hover:bg-blue-500/80 rounded-t transition-all duration-300 min-h-[4px]"
                                                        :style="'height:' + ((item.value / chartMax) * 100) + '%'"
                                                    ></div>
                                                    <span class="text-xs text-gray-500 dark:text-white/50 mt-1 truncate w-full text-center" x-text="item.label"></span>
                                                </div>
                                            </template>
                                        </div>
                                    </template>
                                    <template x-if="!tabData.chart_data || !tabData.chart_data.length">
                                        <div class="flex items-center justify-center h-48 text-gray-400 dark:text-white/30 text-sm">No chart data</div>
                                    </template>
                                </div>

                                <template x-if="tabData.records && tabData.records.length">
                                    <div class="overflow-x-auto bg-gray-50 dark:bg-[#0f1535] rounded-lg border border-gray-100 dark:border-white/5">
                                        <table class="w-full text-sm">
                                            <thead>
                                                <tr class="border-b border-gray-100 dark:border-white/5">
                                                    <th class="text-left text-xs font-medium text-gray-500 dark:text-white/50 uppercase px-3 sm:px-6 py-3">Date</th>
                                                    <th class="text-left text-xs font-medium text-gray-500 dark:text-white/50 uppercase px-3 sm:px-6 py-3">Order #</th>
                                                    <th class="text-left text-xs font-medium text-gray-500 dark:text-white/50 uppercase px-3 sm:px-6 py-3">Customer</th>
                                                    <th class="text-right text-xs font-medium text-gray-500 dark:text-white/50 uppercase px-3 sm:px-6 py-3">Subtotal</th>
                                                    <th class="text-right text-xs font-medium text-gray-500 dark:text-white/50 uppercase px-3 sm:px-6 py-3">Tax</th>
                                                    <th class="text-right text-xs font-medium text-gray-500 dark:text-white/50 uppercase px-3 sm:px-6 py-3">Total</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <template x-for="row in tabData.records" :key="row.id">
                                                    <tr class="border-b border-gray-100 dark:border-white/5 hover:bg-gray-100 dark:hover:bg-white/5">
                                                        <td class="px-3 sm:px-6 py-3 text-gray-500 dark:text-white/70" x-text="row.date || '--'"></td>
                                                        <td class="px-3 sm:px-6 py-3 font-mono text-xs text-gray-900 dark:text-white" x-text="'#' + row.id"></td>
                                                        <td class="px-3 sm:px-6 py-3 text-gray-900 dark:text-white" x-text="row.customer || 'Walk-in'"></td>
                                                        <td class="px-3 sm:px-6 py-3 text-right font-mono text-gray-700 dark:text-gray-300" x-text="$store.currency.symbol + parseFloat(row.subtotal || 0).toFixed(2)"></td>
                                                        <td class="px-3 sm:px-6 py-3 text-right font-mono text-gray-700 dark:text-gray-300" x-text="$store.currency.symbol + parseFloat(row.tax || 0).toFixed(2)"></td>
                                                        <td class="px-3 sm:px-6 py-3 text-right font-mono font-semibold text-gray-900 dark:text-white" x-text="$store.currency.symbol + parseFloat(row.total || 0).toFixed(2)"></td>
                                                    </tr>
                                                </template>
                                            </tbody>
                                        </table>
                                    </div>
                                </template>
                                <template x-if="!tabData.records || !tabData.records.length">
                                    <div class="text-center py-12 text-gray-400 dark:text-white/30 text-sm">No sales records for this period</div>
                                </template>
                            </div>
                    </template>

                    <!-- Best Selling Tab -->
                    <div x-show="activeTab === 'bestselling'" class="flex-1 overflow-auto">
                        <template x-if="tabData.records && tabData.records.length">
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead>
                                        <tr class="border-b border-gray-100 dark:border-white/5">
                                            <th class="text-left text-xs font-medium text-gray-500 dark:text-white/50 uppercase px-3 sm:px-6 py-3">Rank</th>
                                            <th class="text-left text-xs font-medium text-gray-500 dark:text-white/50 uppercase px-3 sm:px-6 py-3">Name</th>
                                            <th class="text-right text-xs font-medium text-gray-500 dark:text-white/50 uppercase px-3 sm:px-6 py-3">Qty Sold</th>
                                            <th class="text-right text-xs font-medium text-gray-500 dark:text-white/50 uppercase px-3 sm:px-6 py-3">Revenue</th>
                                            <th class="text-right text-xs font-medium text-gray-500 dark:text-white/50 uppercase px-3 sm:px-6 py-3">Profit</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-for="(row, i) in tabData.records" :key="i">
                                            <tr class="border-b border-gray-100 dark:border-white/5 hover:bg-gray-50 dark:hover:bg-white/5">
                                                <td class="px-3 sm:px-6 py-3">
                                                    <span
                                                        class="inline-flex items-center justify-center w-6 h-6 rounded-full text-xs font-bold"
                                                        :class="{
                                                            'bg-amber-500/20 text-amber-400': i === 0,
                                                            'bg-gray-200 dark:bg-white/10 text-gray-500 dark:text-gray-300': i === 1,
                                                            'bg-amber-700/10 text-amber-600': i === 2,
                                                            'text-gray-500 dark:text-white/50': i > 2,
                                                        }"
                                                        x-text="i + 1"
                                                    ></span>
                                                </td>
                                                <td class="px-3 sm:px-6 py-3 font-medium text-gray-900 dark:text-white" x-text="row.name"></td>
                                                <td class="px-3 sm:px-6 py-3 text-right text-gray-700 dark:text-gray-300" x-text="row.quantity || 0"></td>
                                                <td class="px-3 sm:px-6 py-3 text-right font-mono text-gray-700 dark:text-gray-300" x-text="$store.currency.symbol + parseFloat(row.revenue || 0).toFixed(2)"></td>
                                                <td class="px-3 sm:px-6 py-3 text-right font-mono text-green-600 dark:text-green-400" x-text="$store.currency.symbol + parseFloat(row.profit || 0).toFixed(2)"></td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </template>
                         <template x-if="!tabData.records || !tabData.records.length">
                            <div class="text-center py-16 text-gray-400 dark:text-white/30">
                                <svg class="w-12 h-12 mx-auto mb-3 text-gray-300 dark:text-white/20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                                <p class="text-sm">No data available for this period</p>
                            </div>
                        </template>
                    </div>


                    <!-- Customer Analytics Tab -->
                    <div x-show="activeTab === 'customers'" class="flex-1 overflow-auto">
                        <template x-if="tabData.records && tabData.records.length">
                            <div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 p-6">
                                    <div class="bg-gray-50 dark:bg-[#0f1535] rounded-lg p-4 border border-gray-100 dark:border-white/5">
                                        <span class="text-xs text-gray-500 dark:text-white/50">Total Orders</span>
                                        <div class="text-xl font-bold mt-1 text-gray-900 dark:text-white" x-text="tabData.total_orders || 0"></div>
                                    </div>
                                    <div class="bg-gray-50 dark:bg-[#0f1535] rounded-lg p-4 border border-gray-100 dark:border-white/5">
                                        <span class="text-xs text-gray-500 dark:text-white/50">Total Spent</span>
                                        <div class="text-xl font-bold mt-1 text-gray-900 dark:text-white" x-text="$store.currency.symbol + parseFloat(tabData.total_spent || 0).toFixed(2)"></div>
                                    </div>
                                </div>
                                <div class="overflow-x-auto">
                                    <table class="w-full text-sm">
                                        <thead>
                                            <tr class="border-b border-gray-100 dark:border-white/5">
                                                <th class="text-left text-xs font-medium text-gray-500 dark:text-white/50 uppercase px-3 sm:px-6 py-3">Rank</th>
                                                <th class="text-left text-xs font-medium text-gray-500 dark:text-white/50 uppercase px-3 sm:px-6 py-3">Name</th>
                                                <th class="text-right text-xs font-medium text-gray-500 dark:text-white/50 uppercase px-3 sm:px-6 py-3">Orders</th>
                                                <th class="text-right text-xs font-medium text-gray-500 dark:text-white/50 uppercase px-3 sm:px-6 py-3">Total Spent</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <template x-for="(row, i) in tabData.records" :key="i">
                                                <tr class="border-b border-gray-100 dark:border-white/5 hover:bg-gray-50 dark:hover:bg-white/5">
                                                    <td class="px-3 sm:px-6 py-3">
                                                        <span
                                                            class="inline-flex items-center justify-center w-6 h-6 rounded-full text-xs font-bold"
                                                            :class="{
                                                                'bg-amber-500/20 text-amber-400': i === 0,
                                                                'bg-gray-200 dark:bg-white/10 text-gray-500 dark:text-gray-300': i === 1,
                                                                'bg-amber-700/10 text-amber-600': i === 2,
                                                                'text-gray-500 dark:text-white/50': i > 2,
                                                            }"
                                                            x-text="i + 1"
                                                        ></span>
                                                    </td>
                                                    <td class="px-3 sm:px-6 py-3 font-medium text-gray-900 dark:text-white" x-text="row.name || '--'"></td>
                                                    <td class="px-3 sm:px-6 py-3 text-right text-gray-700 dark:text-gray-300" x-text="row.order_count || 0"></td>
                                                    <td class="px-3 sm:px-6 py-3 text-right font-mono text-gray-700 dark:text-gray-300" x-text="$store.currency.symbol + parseFloat(row.total_spent || 0).toFixed(2)"></td>
                                                </tr>
                                            </template>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </template>
                        <template x-if="!tabData.records || !tabData.records.length">
                            <div class="text-center py-16 text-gray-400 dark:text-white/30">
                                <svg class="w-12 h-12 mx-auto mb-3 text-gray-300 dark:text-white/20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                                <p class="text-sm">No data available for this period</p>
                            </div>
                        </template>
                    </div>


                    <!-- Customer Detail Tab -->
                    <div x-show="activeTab === 'customer-detail'" class="flex-1 overflow-auto">
                        <template x-if="customerId && tabData.customer">
                            <div>
                                <div class="p-4 sm:p-6">
                                    <div class="flex items-center gap-4 mb-6">
                                        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-full w-14 h-14 flex items-center justify-center text-lg font-bold text-blue-600 dark:text-blue-400" x-text="(tabData.customer.name || '?')[0]"></div>
                                        <div>
                                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white" x-text="tabData.customer.name"></h2>
                                            <p class="text-sm text-gray-500 dark:text-gray-400" x-text="tabData.customer.email || tabData.customer.phone || '--'"></p>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4 mb-4 shrink-0">
                                        <div class="bg-gray-50 dark:bg-[#0f1535] rounded-lg p-4 border border-gray-100 dark:border-white/5">
                                            <span class="text-xs text-gray-500 dark:text-white/50">Total Orders</span>
                                            <div class="text-xl font-bold mt-1 text-gray-900 dark:text-white" x-text="tabData.summary.order_count || 0"></div>
                                        </div>
                                        <div class="bg-gray-50 dark:bg-[#0f1535] rounded-lg p-4 border border-gray-100 dark:border-white/5">
                                            <span class="text-xs text-gray-500 dark:text-white/50">Total Spent</span>
                                            <div class="text-xl font-bold mt-1 text-gray-900 dark:text-white" x-text="$store.currency.symbol + parseFloat(tabData.summary.total_spent || 0).toFixed(2)"></div>
                                        </div>
                                        <div class="bg-gray-50 dark:bg-[#0f1535] rounded-lg p-4 border border-gray-100 dark:border-white/5">
                                            <span class="text-xs text-gray-500 dark:text-white/50">Avg. Order</span>
                                            <div class="text-xl font-bold mt-1 text-gray-900 dark:text-white" x-text="$store.currency.symbol + parseFloat(tabData.summary.avg_order || 0).toFixed(2)"></div>
                                        </div>
                                        <div class="bg-gray-50 dark:bg-[#0f1535] rounded-lg p-4 border border-gray-100 dark:border-white/5">
                                            <span class="text-xs text-gray-500 dark:text-white/50">Total Items</span>
                                            <div class="text-xl font-bold mt-1 text-gray-900 dark:text-white" x-text="tabData.summary.item_count || 0"></div>
                                        </div>
                                    </div>
                                    <template x-if="tabData.summary.top_products && tabData.summary.top_products.length">
                                        <div class="mb-6">
                                            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Top Products</h3>
                                            <div class="flex flex-wrap gap-2">
                                                <template x-for="p in tabData.summary.top_products" :key="p.product_name">
                                                    <span class="px-3 py-1.5 bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 rounded-full text-xs font-medium" x-text="p.product_name + ' (x' + (p.total_qty || 0) + ')'"></span>
                                                </template>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                                <div class="overflow-x-auto">
                                    <table class="w-full text-sm">
                                        <thead>
                                            <tr class="border-b border-gray-100 dark:border-white/5">
                                                <th class="text-left text-xs font-medium text-gray-500 dark:text-white/50 uppercase px-3 sm:px-6 py-3">Order #</th>
                                                <th class="text-left text-xs font-medium text-gray-500 dark:text-white/50 uppercase px-3 sm:px-6 py-3">Date</th>
                                                <th class="text-center text-xs font-medium text-gray-500 dark:text-white/50 uppercase px-3 sm:px-6 py-3">Items</th>
                                                <th class="text-right text-xs font-medium text-gray-500 dark:text-white/50 uppercase px-3 sm:px-6 py-3">Total</th>
                                                <th class="text-center text-xs font-medium text-gray-500 dark:text-white/50 uppercase px-3 sm:px-6 py-3">Payment</th>
                                                <th class="text-center text-xs font-medium text-gray-500 dark:text-white/50 uppercase px-3 sm:px-6 py-3">Receipt</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <template x-for="order in tabData.orders" :key="order.id">
                                                <tr class="border-b border-gray-100 dark:border-white/5 hover:bg-gray-50 dark:hover:bg-white/5">
                                                    <td class="px-3 sm:px-6 py-3 font-medium text-gray-900 dark:text-white" x-text="order.number"></td>
                                                    <td class="px-3 sm:px-6 py-3 text-gray-500 dark:text-gray-400 text-xs" x-text="order.date"></td>
                                                    <td class="px-3 sm:px-6 py-3 text-center text-gray-700 dark:text-gray-300" x-text="order.item_count"></td>
                                                    <td class="px-3 sm:px-6 py-3 text-right font-mono text-gray-700 dark:text-gray-300" x-text="$store.currency.symbol + parseFloat(order.total || 0).toFixed(2)"></td>
                                                    <td class="px-3 sm:px-6 py-3 text-center text-xs text-gray-500 dark:text-gray-400" x-text="order.payment + (order.service_type === 0 ? ' (Dine-in)' : order.service_type === 1 ? ' (Takeaway)' : '')"></td>
                                                    <td class="px-3 sm:px-6 py-3 text-center">
                                                        <button @click="downloadCustReceipt(order.id)" class="text-indigo-500 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 p-1 rounded transition-colors" title="Download Receipt">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                                                        </button>
                                                    </td>
                                                </tr>
                                            </template>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </template>
                        <template x-if="customerId && !tabData.customer">
                            <div class="text-center py-16 text-gray-400 dark:text-white/30">
                                <svg class="w-12 h-12 mx-auto mb-3 text-gray-300 dark:text-white/20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <p class="text-sm">Select a customer to view their sales history</p>
                            </div>
                        </template>
                        <template x-if="!customerId">
                            <div class="text-center py-16 text-gray-400 dark:text-white/30">
                                <svg class="w-12 h-12 mx-auto mb-3 text-gray-300 dark:text-white/20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <p class="text-sm">Select a customer from the dropdown above</p>
                            </div>
                        </template>
                            </div>
                        </template>
                    </div>

                    <!-- Tax Report Tab -->
                    <div x-show="activeTab === 'tax'" class="flex-1 overflow-auto">
                        <template x-if="tabData.records && tabData.records.length">
                            <div>
                                <div class="p-4 sm:p-6">
                                    <div class="bg-gray-50 dark:bg-[#0f1535] rounded-lg p-4 border border-gray-100 dark:border-white/5 inline-block mb-6">
                                        <span class="text-xs text-gray-500 dark:text-white/50">Total Tax Collected</span>
                                        <div class="text-xl font-bold mt-1 text-gray-900 dark:text-white" x-text="$store.currency.symbol + parseFloat(tabData.total_tax || 0).toFixed(2)"></div>
                                    </div>
                                </div>
                                <div class="overflow-x-auto">
                                    <table class="w-full text-sm">
                                        <thead>
                                            <tr class="border-b border-gray-100 dark:border-white/5">
                                                <th class="text-left text-xs font-medium text-gray-500 dark:text-white/50 uppercase px-3 sm:px-6 py-3">Date</th>
                                                <th class="text-right text-xs font-medium text-gray-500 dark:text-white/50 uppercase px-3 sm:px-6 py-3">Taxable Amount</th>
                                                <th class="text-right text-xs font-medium text-gray-500 dark:text-white/50 uppercase px-3 sm:px-6 py-3">Tax Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <template x-for="(row, i) in tabData.records" :key="i">
                                                <tr class="border-b border-gray-100 dark:border-white/5 hover:bg-gray-50 dark:hover:bg-white/5">
                                                    <td class="px-3 sm:px-6 py-3 font-medium text-gray-900 dark:text-white" x-text="row.date || '--'"></td>
                                                    <td class="px-3 sm:px-6 py-3 text-right font-mono text-gray-700 dark:text-gray-300" x-text="$store.currency.symbol + parseFloat(row.taxable_amount || 0).toFixed(2)"></td>
                                                    <td class="px-3 sm:px-6 py-3 text-right font-mono text-gray-700 dark:text-gray-300" x-text="$store.currency.symbol + parseFloat(row.tax_amount || 0).toFixed(2)"></td>
                                                </tr>
                                            </template>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </template>
                        <template x-if="!tabData.records || !tabData.records.length">
                            <div class="text-center py-16 text-gray-400 dark:text-white/30">
                                <svg class="w-12 h-12 mx-auto mb-3 text-gray-300 dark:text-white/20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                                <p class="text-sm">No data available for this period</p>
                            </div>
                        </template>
                    </div>

                    <template x-if="pagination && pagination.last_page > 1">
                        <div class="flex items-center justify-between px-4 sm:px-6 py-3 border-t border-gray-100 dark:border-white/5 shrink-0">
                            <span class="text-sm text-gray-500 dark:text-gray-400" x-text="'Page ' + pagination.current_page + ' of ' + pagination.last_page + ' (' + pagination.total + ' records)'"></span>
                            <div class="flex gap-2">
                                <button @click="fetchTabData(pagination.current_page - 1)" :disabled="pagination.current_page <= 1" class="px-3 py-1.5 text-sm rounded-lg border border-gray-300 dark:border-gray-600 disabled:opacity-40 hover:bg-gray-50 dark:hover:bg-white/5">Previous</button>
                                <button @click="fetchTabData(pagination.current_page + 1)" :disabled="pagination.current_page >= pagination.last_page" class="px-3 py-1.5 text-sm rounded-lg border border-gray-300 dark:border-gray-600 disabled:opacity-40 hover:bg-gray-50 dark:hover:bg-white/5">Next</button>
                            </div>
                        </div>
                    </template>
                </div>
            </template>
        </div>
    </div>
</div>
@endsection