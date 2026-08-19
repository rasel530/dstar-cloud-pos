@extends('layouts.app')
@section('title', 'Income & Expenses')
@section('content')
<div x-data="incomeExpensesManager" class="h-full flex flex-col">
    {{-- Header --}}
    <div class="flex flex-wrap items-center justify-between gap-4 px-4 lg:px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">
        <div>
            <h1 class="text-lg font-bold text-gray-900 dark:text-white">Income & Expenses</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Track income, expenses, categories & reports</p>
        </div>
        <div class="flex gap-2" x-show="activeTab === 'income'">
            <button @click="openForm('income')" class="px-4 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700 transition-colors">Add Income</button>
            <button @click="openSyncModal()" :disabled="syncing" class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50">Sync POS Sales</button>
        </div>
        <div class="flex gap-2" x-show="activeTab === 'expenses'">
            <button @click="openForm('expense')" class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition-colors">Add Expense</button>
        </div>
        <div class="flex gap-2" x-show="activeTab === 'categories'">
            <button @click="openCategoryForm('income')" class="px-4 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700 transition-colors">Add Income Category</button>
            <button @click="openCategoryForm('expense')" class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition-colors">Add Expense Category</button>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="flex gap-1 px-4 lg:px-6 pt-3 pb-2 bg-white dark:bg-gray-800 overflow-x-auto">
        <button @click="switchTab('dashboard')" :class="activeTab==='dashboard'?'bg-blue-500 text-white':'bg-gray-100 dark:bg-[#1a1f3d] text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-white/5'" class="shrink-0 px-3 py-2 rounded-lg text-sm font-medium transition whitespace-nowrap">Dashboard</button>
        <button @click="switchTab('income')" :class="activeTab==='income'?'bg-blue-500 text-white':'bg-gray-100 dark:bg-[#1a1f3d] text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-white/5'" class="shrink-0 px-3 py-2 rounded-lg text-sm font-medium transition whitespace-nowrap">Income</button>
        <button @click="switchTab('expenses')" :class="activeTab==='expenses'?'bg-blue-500 text-white':'bg-gray-100 dark:bg-[#1a1f3d] text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-white/5'" class="shrink-0 px-3 py-2 rounded-lg text-sm font-medium transition whitespace-nowrap">Expenses</button>
        <button @click="switchTab('categories')" :class="activeTab==='categories'?'bg-blue-500 text-white':'bg-gray-100 dark:bg-[#1a1f3d] text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-white/5'" class="shrink-0 px-3 py-2 rounded-lg text-sm font-medium transition whitespace-nowrap">Categories</button>
        <button @click="switchTab('reports')" :class="activeTab==='reports'?'bg-blue-500 text-white':'bg-gray-100 dark:bg-[#1a1f3d] text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-white/5'" class="shrink-0 px-3 py-2 rounded-lg text-sm font-medium transition whitespace-nowrap">Reports</button>
    </div>

    {{-- Content Area --}}
    <div class="flex-1 overflow-auto p-4 lg:p-6 bg-gray-50 dark:bg-gray-900">

        {{-- ==================== DASHBOARD TAB ==================== --}}
        <div x-show="activeTab === 'dashboard'" x-cloak>
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-4 mb-6">
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase font-semibold">Total Income</p>
                    <p class="text-2xl font-bold text-emerald-600 mt-1" x-text="(Alpine.store('currency')?.symbol || '$') + Number(dashboard.summary?.total_income || 0).toFixed(2)"></p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase font-semibold">Total Expenses</p>
                    <p class="text-2xl font-bold text-red-600 mt-1" x-text="(Alpine.store('currency')?.symbol || '$') + Number(dashboard.summary?.total_expense || 0).toFixed(2)"></p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase font-semibold">Net Profit/Loss</p>
                    <p class="text-2xl font-bold mt-1" :class="(dashboard.summary?.total_income - dashboard.summary?.total_expense) >= 0 ? 'text-emerald-600' : 'text-red-600'" x-text="(Alpine.store('currency')?.symbol || '$') + Number((dashboard.summary?.total_income || 0) - (dashboard.summary?.total_expense || 0)).toFixed(2)"></p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase font-semibold">Total Entries</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1" x-text="dashboard.summary?.total_count || 0"></p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase font-semibold">Customer Due</p>
                    <p class="text-2xl font-bold text-rose-600 mt-1" x-text="(Alpine.store('currency')?.symbol || '$') + Number(dashboard.due_summary?.total_due || 0).toFixed(2)"></p>
                    <p class="text-xs text-gray-400 mt-1" x-text="(dashboard.due_summary?.customers || 0) + ' customer(s) with due'"></p>
                </div>
            </div>

            {{-- Monthly Chart --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5 mb-6">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Monthly Overview ({{ date('Y') }})</h3>
                <div x-show="!chartLoading" class="flex items-end justify-between gap-1 h-48" x-ref="chartContainer">
                    <template x-for="(m, i) in monthlyData" :key="i">
                        <div class="flex-1 flex flex-col items-center gap-1 min-w-0">
                            <div class="flex flex-col items-center gap-0.5 w-full">
                                <div class="w-full bg-emerald-500 rounded-t-sm" :style="'height: ' + (m.income_total > 0 ? Math.max((m.income_total / chartMax) * 140, 2) : 0) + 'px'"></div>
                                <div class="w-full bg-red-500 rounded-b-sm" :style="'height: ' + (m.expense_total > 0 ? Math.max((m.expense_total / chartMax) * 140, 2) : 0) + 'px'"></div>
                            </div>
                            <span class="text-[10px] text-gray-400" x-text="m.month?.substring(5)"></span>
                        </div>
                    </template>
                </div>
                <div x-show="chartLoading" class="flex items-center justify-center h-48 text-gray-400">Loading...</div>
                <div class="flex items-center justify-center gap-6 mt-3 text-xs">
                    <span class="flex items-center gap-1"><span class="w-3 h-3 bg-emerald-500 rounded-sm"></span> Income</span>
                    <span class="flex items-center gap-1"><span class="w-3 h-3 bg-red-500 rounded-sm"></span> Expense</span>
                </div>
            </div>

            {{-- Top Categories --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
                    <h3 class="text-sm font-semibold text-emerald-600 mb-3">Top Income Categories</h3>
                    <template x-for="c in dashboard.top_income_categories || []" :key="c.id">
                        <div class="flex items-center justify-between py-1.5 border-b border-gray-100 dark:border-gray-700 last:border-0">
                            <span class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                                <span class="w-2.5 h-2.5 rounded-full" :style="'background-color: ' + c.color"></span>
                                <span x-text="c.name"></span>
                            </span>
                            <span class="text-sm font-medium text-gray-900 dark:text-white" x-text="(Alpine.store('currency')?.symbol || '$') + Number(c.total_amount).toFixed(2)"></span>
                        </div>
                    </template>
                    <div x-show="!(dashboard.top_income_categories || []).length" class="text-sm text-gray-400 py-4 text-center">No income data yet</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
                    <h3 class="text-sm font-semibold text-red-600 mb-3">Top Expense Categories</h3>
                    <template x-for="c in dashboard.top_expense_categories || []" :key="c.id">
                        <div class="flex items-center justify-between py-1.5 border-b border-gray-100 dark:border-gray-700 last:border-0">
                            <span class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                                <span class="w-2.5 h-2.5 rounded-full" :style="'background-color: ' + c.color"></span>
                                <span x-text="c.name"></span>
                            </span>
                            <span class="text-sm font-medium text-gray-900 dark:text-white" x-text="(Alpine.store('currency')?.symbol || '$') + Number(c.total_amount).toFixed(2)"></span>
                        </div>
                    </template>
                    <div x-show="!(dashboard.top_expense_categories || []).length" class="text-sm text-gray-400 py-4 text-center">No expense data yet</div>
                </div>
            </div>

            {{-- Recent Entries --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Recent Entries</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-400">Ref#</th>
                                <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-400">Type</th>
                                <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-400">Category</th>
                                <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-400">Description</th>
                                <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-400">Date</th>
                                <th class="px-4 py-2 text-right text-xs font-semibold text-gray-500 dark:text-gray-400">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <template x-for="e in dashboard.recent_entries || []" :key="e.id">
                                <tr>
                                    <td class="px-4 py-2 text-gray-900 dark:text-white font-mono text-xs" x-text="e.reference_number"></td>
                                    <td class="px-4 py-2"><span class="px-2 py-0.5 rounded text-xs font-medium" :class="e.type==='income'?'bg-emerald-100 text-emerald-700':'bg-red-100 text-red-700'" x-text="e.type"></span></td>
                                    <td class="px-4 py-2 text-gray-600 dark:text-gray-300" x-text="e.category?.name"></td>
                                    <td class="px-4 py-2 text-gray-600 dark:text-gray-300 truncate max-w-[200px]" :title="e.description || '—'" x-text="e.description || '—'"></td>
                                    <td class="px-4 py-2 text-gray-600 dark:text-gray-300" x-text="e.date?.split('T')[0]"></td>
                                    <td class="px-4 py-2 text-right font-medium" :class="e.type==='income'?'text-emerald-600':'text-red-600'" x-text="(Alpine.store('currency')?.symbol || '$') + Number(e.amount).toFixed(2)"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ==================== INCOME TAB ==================== --}}
        <div x-show="activeTab === 'income'" x-cloak>
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex flex-wrap gap-3 items-center">
                    <input type="text" x-model="filterSearch" @input.debounce.300ms="fetchEntries()" placeholder="Search ref# or description..." class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white w-full max-w-xs">
                    <input type="date" x-model="filterDateFrom" @change="fetchEntries()" class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    <span class="text-gray-400 text-sm">to</span>
                    <input type="date" x-model="filterDateTo" @change="fetchEntries()" class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    <select x-model="filterCategoryId" @change="fetchEntries()" class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        <option value="">All Categories</option>
                        <template x-for="c in categories.income" :key="c.id">
                            <option :value="c.id" x-text="c.name"></option>
                        </template>
                    </select>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Ref#</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Date</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Category</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Description</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Payment</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Amount</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <template x-if="entriesLoading"><tr><td colspan="7" class="px-6 py-12 text-center text-gray-400">Loading...</td></tr></template>
                            <template x-if="!entriesLoading && entries.length === 0"><tr><td colspan="7" class="px-6 py-12 text-center text-gray-400">No income entries found</td></tr></template>
                            <template x-for="e in entries" :key="e.id">
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                    <td class="px-4 py-4 font-mono text-sm font-medium text-gray-900 dark:text-white" x-text="e.reference_number"></td>
                                <td class="px-4 py-4 text-gray-600 dark:text-gray-300" x-text="e.date?.split('T')[0]"></td>
                                <td class="px-4 py-4">
                                    <span class="inline-flex items-center gap-1.5">
                                        <span class="w-2 h-2 rounded-full" :style="'background-color: ' + (e.category?.color || '#6b7280')"></span>
                                        <span class="text-gray-600 dark:text-gray-300" x-text="e.category?.name"></span>
                                    </span>
                                </td>
                                <td class="px-4 py-4 text-gray-600 dark:text-gray-300 truncate max-w-[200px]" :title="e.description || '—'" x-text="e.description || '—'"></td>
                                <td class="px-4 py-4 text-gray-600 dark:text-gray-300" x-text="e.payment_method || '—'"></td>
                                <td class="px-4 py-4 text-right font-medium text-emerald-600" x-text="(Alpine.store('currency')?.symbol || '$') + Number(e.amount).toFixed(2)"></td>
                                <td class="px-4 py-4 text-right space-x-2">
                                    <button @click="editEntry(e)" class="text-blue-600 hover:text-blue-800 text-xs font-medium">Edit</button>
                                    <button @click="deleteEntry(e.id)" class="text-red-600 hover:text-red-800 text-xs font-medium">Delete</button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
                <div x-show="entriesPage.last_page > 1" class="flex justify-center gap-2 p-4 border-t border-gray-200 dark:border-gray-700">
                    <button @click="fetchEntries(entriesPage.current_page-1)" :disabled="entriesPage.current_page<=1" class="px-3 py-1.5 text-sm border rounded-lg disabled:opacity-40">Prev</button>
                    <span class="px-3 py-1.5 text-sm text-gray-500" x-text="entriesPage.current_page + ' / ' + entriesPage.last_page"></span>
                    <button @click="fetchEntries(entriesPage.current_page+1)" :disabled="entriesPage.current_page>=entriesPage.last_page" class="px-3 py-1.5 text-sm border rounded-lg disabled:opacity-40">Next</button>
                </div>
            </div>
        </div>

        {{-- ==================== EXPENSES TAB ==================== --}}
        <div x-show="activeTab === 'expenses'" x-cloak>
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex flex-wrap gap-3 items-center">
                    <input type="text" x-model="filterSearch" @input.debounce.300ms="fetchEntries()" placeholder="Search ref# or description..." class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white w-full max-w-xs">
                    <input type="date" x-model="filterDateFrom" @change="fetchEntries()" class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    <span class="text-gray-400 text-sm">to</span>
                    <input type="date" x-model="filterDateTo" @change="fetchEntries()" class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    <select x-model="filterCategoryId" @change="fetchEntries()" class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        <option value="">All Categories</option>
                        <template x-for="c in categories.expense" :key="c.id">
                            <option :value="c.id" x-text="c.name"></option>
                        </template>
                    </select>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Ref#</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Date</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Category</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Description</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Payment</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Amount</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <template x-if="entriesLoading"><tr><td colspan="7" class="px-6 py-12 text-center text-gray-400">Loading...</td></tr></template>
                            <template x-if="!entriesLoading && entries.length === 0"><tr><td colspan="7" class="px-6 py-12 text-center text-gray-400">No expense entries found</td></tr></template>
                            <template x-for="e in entries" :key="e.id">
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                    <td class="px-4 py-4 font-mono text-sm font-medium text-gray-900 dark:text-white" x-text="e.reference_number"></td>
                                    <td class="px-4 py-4 text-gray-600 dark:text-gray-300" x-text="e.date?.split('T')[0]"></td>
                                    <td class="px-4 py-4">
                                        <span class="inline-flex items-center gap-1.5">
                                            <span class="w-2 h-2 rounded-full" :style="'background-color: ' + (e.category?.color || '#6b7280')"></span>
                                            <span class="text-gray-600 dark:text-gray-300" x-text="e.category?.name"></span>
                                        </span>
                                    </td>
                                    <td class="px-4 py-4 text-gray-600 dark:text-gray-300 truncate max-w-[200px]" :title="e.description || '—'" x-text="e.description || '—'"></td>
                                    <td class="px-4 py-4 text-gray-600 dark:text-gray-300" x-text="e.payment_method || '—'"></td>
                                    <td class="px-4 py-4 text-right font-medium text-red-600" x-text="(Alpine.store('currency')?.symbol || '$') + Number(e.amount).toFixed(2)"></td>
                                    <td class="px-4 py-4 text-right space-x-2">
                                        <button @click="editEntry(e)" class="text-blue-600 hover:text-blue-800 text-xs font-medium">Edit</button>
                                        <button @click="deleteEntry(e.id)" class="text-red-600 hover:text-red-800 text-xs font-medium">Delete</button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
                <div x-show="entriesPage.last_page > 1" class="flex justify-center gap-2 p-4 border-t border-gray-200 dark:border-gray-700">
                    <button @click="fetchEntries(entriesPage.current_page-1)" :disabled="entriesPage.current_page<=1" class="px-3 py-1.5 text-sm border rounded-lg disabled:opacity-40">Prev</button>
                    <span class="px-3 py-1.5 text-sm text-gray-500" x-text="entriesPage.current_page + ' / ' + entriesPage.last_page"></span>
                    <button @click="fetchEntries(entriesPage.current_page+1)" :disabled="entriesPage.current_page>=entriesPage.last_page" class="px-3 py-1.5 text-sm border rounded-lg disabled:opacity-40">Next</button>
                </div>
            </div>
        </div>

        {{-- ==================== CATEGORIES TAB ==================== --}}
        <div x-show="activeTab === 'categories'" x-cloak>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Income Categories --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 bg-emerald-50 dark:bg-emerald-500/10">
                        <h3 class="text-sm font-semibold text-emerald-700 dark:text-emerald-400">Income Categories</h3>
                    </div>
                    <div class="divide-y divide-gray-200 dark:divide-gray-700">
                        <template x-for="c in categories.income" :key="c.id">
                            <div class="flex items-center justify-between px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                <span class="flex items-center gap-2 text-sm text-gray-900 dark:text-white">
                                    <span class="w-3 h-3 rounded-full" :style="'background-color: ' + c.color"></span>
                                    <span x-text="c.name"></span>
                                    <span x-show="!c.is_enabled" class="text-xs text-red-400">(disabled)</span>
                                </span>
                                <div class="flex gap-2">
                                    <button @click="editCategory(c)" class="text-blue-600 hover:text-blue-800 text-xs font-medium">Edit</button>
                                    <button @click="deleteCategory(c.id)" class="text-red-600 hover:text-red-800 text-xs font-medium">Delete</button>
                                </div>
                            </div>
                        </template>
                        <div x-show="!categories.income.length" class="px-4 py-8 text-center text-sm text-gray-400">No income categories</div>
                    </div>
                </div>
                {{-- Expense Categories --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 bg-red-50 dark:bg-red-500/10">
                        <h3 class="text-sm font-semibold text-red-700 dark:text-red-400">Expense Categories</h3>
                    </div>
                    <div class="divide-y divide-gray-200 dark:divide-gray-700">
                        <template x-for="c in categories.expense" :key="c.id">
                            <div class="flex items-center justify-between px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                <span class="flex items-center gap-2 text-sm text-gray-900 dark:text-white">
                                    <span class="w-3 h-3 rounded-full" :style="'background-color: ' + c.color"></span>
                                    <span x-text="c.name"></span>
                                    <span x-show="!c.is_enabled" class="text-xs text-red-400">(disabled)</span>
                                </span>
                                <div class="flex gap-2">
                                    <button @click="editCategory(c)" class="text-blue-600 hover:text-blue-800 text-xs font-medium">Edit</button>
                                    <button @click="deleteCategory(c.id)" class="text-red-600 hover:text-red-800 text-xs font-medium">Delete</button>
                                </div>
                            </div>
                        </template>
                        <div x-show="!categories.expense.length" class="px-4 py-8 text-center text-sm text-gray-400">No expense categories</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ==================== REPORTS TAB ==================== --}}
        <div x-show="activeTab === 'reports'" x-cloak>
            <div class="flex flex-wrap items-center gap-3 mb-4">
                <input type="date" x-model="reportDateFrom" class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                <span class="text-gray-400 text-sm">to</span>
                <input type="date" x-model="reportDateTo" class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                <button @click="fetchReports()" class="px-4 py-2 text-sm text-white bg-blue-600 rounded-lg hover:bg-blue-700">Apply</button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase font-semibold">Total Income</p>
                    <p class="text-xl font-bold text-emerald-600 mt-1" x-text="(Alpine.store('currency')?.symbol || '$') + Number(reportData.summary?.total_income || 0).toFixed(2)"></p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase font-semibold">Total Expenses</p>
                    <p class="text-xl font-bold text-red-600 mt-1" x-text="(Alpine.store('currency')?.symbol || '$') + Number(reportData.summary?.total_expense || 0).toFixed(2)"></p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase font-semibold">Net</p>
                    <p class="text-xl font-bold mt-1" :class="(reportData.summary?.total_income - reportData.summary?.total_expense) >= 0 ? 'text-emerald-600' : 'text-red-600'" x-text="(Alpine.store('currency')?.symbol || '$') + Number((reportData.summary?.total_income || 0) - (reportData.summary?.total_expense || 0)).toFixed(2)"></p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="px-4 py-3 border-b bg-emerald-50 dark:bg-emerald-500/10">
                        <h3 class="text-sm font-semibold text-emerald-700">Income by Category</h3>
                    </div>
                    <table class="w-full text-sm">
                        <thead><tr><th class="px-4 py-2 text-left text-xs text-gray-500">Category</th><th class="px-4 py-2 text-right text-xs text-gray-500">Amount</th><th class="px-4 py-2 text-right text-xs text-gray-500">%</th></tr></thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            <template x-for="c in reportData.top_income_categories || []" :key="c.id">
                                <tr>
                                    <td class="px-4 py-2 text-sm"><span class="inline-flex items-center gap-2"><span class="w-2 h-2 rounded-full" :style="'background-color: '+c.color"></span> <span x-text="c.name"></span></span></td>
                                    <td class="px-4 py-2 text-right text-sm font-medium text-gray-900 dark:text-white" x-text="(Alpine.store('currency')?.symbol || '$') + Number(c.total_amount).toFixed(2)"></td>
                                    <td class="px-4 py-2 text-right text-xs text-gray-400" x-text="c.entry_count + ' entries'"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="px-4 py-3 border-b bg-red-50 dark:bg-red-500/10">
                        <h3 class="text-sm font-semibold text-red-700">Expense by Category</h3>
                    </div>
                    <table class="w-full text-sm">
                        <thead><tr><th class="px-4 py-2 text-left text-xs text-gray-500">Category</th><th class="px-4 py-2 text-right text-xs text-gray-500">Amount</th><th class="px-4 py-2 text-right text-xs text-gray-500">%</th></tr></thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            <template x-for="c in reportData.top_expense_categories || []" :key="c.id">
                                <tr>
                                    <td class="px-4 py-2 text-sm"><span class="inline-flex items-center gap-2"><span class="w-2 h-2 rounded-full" :style="'background-color: '+c.color"></span> <span x-text="c.name"></span></span></td>
                                    <td class="px-4 py-2 text-right text-sm font-medium text-gray-900 dark:text-white" x-text="(Alpine.store('currency')?.symbol || '$') + Number(c.total_amount).toFixed(2)"></td>
                                    <td class="px-4 py-2 text-right text-xs text-gray-400" x-text="c.entry_count + ' entries'"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    {{-- ==================== ENTRY FORM MODAL ==================== --}}
    <div x-show="showEntryModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click.self="showEntryModal=false">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl w-full max-w-md mx-4">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white" x-text="editing ? 'Edit Entry' : (entryForm.type === 'income' ? 'Add Income' : 'Add Expense')"></h3>
                <button @click="showEntryModal=false" class="text-gray-400 hover:text-gray-600 text-xl">&times;</button>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Category *</label>
                    <select x-model="entryForm.category_id" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                        <option value="">Select Category</option>
                        <template x-for="c in (entryForm.type === 'income' ? categories.income : categories.expense)" :key="c.id">
                            <option :value="c.id" x-text="c.name"></option>
                        </template>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Amount *</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm" x-text="Alpine.store('currency')?.symbol || '$'"></span>
                        <input type="number" x-model="entryForm.amount" step="0.01" min="0.01" class="w-full pl-8 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Date *</label>
                    <input type="date" x-model="entryForm.date" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Payment Method</label>
                    <select x-model="entryForm.payment_method" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                        <option value="">Select</option>
                        <template x-for="pm in paymentMethods" :key="pm.name">
                            <option :value="pm.name" x-text="pm.name"></option>
                        </template>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                    <textarea x-model="entryForm.description" rows="2" placeholder="Optional notes..." class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm"></textarea>
                </div>
                <div x-show="entryError" class="text-sm text-red-500" x-text="entryError"></div>
            </div>
            <div class="flex justify-end gap-3 px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                <button @click="showEntryModal=false" class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg">Cancel</button>
                <button @click="saveEntry()" :disabled="entrySaving" class="px-4 py-2 text-sm text-white rounded-lg disabled:opacity-50" :class="entryForm.type==='income' ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-red-600 hover:bg-red-700'" x-text="entrySaving ? 'Saving...' : 'Save'"></button>
            </div>
        </div>
    </div>

    {{-- ==================== CATEGORY FORM MODAL ==================== --}}
    <div x-show="showCategoryModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click.self="showCategoryModal=false">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl w-full max-w-sm mx-4">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white" x-text="categoryEditing ? 'Edit Category' : (categoryForm.type === 'income' ? 'Add Income Category' : 'Add Expense Category')"></h3>
                <button @click="showCategoryModal=false" class="text-gray-400 hover:text-gray-600 text-xl">&times;</button>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Name *</label>
                    <input type="text" x-model="categoryForm.name" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Color</label>
                    <div class="flex gap-2 flex-wrap">
                        <template x-for="color in ['#10b981','#3b82f6','#8b5cf6','#f59e0b','#ef4444','#f97316','#06b6d4','#ec4899','#6366f1','#14b8a6','#78716c','#6b7280']" :key="color">
                            <button type="button" @click="categoryForm.color = color" class="w-7 h-7 rounded-full border-2 transition-colors" :class="categoryForm.color === color ? 'border-gray-900 dark:border-white scale-110' : 'border-transparent'" :style="'background-color: ' + color"></button>
                        </template>
                    </div>
                </div>
                <div x-show="categoryError" class="text-sm text-red-500" x-text="categoryError"></div>
            </div>
            <div class="flex justify-end gap-3 px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                <button @click="showCategoryModal=false" class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg">Cancel</button>
                <button @click="saveCategory()" :disabled="categorySaving" class="px-4 py-2 text-sm text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50" x-text="categorySaving ? 'Saving...' : 'Save'"></button>
            </div>
        </div>
    </div>

    {{-- ==================== SYNC POS SALES MODAL ==================== --}}
    <div x-show="showSyncModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click.self="showSyncModal=false">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl w-full max-w-sm mx-4">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Sync POS Sales</h3>
                <button @click="showSyncModal=false" class="text-gray-400 hover:text-gray-600 text-xl">&times;</button>
            </div>
            <div class="p-6 space-y-4">
                <p class="text-sm text-gray-500 dark:text-gray-400">Select date range to import completed POS sales as income entries.</p>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">From</label>
                    <input type="date" x-model="syncDateFrom" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">To</label>
                    <input type="date" x-model="syncDateTo" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                </div>
                <div x-show="syncError" class="text-sm text-red-500" x-text="syncError"></div>
            </div>
            <div class="flex justify-end gap-3 px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                <button @click="showSyncModal=false" class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg">Cancel</button>
                <button @click="syncPosSales()" :disabled="syncing" class="px-4 py-2 text-sm text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50" x-text="syncing ? 'Syncing...' : 'Sync Now'"></button>
            </div>
        </div>
    </div>
</div>
@endsection
