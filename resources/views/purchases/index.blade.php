@extends('layouts.app')
@section('title', 'Purchases')
@section('content')
<div x-data="purchasesManager" class="h-full flex flex-col">
    {{-- Header --}}
    <div class="flex flex-wrap items-center justify-between gap-4 px-4 lg:px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">
        <div>
            <h1 class="text-lg font-bold text-gray-900 dark:text-white">Purchases</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Manage suppliers, orders, returns & reports</p>
        </div>
        <div class="flex gap-2" x-show="activeTab === 'suppliers'">
            <button @click="openSupplierForm()" class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">Add Supplier</button>
        </div>
        <div class="flex gap-2" x-show="activeTab === 'purchases'">
            <button @click="openPurchaseForm()" class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">New Purchase</button>
        </div>
        <div class="flex gap-2" x-show="activeTab === 'returns'">
            <button @click="openReturnForm()" class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">New Return</button>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="flex gap-1 px-4 lg:px-6 pt-3 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 overflow-x-auto">
        <button @click="switchTab('report-dashboard')" :class="activeTab==='report-dashboard'?'border-blue-500 text-blue-600 dark:text-blue-400':'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'" class="px-4 py-2.5 text-sm font-medium border-b-2 transition-colors whitespace-nowrap">Dashboard</button>
        <button @click="switchTab('suppliers')" :class="activeTab==='suppliers'?'border-blue-500 text-blue-600 dark:text-blue-400':'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'" class="px-4 py-2.5 text-sm font-medium border-b-2 transition-colors whitespace-nowrap">Suppliers</button>
        <button @click="switchTab('purchases')" :class="activeTab==='purchases'?'border-blue-500 text-blue-600 dark:text-blue-400':'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'" class="px-4 py-2.5 text-sm font-medium border-b-2 transition-colors whitespace-nowrap">Purchases</button>
        <button @click="switchTab('returns')" :class="activeTab==='returns'?'border-blue-500 text-blue-600 dark:text-blue-400':'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'" class="px-4 py-2.5 text-sm font-medium border-b-2 transition-colors whitespace-nowrap">Returns</button>
        <button @click="switchTab('report-suppliers')" :class="activeTab==='report-suppliers'?'border-blue-500 text-blue-600 dark:text-blue-400':'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'" class="px-4 py-2.5 text-sm font-medium border-b-2 transition-colors whitespace-nowrap">By Supplier</button>
        <button @click="switchTab('report-products')" :class="activeTab==='report-products'?'border-blue-500 text-blue-600 dark:text-blue-400':'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'" class="px-4 py-2.5 text-sm font-medium border-b-2 transition-colors whitespace-nowrap">By Product</button>
        <button @click="switchTab('report-monthly')" :class="activeTab==='report-monthly'?'border-blue-500 text-blue-600 dark:text-blue-400':'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'" class="px-4 py-2.5 text-sm font-medium border-b-2 transition-colors whitespace-nowrap">Monthly</button>
        <button @click="switchTab('report-outstanding')" :class="activeTab==='report-outstanding'?'border-blue-500 text-blue-600 dark:text-blue-400':'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'" class="px-4 py-2.5 text-sm font-medium border-b-2 transition-colors whitespace-nowrap">Outstanding</button>
    </div>

    {{-- Content Area --}}
    <div class="flex-1 overflow-auto p-4 lg:p-6 bg-gray-50 dark:bg-gray-900">

        {{-- ==================== SUPPLIERS TAB ==================== --}}
        <div x-show="activeTab === 'suppliers'" x-cloak>
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                    <input type="text" x-model="supplierSearch" @input.debounce.300ms="fetchSuppliers()" placeholder="Search suppliers..." class="w-full max-w-xs px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Code</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Phone</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Email</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <template x-if="suppliersLoading">
                                <tr><td colspan="6" class="px-6 py-12 text-center text-gray-400">Loading...</td></tr>
                            </template>
                            <template x-if="!suppliersLoading && suppliers.length === 0">
                                <tr><td colspan="6" class="px-6 py-12 text-center text-gray-400">No suppliers found</td></tr>
                            </template>
                            <template x-for="s in suppliers" :key="s.id">
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                    <td class="px-6 py-4 font-medium text-gray-900 dark:text-white" x-text="s.name"></td>
                                    <td class="px-6 py-4 text-gray-500 dark:text-gray-400 font-mono" x-text="s.code || '—'"></td>
                                    <td class="px-6 py-4 text-gray-600 dark:text-gray-300" x-text="s.phone_number || '—'"></td>
                                    <td class="px-6 py-4 text-gray-600 dark:text-gray-300" x-text="s.email || '—'"></td>
                                    <td class="px-6 py-4 text-center">
                                        <span x-show="s.is_enabled" class="text-green-600 dark:text-green-400 text-xs font-medium bg-green-50 dark:bg-green-500/20 px-2 py-0.5 rounded">Active</span>
                                        <span x-show="!s.is_enabled" class="text-red-600 dark:text-red-400 text-xs font-medium bg-red-50 dark:bg-red-500/20 px-2 py-0.5 rounded">Disabled</span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <button @click="editSupplier(s)" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 text-xs font-medium mr-2">Edit</button>
                                        <button @click="deleteSupplier(s.id)" class="text-red-600 hover:text-red-800 dark:text-red-400 text-xs font-medium">Delete</button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ==================== PURCHASES TAB ==================== --}}
        <div x-show="activeTab === 'purchases'" x-cloak>
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex flex-wrap gap-3 items-center">
                    <input type="text" x-model="purchaseSearch" @input.debounce.300ms="fetchPurchases()" placeholder="Search PO# or Ref#" class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white w-full max-w-xs">
                    <select x-model="purchaseStatusFilter" @change="fetchPurchases()" class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="ordered">Ordered</option>
                        <option value="partially_received">Partially Received</option>
                        <option value="received">Received</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">PO#</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Supplier</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Date</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Total</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Status</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Payment</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <template x-if="purchasesLoading"><tr><td colspan="7" class="px-6 py-12 text-center text-gray-400">Loading...</td></tr></template>
                            <template x-if="!purchasesLoading && purchases.length === 0"><tr><td colspan="7" class="px-6 py-12 text-center text-gray-400">No purchases found</td></tr></template>
                            <template x-for="po in purchases" :key="po.id">
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                    <td class="px-4 py-4 font-mono text-sm font-medium text-gray-900 dark:text-white" x-text="po.purchase_number"></td>
                                    <td class="px-4 py-4 text-gray-600 dark:text-gray-300" x-text="po.supplier?.name || '—'"></td>
                                    <td class="px-4 py-4 text-gray-600 dark:text-gray-300" x-text="po.purchase_date?.split('T')[0]"></td>
                                    <td class="px-4 py-4 text-right font-medium text-gray-900 dark:text-white" x-text="(Alpine.store('currency')?.symbol || '$') + Number(po.grand_total).toFixed(2)"></td>
                                    <td class="px-4 py-4 text-center">
                                        <span :class="{
                                            'bg-yellow-100 text-yellow-700 dark:bg-yellow-500/20 dark:text-yellow-400': po.status==='pending',
                                            'bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-400': po.status==='ordered',
                                            'bg-purple-100 text-purple-700 dark:bg-purple-500/20 dark:text-purple-400': po.status==='partially_received',
                                            'bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400': po.status==='received',
                                            'bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-400': po.status==='cancelled',
                                        }" class="px-2 py-0.5 rounded text-xs font-medium capitalize" x-text="po.status.replace('_',' ')"></span>
                                    </td>
                                    <td class="px-4 py-4 text-center">
                                        <span :class="po.payment_status==='paid'?'text-green-600':'text-red-500'" class="text-xs font-medium capitalize" x-text="po.payment_status"></span>
                                    </td>
                                    <td class="px-4 py-4 text-right space-x-2">
                                        <button @click="viewPurchase(po)" class="text-blue-600 hover:text-blue-800 text-xs font-medium">View</button>
                                        <button x-show="['pending','ordered','partially_received'].includes(po.status)" @click="receivePurchase(po)" class="text-green-600 hover:text-green-800 text-xs font-medium">Receive</button>
                                        <button x-show="po.payment_status !== 'paid'" @click="markPaid(po.id)" class="text-amber-600 hover:text-amber-800 text-xs font-medium">Pay</button>
                                        <button x-show="['pending','ordered'].includes(po.status)" @click="cancelPurchase(po.id)" class="text-red-600 hover:text-red-800 text-xs font-medium">Cancel</button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
                <div x-show="purchasesPage.last_page > 1" class="flex justify-center gap-2 p-4 border-t border-gray-200 dark:border-gray-700">
                    <button @click="fetchPurchases(purchasesPage.current_page-1)" :disabled="purchasesPage.current_page<=1" class="px-3 py-1.5 text-sm border rounded-lg disabled:opacity-40">Prev</button>
                    <span class="px-3 py-1.5 text-sm text-gray-500" x-text="purchasesPage.current_page + ' / ' + purchasesPage.last_page"></span>
                    <button @click="fetchPurchases(purchasesPage.current_page+1)" :disabled="purchasesPage.current_page>=purchasesPage.last_page" class="px-3 py-1.5 text-sm border rounded-lg disabled:opacity-40">Next</button>
                </div>
            </div>
        </div>

        {{-- ==================== RETURNS TAB ==================== --}}
        <div x-show="activeTab === 'returns'" x-cloak>
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Return #</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Purchase</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Supplier</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Date</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Total</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <template x-if="returnsLoading"><tr><td colspan="6" class="px-6 py-12 text-center text-gray-400">Loading...</td></tr></template>
                            <template x-if="!returnsLoading && returns.length === 0"><tr><td colspan="6" class="px-6 py-12 text-center text-gray-400">No returns found</td></tr></template>
                            <template x-for="r in returns" :key="r.id">
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                    <td class="px-6 py-4 font-mono text-sm text-gray-900 dark:text-white" x-text="r.return_number"></td>
                                    <td class="px-6 py-4 text-gray-600 dark:text-gray-300" x-text="r.purchase?.purchase_number || '—'"></td>
                                    <td class="px-6 py-4 text-gray-600 dark:text-gray-300" x-text="r.supplier?.name || '—'"></td>
                                    <td class="px-6 py-4 text-gray-600 dark:text-gray-300" x-text="r.return_date?.split('T')[0]"></td>
                                    <td class="px-6 py-4 text-right font-medium text-gray-900 dark:text-white" x-text="(Alpine.store('currency')?.symbol || '$') + Number(r.grand_total).toFixed(2)"></td>
                                    <td class="px-6 py-4 text-center"><span class="px-2 py-0.5 rounded text-xs font-medium capitalize bg-green-100 text-green-700" x-text="r.status"></span></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ==================== REPORT: DASHBOARD ==================== --}}
        <div x-show="activeTab === 'report-dashboard'" x-cloak>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase font-semibold">Total Purchases</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1" x-text="reportData.summary?.total_count || 0"></p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase font-semibold">Total Amount</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1" x-text="(Alpine.store('currency')?.symbol || '$') + Number(reportData.summary?.total_amount || 0).toFixed(2)"></p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase font-semibold">Total Paid</p>
                    <p class="text-2xl font-bold text-green-600 mt-1" x-text="(Alpine.store('currency')?.symbol || '$') + Number(reportData.summary?.total_paid || 0).toFixed(2)"></p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase font-semibold">Total Due</p>
                    <p class="text-2xl font-bold text-red-600 mt-1" x-text="(Alpine.store('currency')?.symbol || '$') + Number(reportData.summary?.total_due || 0).toFixed(2)"></p>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Top Suppliers</h3>
                <table class="w-full text-sm">
                    <thead><tr><th class="text-left py-2 text-xs text-gray-500">Supplier</th><th class="text-center py-2 text-xs text-gray-500">Orders</th><th class="text-right py-2 text-xs text-gray-500">Total</th></tr></thead>
                    <tbody>
                        <template x-for="s in reportData.top_suppliers || []" :key="s.name">
                            <tr class="border-t border-gray-100 dark:border-gray-700">
                                <td class="py-2 text-gray-900 dark:text-white" x-text="s.name"></td>
                                <td class="py-2 text-center text-gray-600 dark:text-gray-300" x-text="s.purchase_count"></td>
                                <td class="py-2 text-right font-medium text-gray-900 dark:text-white" x-text="(Alpine.store('currency')?.symbol || '\$') + Number(s.total_amount).toFixed(2)"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ==================== REPORT: BY SUPPLIER ==================== --}}
        <div x-show="activeTab === 'report-suppliers'" x-cloak>
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Supplier</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Orders</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Total</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Avg Order</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <template x-if="reportSuppliersLoading"><tr><td colspan="4" class="px-6 py-12 text-center text-gray-400">Loading...</td></tr></template>
                            <template x-for="r in reportSuppliers" :key="r.supplier_id">
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                    <td class="px-6 py-4 text-gray-900 dark:text-white font-medium" x-text="r.supplier_name"></td>
                                    <td class="px-6 py-4 text-center text-gray-600 dark:text-gray-300" x-text="r.purchase_count"></td>
                                    <td class="px-6 py-4 text-right font-medium text-gray-900 dark:text-white" x-text="(Alpine.store('currency')?.symbol || '$')+Number(r.total_amount).toFixed(2)"></td>
                                    <td class="px-6 py-4 text-right text-gray-600 dark:text-gray-300" x-text="(Alpine.store('currency')?.symbol || '$')+Number(r.avg_order_value).toFixed(2)"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ==================== REPORT: BY PRODUCT ==================== --}}
        <div x-show="activeTab === 'report-products'" x-cloak>
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Product</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Qty</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Total Cost</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Avg Unit Cost</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <template x-if="reportProductsLoading"><tr><td colspan="4" class="px-6 py-12 text-center text-gray-400">Loading...</td></tr></template>
                            <template x-for="r in reportProducts" :key="r.product_id">
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                    <td class="px-6 py-4 text-gray-900 dark:text-white font-medium"><span x-text="r.product_name"></span> <span class="text-gray-400 text-xs font-mono" x-text="r.product_code"></span></td>
                                    <td class="px-6 py-4 text-center text-gray-600 dark:text-gray-300" x-text="Number(r.total_quantity).toFixed(2)"></td>
                                    <td class="px-6 py-4 text-right font-medium text-gray-900 dark:text-white" x-text="(Alpine.store('currency')?.symbol || '$')+Number(r.total_cost).toFixed(2)"></td>
                                    <td class="px-6 py-4 text-right text-gray-600 dark:text-gray-300" x-text="(Alpine.store('currency')?.symbol || '$')+Number(r.avg_unit_cost).toFixed(2)"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ==================== REPORT: MONTHLY ==================== --}}
        <div x-show="activeTab === 'report-monthly'" x-cloak>
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Month</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Orders</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <template x-if="reportMonthlyLoading"><tr><td colspan="3" class="px-6 py-12 text-center text-gray-400">Loading...</td></tr></template>
                            <template x-for="r in reportMonthly" :key="r.month">
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                    <td class="px-6 py-4 text-gray-900 dark:text-white font-medium" x-text="r.month"></td>
                                    <td class="px-6 py-4 text-center text-gray-600 dark:text-gray-300" x-text="r.purchase_count"></td>
                                    <td class="px-6 py-4 text-right font-medium text-gray-900 dark:text-white" x-text="(Alpine.store('currency')?.symbol || '$')+Number(r.total_amount).toFixed(2)"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ==================== REPORT: OUTSTANDING ==================== --}}
        <div x-show="activeTab === 'report-outstanding'" x-cloak>
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">PO#</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Supplier</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Date</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Grand Total</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Paid</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Due</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <template x-if="reportOutstandingLoading"><tr><td colspan="6" class="px-6 py-12 text-center text-gray-400">Loading...</td></tr></template>
                            <template x-if="!reportOutstandingLoading && reportOutstanding.length === 0"><tr><td colspan="6" class="px-6 py-12 text-center text-gray-400">All payments settled</td></tr></template>
                            <template x-for="po in reportOutstanding" :key="po.id">
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                    <td class="px-4 py-4 font-mono text-sm text-gray-900 dark:text-white" x-text="po.purchase_number"></td>
                                    <td class="px-4 py-4 text-gray-600 dark:text-gray-300" x-text="po.supplier?.name"></td>
                                    <td class="px-4 py-4 text-gray-600 dark:text-gray-300" x-text="po.purchase_date?.split('T')[0]"></td>
                                    <td class="px-4 py-4 text-right font-medium" x-text="(Alpine.store('currency')?.symbol || '$') + Number(po.grand_total).toFixed(2)"></td>
                                    <td class="px-4 py-4 text-right text-green-600" x-text="(Alpine.store('currency')?.symbol || '$')+Number(po.paid_amount).toFixed(2)"></td>
                                    <td class="px-4 py-4 text-right text-red-500 font-medium" x-text="(Alpine.store('currency')?.symbol || '$')+Number(po.due_amount).toFixed(2)"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    {{-- ==================== SUPPLIER MODAL ==================== --}}
    <div x-show="showSupplierModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click.self="showSupplierModal=false">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl w-full max-w-lg mx-4 max-h-[90vh] overflow-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white" x-text="supplierEditing ? 'Edit Supplier' : 'Add Supplier'"></h3>
                <button @click="showSupplierModal=false" class="text-gray-400 hover:text-gray-600">&times;</button>
            </div>
            <div class="p-6 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Name *</label>
                        <input x-model="supplierForm.name" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Code</label>
                        <input x-model="supplierForm.code" disabled placeholder="Auto-generated (SUP-0001)" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-100 dark:bg-gray-600 text-gray-500 dark:text-gray-400 text-sm cursor-not-allowed">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Phone</label>
                        <input x-model="supplierForm.phone_number" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                        <input x-model="supplierForm.email" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tax Number</label>
                        <input x-model="supplierForm.tax_number" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">City</label>
                        <input x-model="supplierForm.city" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Address</label>
                    <textarea x-model="supplierForm.address" rows="2" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm"></textarea>
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" x-model="supplierForm.is_enabled" id="supEnabled">
                    <label for="supEnabled" class="text-sm text-gray-700 dark:text-gray-300">Active</label>
                </div>
                <div x-show="supplierError" class="text-sm text-red-500" x-text="supplierError"></div>
            </div>
            <div class="flex justify-end gap-3 px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                <button @click="showSupplierModal=false" class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200">Cancel</button>
                <button @click="saveSupplier()" :disabled="supplierSaving" class="px-4 py-2 text-sm text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50" x-text="supplierSaving ? 'Saving...' : 'Save'"></button>
            </div>
        </div>
    </div>

    {{-- ==================== PURCHASE FORM MODAL ==================== --}}
    <div x-show="showPurchaseModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click.self="showPurchaseModal=false">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl w-full max-w-3xl mx-4 max-h-[90vh] flex flex-col">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700 shrink-0">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">New Purchase</h3>
                <button @click="showPurchaseModal=false" class="text-gray-400 hover:text-gray-600">&times;</button>
            </div>
            <div class="flex-1 overflow-auto p-6 space-y-4">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Supplier</label>
                        <select x-model="purchaseForm.supplier_id" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                            <option value="">Select Supplier</option>
                            <template x-for="s in supplierList" :key="s.id">
                                <option :value="s.id" x-text="s.name"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Warehouse</label>
                        <select x-model="purchaseForm.warehouse_id" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                            <option value="">Default</option>
                            <template x-for="w in warehouseList" :key="w.id">
                                <option :value="w.id" x-text="w.name"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Date *</label>
                        <input type="date" x-model="purchaseForm.purchase_date" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Ref Number <span class="text-xs text-gray-400">(optional)</span></label>
                        <input x-model="purchaseForm.reference_number" placeholder="Supplier invoice #" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                    </div>
                </div>

                {{-- Items --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Items</label>
                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-700/50">
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-400">Product</th>
                                    <th class="px-3 py-2 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 w-20">Qty</th>
                                    <th class="px-3 py-2 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 w-24">Cost</th>
                                    <th class="px-3 py-2 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 w-24">Total</th>
                                    <th class="px-3 py-2 w-10"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                <template x-for="(item, i) in purchaseForm.items" :key="i">
                                    <tr>
                                        <td class="px-3 py-2">
                                            <select x-model="item.product_id" @change="onPurchaseItemProductChange(i)" class="w-full px-2 py-1.5 border border-gray-200 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                                                <option value="">Select Product</option>
                                                <template x-for="p in productList" :key="p.id">
                                                    <option :value="p.id" x-text="p.name + ' (' + p.code + ')'"></option>
                                                </template>
                                            </select>
                                        </td>
                                        <td class="px-3 py-2"><input type="number" x-model.number="item.quantity" @input="calcPurchaseTotals()" min="0.01" step="0.01" class="w-full px-2 py-1.5 border border-gray-200 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm text-center"></td>
                                        <td class="px-3 py-2"><input type="number" x-model.number="item.unit_cost" @input="calcPurchaseTotals()" min="0" step="0.01" class="w-full px-2 py-1.5 border border-gray-200 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm text-right"></td>
                                        <td class="px-3 py-2 text-right text-gray-900 dark:text-white font-medium" x-text="(item.quantity * item.unit_cost).toFixed(2)"></td>
                                        <td class="px-3 py-2 text-center"><button @click="purchaseForm.items.splice(i,1); calcPurchaseTotals()" class="text-red-500 hover:text-red-700">&times;</button></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                    <button @click="purchaseForm.items.push({product_id:'',quantity:1,unit_cost:0,tax_id:null,discount:0,discount_type:0})" class="mt-2 text-sm text-blue-600 hover:text-blue-800">+ Add Item</button>
                </div>

                {{-- Totals --}}
                <div class="flex justify-end">
                    <div class="w-64 space-y-2 text-sm">
                        <div class="flex justify-between"><span class="text-gray-500">Subtotal:</span><span class="font-medium text-gray-900 dark:text-white" x-text="(Alpine.store('currency')?.symbol || '$') + purchaseSubtotal.toFixed(2)"></span></div>
                        <div class="flex justify-between items-center gap-2">
                            <span class="text-gray-500">Discount <span class="text-xs text-gray-400">(supplier)</span>:</span>
                            <div class="flex items-center gap-1">
                                <input type="number" x-model.number="purchaseForm.discount" @input="calcPurchaseTotals()" min="0" class="w-16 px-2 py-1 border border-gray-200 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm text-right">
                                <select x-model.number="purchaseForm.discount_type" class="w-14 px-1 py-1 border border-gray-200 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-xs">
                                    <option value="0">%</option>
                                    <option value="1">$</option>
                                </select>
                            </div>
                        </div>
                        <div class="flex justify-between"><span class="text-gray-500">Shipping:</span><input type="number" x-model.number="purchaseForm.shipping_cost" @input="calcPurchaseTotals()" min="0" class="w-20 px-2 py-1 border border-gray-200 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm text-right"></div>
                        <div class="flex justify-between border-t pt-2 font-semibold"><span>Grand Total:</span><span class="text-gray-900 dark:text-white" x-text="(Alpine.store('currency')?.symbol || '$') + purchaseGrandTotal.toFixed(2)"></span></div>
                    </div>
                </div>

                <div x-show="purchaseError" class="text-sm text-red-500" x-text="purchaseError"></div>
            </div>
            <div class="flex justify-end gap-3 px-6 py-4 border-t border-gray-200 dark:border-gray-700 shrink-0">
                <select x-model="purchaseForm.status" class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    <option value="pending">Save Pending</option>
                    <option value="ordered">Send Order</option>
                    <option value="received">Receive Now</option>
                </select>
                <button @click="showPurchaseModal=false" class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200">Cancel</button>
                <button @click="savePurchase()" :disabled="purchaseSaving" class="px-4 py-2 text-sm text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50" x-text="purchaseSaving ? 'Saving...' : 'Create Purchase'"></button>
            </div>
        </div>
    </div>

    {{-- ==================== RECEIVE MODAL ==================== --}}
    <div x-show="showReceiveModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click.self="showReceiveModal=false">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl w-full max-w-md mx-4">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Receive Items</h3>
                <button @click="showReceiveModal=false" class="text-gray-400 hover:text-gray-600">&times;</button>
            </div>
            <div class="p-6 space-y-3">
                <template x-for="item in receiveItems" :key="item.id">
                    <div class="flex items-center gap-3 py-2">
                        <span class="flex-1 text-sm text-gray-900 dark:text-white font-medium" x-text="item.product?.name || item.product_id"></span>
                        <span class="text-xs text-gray-500 text-right w-16" x-text="'Ordered: '+Number(item.quantity||0).toFixed(2)"></span>
                        <span class="text-xs text-blue-500 font-medium text-right w-20" x-text="'Received: '+Number(item.received_quantity||0).toFixed(2)"></span>
                        <input type="number" x-model.number="item.receive_qty" min="0" :max="Math.max(0, Number(item.quantity) - Number(item.received_quantity))" class="w-20 px-2 py-1.5 border border-gray-200 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm text-center">
                    </div>
                </template>
                <div x-show="receiveError" class="text-sm text-red-500" x-text="receiveError"></div>
            </div>
            <div class="flex justify-end gap-3 px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                <button @click="showReceiveModal=false" class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg">Cancel</button>
                <button @click="confirmReceive()" :disabled="receiveSaving" class="px-4 py-2 text-sm text-white bg-green-600 rounded-lg hover:bg-green-700 disabled:opacity-50" x-text="receiveSaving ? 'Saving...' : 'Confirm Receive'"></button>
            </div>
        </div>
    </div>

    {{-- ==================== RETURN FORM MODAL ==================== --}}
    <div x-show="showReturnModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click.self="showReturnModal=false">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl w-full max-w-lg mx-4">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">New Purchase Return</h3>
                <button @click="showReturnModal=false" class="text-gray-400 hover:text-gray-600">&times;</button>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Select Purchase</label>
                    <select x-model="returnForm.purchase_id" @change="loadReturnItems()" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                        <option value="">Choose a received purchase...</option>
                        <template x-for="rpo in returnablePurchases" :key="rpo.id">
                            <option :value="rpo.id" x-text="rpo.purchase_number + ' — ' + (rpo.supplier?.name || 'N/A')"></option>
                        </template>
                    </select>
                </div>

                <template x-if="returnItems.length > 0">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Items to Return</label>
                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                            <table class="w-full text-sm">
                                <thead class="bg-gray-50 dark:bg-gray-700/50">
                                    <tr>
                                        <th class="px-3 py-2 text-left text-xs text-gray-500">Product</th>
                                        <th class="px-3 py-2 text-center text-xs text-gray-500 w-20">Recv'd</th>
                                        <th class="px-3 py-2 text-center text-xs text-gray-500 w-20">Return</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                    <template x-for="(ritem, i) in returnItems" :key="ritem.id">
                                        <tr>
                                            <td class="px-3 py-2 text-gray-900 dark:text-white" x-text="ritem.product?.name || ritem.product_id"></td>
                                            <td class="px-3 py-2 text-center text-gray-500" x-text="Number(ritem.received_quantity).toFixed(2)"></td>
                                            <td class="px-3 py-2">
                                                <input type="number" x-model.number="ritem.return_qty" min="0" :max="Number(ritem.received_quantity)" class="w-20 px-2 py-1.5 border border-gray-200 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm text-center">
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </template>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Return Date</label>
                    <input type="date" x-model="returnForm.return_date" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Reason</label>
                    <textarea x-model="returnForm.reason" rows="2" placeholder="Damaged, wrong item, etc." class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm"></textarea>
                </div>

                <div x-show="returnError" class="text-sm text-red-500" x-text="returnError"></div>
            </div>
            <div class="flex justify-end gap-3 px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                <button @click="showReturnModal=false" class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg">Cancel</button>
                <button @click="saveReturn()" :disabled="returnSaving" class="px-4 py-2 text-sm text-white bg-red-600 rounded-lg hover:bg-red-700 disabled:opacity-50" x-text="returnSaving ? 'Processing...' : 'Submit Return'"></button>
            </div>
        </div>
    </div>
</div>
@endsection
