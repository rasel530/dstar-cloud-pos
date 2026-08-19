@extends('layouts.app')

@section('title', 'Customers')

@section('content')
<div x-data="customersManager" class="flex flex-col h-full">
    <div class="flex flex-wrap items-center justify-between gap-3 px-4 sm:px-6 py-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Customers</h1>
            <p class="text-gray-500 dark:text-gray-400 text-sm mt-1">Manage your customer records</p>
        </div>
        <button @click="openAdd()" class="bg-blue-500 hover:bg-blue-600 text-white text-sm font-medium px-4 py-2 rounded-lg transition flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Add Customer
        </button>
    </div>

    <div class="px-4 sm:px-6">
        <div class="bg-white dark:bg-[#1a1f3d] rounded-xl border border-gray-100 dark:border-white/5 mb-6">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-white/5 flex flex-col sm:flex-row gap-4 sm:items-center sm:justify-between">
                <div class="relative flex-1 max-w-md">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 dark:text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input
                        type="text"
                        x-model="search"
                        @input.debounce.300ms="fetchCustomers()"
                        placeholder="Search customers..."
                        class="w-full bg-gray-50 dark:bg-[#0f1535] border border-gray-300 dark:border-white/10 rounded-lg pl-10 pr-4 py-2 text-sm text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                    >
                </div>
                <span class="text-xs text-gray-500 dark:text-white/50" x-text="'Showing ' + customers.length + ' results'"></span>
            </div>

            <div x-show="loading" class="flex justify-center py-12">
                <svg class="animate-spin h-6 w-6 text-gray-400 dark:text-white/30" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>

            <template x-if="!loading && customers.length">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-100 dark:border-white/5">
                                <th class="text-left text-xs font-medium text-gray-500 dark:text-white/50 uppercase tracking-wider px-6 py-3">Name</th>
                                <th class="text-left text-xs font-medium text-gray-500 dark:text-white/50 uppercase tracking-wider px-6 py-3">Code</th>
                                <th class="text-left text-xs font-medium text-gray-500 dark:text-white/50 uppercase tracking-wider px-6 py-3">Phone</th>
                                <th class="text-left text-xs font-medium text-gray-500 dark:text-white/50 uppercase tracking-wider px-6 py-3">Email</th>
                                <th class="text-right text-xs font-medium text-gray-500 dark:text-white/50 uppercase tracking-wider px-6 py-3">Loyalty Pts</th>
                                <th class="text-right text-xs font-medium text-gray-500 dark:text-white/50 uppercase tracking-wider px-6 py-3">Balance</th>
                                <th class="text-center text-xs font-medium text-gray-500 dark:text-white/50 uppercase tracking-wider px-6 py-3">Status</th>
                                <th class="text-right text-xs font-medium text-gray-500 dark:text-white/50 uppercase tracking-wider px-6 py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="customer in customers" :key="customer.id">
                                <tr class="border-b border-gray-100 dark:border-white/5 hover:bg-gray-50 dark:hover:bg-white/5 transition">
                                    <td class="px-6 py-3 font-medium text-gray-900 dark:text-white" x-text="customer.name"></td>
                                    <td class="px-6 py-3 font-mono text-xs text-gray-500 dark:text-white/70" x-text="customer.code || '--'"></td>
                                    <td class="px-6 py-3 text-gray-500 dark:text-white/70" x-text="customer.phone_number || '--'"></td>
                                    <td class="px-6 py-3 text-gray-500 dark:text-white/70" x-text="customer.email || '--'"></td>
                                    <td class="px-6 py-3 text-right">
                                        <span
                                            class="inline-block px-2 py-0.5 rounded-full text-xs font-medium"
                                            :class="{
                                                'bg-yellow-500/10 text-yellow-400': customer.loyalty_points >= 500,
                                                'bg-blue-500/10 text-blue-400': customer.loyalty_points >= 100 && customer.loyalty_points < 500,
                                                'bg-gray-100 dark:bg-white/5 text-gray-400 dark:text-white/50': customer.loyalty_points < 100,
                                            }"
                                            x-text="customer.loyalty_points || 0"
                                        ></span>
                                    </td>
                                    <td class="px-6 py-3 text-right font-semibold" :class="parseFloat(customer.outstanding_balance || 0) > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-gray-400 dark:text-white/50'" x-text="parseFloat(customer.outstanding_balance || 0) > 0 ? formatMoney(customer.outstanding_balance) : '—'"></td>
                                    <td class="px-6 py-3 text-center">
                                        <button
                                            @click="toggleStatus(customer)"
                                            class="relative inline-flex h-5 w-9 items-center rounded-full transition"
                                            :class="customer.is_enabled ? 'bg-blue-500' : 'bg-gray-200 dark:bg-white/10'"
                                        >
                                            <span
                                                class="inline-block h-3.5 w-3.5 transform rounded-full bg-white transition shadow-sm"
                                                :class="customer.is_enabled ? 'translate-x-[18px]' : 'translate-x-[3px]'"
                                            ></span>
                                        </button>
                                    </td>
                                    <td class="px-6 py-3 text-right whitespace-nowrap">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <button @click="openPayment(customer)" :disabled="parseFloat(customer.outstanding_balance || 0) <= 0" :title="parseFloat(customer.outstanding_balance || 0) > 0 ? 'Add Payment' : 'No outstanding balance'" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-medium border border-transparent transition whitespace-nowrap" :class="parseFloat(customer.outstanding_balance || 0) > 0 ? 'bg-emerald-50 text-emerald-600 hover:bg-emerald-100 dark:bg-emerald-500/10 dark:text-emerald-400 dark:hover:bg-emerald-500/20' : 'bg-gray-50 text-gray-300 dark:bg-white/5 dark:text-white/20 cursor-not-allowed'">
                                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z"/></svg>
                                                <span>Pay</span>
                                            </button>
                                            <button @click="openStatement(customer)" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-medium bg-blue-50 text-blue-600 hover:bg-blue-100 dark:bg-blue-500/10 dark:text-blue-400 dark:hover:bg-blue-500/20 border border-transparent transition whitespace-nowrap" title="Statement">
                                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                                <span>Statement</span>
                                            </button>
                                            <button @click="openEdit(customer)" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-medium bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-white/10 dark:text-gray-200 dark:hover:bg-white/15 border border-transparent transition whitespace-nowrap" title="Edit">
                                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                                <span>Edit</span>
                                            </button>
                                            <button @click="deleteCustomer(customer.id)" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-medium bg-rose-50 text-rose-600 hover:bg-rose-100 dark:bg-rose-500/10 dark:text-rose-400 dark:hover:bg-rose-500/20 border border-transparent transition whitespace-nowrap" title="Delete">
                                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                                <span>Delete</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </template>

            <template x-if="!loading && !customers.length">
                <div class="text-center py-16 text-gray-400 dark:text-white/30">
                    <svg class="w-12 h-12 mx-auto mb-3 text-gray-300 dark:text-white/20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <p class="text-sm" x-text="search ? 'No customers match your search.' : 'No customers yet. Add your first customer.'"></p>
                </div>
            </template>

            <div x-show="pagination && pagination.last_page > 1" class="px-6 py-3 border-t border-gray-100 dark:border-white/5 flex items-center justify-between">
                <span class="text-xs text-gray-500 dark:text-white/50" x-text="'Page ' + pagination.current_page + ' of ' + pagination.last_page"></span>
                <div class="flex gap-1">
                    <button
                        @click="goToPage(pagination.current_page - 1)"
                        :disabled="pagination.current_page <= 1"
                        class="px-3 py-1 text-xs rounded-lg disabled:opacity-30 bg-gray-100 dark:bg-white/5 text-gray-900 dark:text-white hover:bg-gray-200 dark:hover:bg-white/10 transition disabled:cursor-not-allowed"
                    >Prev</button>
                    <button
                        @click="goToPage(pagination.current_page + 1)"
                        :disabled="pagination.current_page >= pagination.last_page"
                        class="px-3 py-1 text-xs rounded-lg disabled:opacity-30 bg-gray-100 dark:bg-white/5 text-gray-900 dark:text-white hover:bg-gray-200 dark:hover:bg-white/10 transition disabled:cursor-not-allowed"
                    >Next</button>
                </div>
            </div>
        </div>
    </div>

    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" @click.self="showModal = false">
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm"></div>
        <div class="relative bg-white dark:bg-[#1a1f3d] rounded-xl w-full max-w-lg border border-gray-200 dark:border-white/10 shadow-2xl max-h-[85vh] overflow-y-auto" @click.stop>
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/5">
                <h3 class="font-semibold text-gray-900 dark:text-white" x-text="editing ? 'Edit Customer' : 'Add Customer'"></h3>
                <button @click="showModal = false" class="p-1 rounded-lg hover:bg-gray-100 dark:hover:bg-white/5 text-gray-400 dark:text-white/50 hover:text-gray-600 dark:hover:text-white transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <form @submit.prevent="save()" class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Name <span class="text-red-500">*</span></label>
                    <input x-model="form.name" required class="w-full bg-gray-50 dark:bg-[#0f1535] border border-gray-300 dark:border-white/20 rounded-lg px-4 py-2.5 text-sm text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" placeholder="Customer name">
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Code <span class="text-xs text-gray-400">(auto)</span></label>
                        <input x-model="form.code" class="w-full bg-gray-50 dark:bg-[#0f1535] border border-gray-300 dark:border-white/20 rounded-lg px-4 py-2.5 text-sm text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" placeholder="Auto-generated if empty">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Phone</label>
                        <input x-model="form.phone_number" type="tel" class="w-full bg-gray-50 dark:bg-[#0f1535] border border-gray-300 dark:border-white/20 rounded-lg px-4 py-2.5 text-sm text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" placeholder="Phone number">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Email</label>
                    <input x-model="form.email" type="email" class="w-full bg-gray-50 dark:bg-[#0f1535] border border-gray-300 dark:border-white/20 rounded-lg px-4 py-2.5 text-sm text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" placeholder="email@example.com">
                </div>
                <div class="flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-white/5">
                    <button type="button" @click="showModal = false" class="px-4 py-2 text-sm font-medium rounded-lg bg-gray-100 dark:bg-white/5 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-white/10 transition">Cancel</button>
                    <button type="submit" :disabled="saving" class="px-4 py-2 text-sm rounded-lg bg-blue-500 hover:bg-blue-600 disabled:bg-blue-500/50 disabled:cursor-not-allowed text-white font-medium transition flex items-center gap-2">
                        <svg x-show="saving" class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span x-text="editing ? 'Update' : 'Create'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Payment Modal --}}
    <div x-show="showPaymentModal" x-cloak class="fixed inset-0 z-50 flex items-end sm:items-center justify-center" @keydown.escape="showPaymentModal = false">
        <div class="absolute inset-0 bg-black/60" @click="showPaymentModal = false"></div>
        <div class="relative bg-white dark:bg-[#1a1f3d] w-full sm:max-w-sm sm:rounded-2xl rounded-t-2xl shadow-2xl max-h-[85vh] overflow-y-auto animate-slide-up sm:animate-none pb-[env(safe-area-inset-bottom,0px)]">
            <div class="sm:hidden flex justify-center pt-3 pb-1"><div class="w-10 h-1 bg-gray-300 dark:bg-gray-600 rounded-full"></div></div>
            <div class="flex items-center justify-between px-5 pt-1 pb-3">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Add Payment</h3>
                <button @click="showPaymentModal = false" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
            </div>
            <div class="px-5 space-y-4 pb-2">
                <div class="text-sm text-gray-500 dark:text-white/60">Customer: <span class="font-semibold text-gray-900 dark:text-white" x-text="paymentCustomer?.name"></span></div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Amount</label>
                    <input type="number" x-model="paymentForm.amount" step="0.01" min="0" inputmode="decimal" placeholder="0.00" class="w-full px-3 py-3 bg-gray-50 dark:bg-[#0f1535] border-2 border-gray-200 dark:border-white/20 rounded-xl text-gray-900 dark:text-white text-xl font-bold focus:border-emerald-500 outline-none transition">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Payment Method</label>
                    <select x-model="paymentForm.payment_method" class="w-full px-3 py-2.5 bg-gray-50 dark:bg-[#0f1535] border border-gray-200 dark:border-white/20 rounded-xl text-sm text-gray-900 dark:text-white">
                        <option value="cash">Cash</option>
                        <option value="card">Card</option>
                        <option value="bkash">bKash</option>
                        <option value="nagad">Nagad</option>
                        <option value="bank">Bank Transfer</option>
                    </select>
                </div>
            </div>
            <div class="px-5 pt-3 pb-5 space-y-2">
                <button @click="savePayment()" :disabled="paymentSaving" class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-40 text-white font-bold rounded-xl transition text-sm">
                    <span x-show="!paymentSaving">Record Payment</span>
                    <span x-show="paymentSaving">Saving...</span>
                </button>
                <button @click="showPaymentModal = false" class="w-full py-2.5 text-gray-400 hover:text-gray-600 text-sm font-medium rounded-lg transition">Cancel</button>
            </div>
        </div>
    </div>

    {{-- Statement Modal --}}
    <div x-show="showStatementModal" x-cloak class="fixed inset-0 z-50 flex items-end sm:items-center justify-center" @keydown.escape="showStatementModal = false">
        <div class="absolute inset-0 bg-black/60" @click="showStatementModal = false"></div>
        <div class="relative bg-white dark:bg-[#1a1f3d] w-full sm:max-w-lg sm:rounded-2xl rounded-t-2xl shadow-2xl max-h-[85vh] flex flex-col animate-slide-up sm:animate-none pb-[env(safe-area-inset-bottom,0px)]">
            <div class="sm:hidden flex justify-center pt-3 pb-1 shrink-0"><div class="w-10 h-1 bg-gray-300 dark:bg-gray-600 rounded-full"></div></div>
            <div class="flex items-center justify-between px-5 pt-1 pb-3 border-b border-gray-200 dark:border-white/10 shrink-0">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white" x-text="'Statement: ' + (statement?.customer?.name || '')"></h3>
                <button @click="showStatementModal = false" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
            </div>
            <div class="overflow-y-auto flex-1 px-5 py-3">
                <div x-show="statementLoading" class="text-center py-10 text-gray-400 text-sm">Loading...</div>
                <template x-if="!statementLoading && statement">
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div class="bg-gray-50 dark:bg-[#0f1535] rounded-lg p-3 text-center border border-gray-100 dark:border-white/5">
                                <span class="block text-xs text-gray-500 dark:text-white/50">Invoiced</span>
                                <span class="text-sm font-bold text-gray-900 dark:text-white" x-text="formatMoney(statement.summary?.total_invoiced || 0)"></span>
                            </div>
                            <div class="bg-gray-50 dark:bg-[#0f1535] rounded-lg p-3 text-center border border-gray-100 dark:border-white/5">
                                <span class="block text-xs text-gray-500 dark:text-white/50">Paid</span>
                                <span class="text-sm font-bold text-emerald-600 dark:text-emerald-400" x-text="formatMoney(statement.summary?.total_paid || 0)"></span>
                            </div>
                            <div class="bg-gray-50 dark:bg-[#0f1535] rounded-lg p-3 text-center border border-gray-100 dark:border-white/5">
                                <span class="block text-xs text-gray-500 dark:text-white/50">Due</span>
                                <span class="text-sm font-bold text-rose-600 dark:text-rose-400" x-text="formatMoney(statement.summary?.total_due || 0)"></span>
                            </div>
                        </div>
                        <div>
                            <h4 class="text-xs font-semibold text-gray-500 dark:text-white/50 uppercase mb-2">Invoices</h4>
                            <div class="space-y-1.5">
                                <template x-for="inv in statement.invoices || []" :key="inv.id">
                                    <div class="flex flex-wrap items-center justify-between gap-x-3 gap-y-1 px-3 py-2 bg-gray-50 dark:bg-[#0f1535] rounded-lg text-sm border border-gray-100 dark:border-white/5">
                                        <span class="text-gray-700 dark:text-gray-300 font-mono text-xs" x-text="inv.number"></span>
                                        <span class="text-gray-500 dark:text-white/60" x-text="inv.date"></span>
                                        <span class="font-semibold text-gray-900 dark:text-white" x-text="formatMoney(inv.total)"></span>
                                        <span class="font-semibold" :class="parseFloat(inv.total) < 0 ? 'text-rose-600 dark:text-rose-400' : (parseFloat(inv.due_amount) > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400')" x-text="parseFloat(inv.total) < 0 ? 'Refund' : (parseFloat(inv.due_amount) > 0 ? 'Due ' + formatMoney(inv.due_amount) : 'Paid')"></span>
                                    </div>
                                </template>
                                <p x-show="!statement.invoices?.length" class="text-xs text-gray-400 py-2 text-center">No invoices</p>
                            </div>
                        </div>
                        <div>
                            <h4 class="text-xs font-semibold text-gray-500 dark:text-white/50 uppercase mb-2">Payments</h4>
                            <div class="space-y-1.5">
                                <template x-for="p in statement.payments || []" :key="p.id">
                                    <div class="flex items-center justify-between px-3 py-2 bg-gray-50 dark:bg-[#0f1535] rounded-lg text-sm border border-gray-100 dark:border-white/5">
                                        <span class="text-gray-500 dark:text-white/60" x-text="p.payment_type?.name || 'Payment'"></span>
                                        <span class="text-gray-400 dark:text-white/40 text-xs" x-text="new Date(p.created_at).toLocaleDateString()"></span>
                                        <span class="font-semibold" :class="parseFloat(p.amount) < 0 ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400'" x-text="(parseFloat(p.amount) < 0 ? '- ' : '+ ') + formatMoney(Math.abs(p.amount))"></span>
                                    </div>
                                </template>
                                <p x-show="!statement.payments?.length" class="text-xs text-gray-400 py-2 text-center">No payments yet</p>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>
@endsection