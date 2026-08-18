@extends('layouts.app')

@section('title', 'Promotions')

@push('styles')
<style>
    [x-cloak] { display: none !important; }
</style>
@endpush

@section('content')
<div x-data="promotionsManager" class="flex flex-col h-full">

    {{-- Header --}}
    <div class="flex items-center justify-between px-4 sm:px-6 py-3 sm:py-4 shrink-0">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Promotions</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Manage discounts and happy hours</p>
        </div>
        <button
            @click="openAdd()"
            class="bg-blue-500 hover:bg-blue-600 text-white text-sm font-medium px-4 py-2 rounded-lg transition flex items-center gap-2 shrink-0"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            Add Promotion
        </button>
    </div>

    {{-- Table --}}
    <div class="bg-white dark:bg-[#1a1f3d] rounded-xl shadow-sm border border-gray-200 dark:border-white/10 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-white/5">
                <thead class="bg-gray-50 dark:bg-white/5">
                    <tr>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Dates</th>
                        <th class="px-6 py-3.5 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Days Active</th>
                        <th class="px-6 py-3.5 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3.5 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-white/5 bg-white dark:bg-[#1a1f3d]">
                    <template x-if="loading">
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="flex items-center justify-center gap-2 text-gray-400 dark:text-gray-500">
                                    <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                    </svg>
                                    <span class="text-sm">Loading promotions...</span>
                                </div>
                            </td>
                        </tr>
                    </template>

                    <template x-if="!loading && promotions.length === 0">
                        <tr>
                            <td colspan="5" class="px-6 py-16 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/>
                                </svg>
                                <h3 class="mt-3 text-sm font-medium text-gray-900 dark:text-white">No promotions found</h3>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Get started by creating your first promotion.</p>
                            </td>
                        </tr>
                    </template>

                    <template x-for="p in promotions" :key="p.id">
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/5/50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="h-9 w-9 flex-shrink-0 rounded-lg bg-purple-100 dark:bg-purple-500/20 flex items-center justify-center">
                                        <svg class="w-4 h-4 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z"/>
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white" x-text="p.name"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-600 dark:text-gray-300" x-text="formatDate(p.start_date)"></div>
                                <div class="text-xs text-gray-400 dark:text-gray-500" x-text="'to ' + formatDate(p.end_date)"></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="flex items-center justify-center gap-1 flex-wrap">
                                    <template x-for="day in p.days_of_week || []" :key="day">
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-gray-100 dark:bg-[#0f1535] text-gray-600 dark:text-gray-400" x-text="dayName(day).substring(0, 2)"></span>
                                    </template>
                                    <span x-show="!(p.days_of_week && p.days_of_week.length)" class="text-xs text-gray-400">All days</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <button
                                    @click="toggleStatus(p)"
                                    type="button"
                                    class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800"
                                    :class="p.is_enabled ? 'bg-blue-600 dark:bg-blue-500' : 'bg-gray-300 dark:bg-gray-600'"
                                    :aria-checked="p.is_enabled"
                                    role="switch"
                                >
                                    <span
                                        class="inline-block h-4 w-4 rounded-full bg-white shadow-sm transform transition-transform duration-200"
                                        :class="p.is_enabled ? 'translate-x-6' : 'translate-x-1'"
                                    ></span>
                                </button>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button
                                        @click="openEdit(p)"
                                        class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-medium bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-white/10 dark:text-gray-200 dark:hover:bg-white/15 border border-transparent transition whitespace-nowrap"
                                        title="Edit"
                                    >
                                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        <span>Edit</span>
                                    </button>
                                    <button
                                        @click="deletePromotion(p.id)"
                                        class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-medium bg-rose-50 text-rose-600 hover:bg-rose-100 dark:bg-rose-500/10 dark:text-rose-400 dark:hover:bg-rose-500/20 border border-transparent transition whitespace-nowrap"
                                        title="Delete"
                                    >
                                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
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

        {{-- Pagination --}}
        <div class="flex items-center justify-between px-6 py-3 border-t border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5" x-show="pagination.total > 0">
            <div class="text-sm text-gray-600 dark:text-gray-400">
                <span x-text="'Page ' + pagination.current_page + ' of ' + pagination.last_page"></span>
                <span class="mx-1 text-gray-400 dark:text-gray-500">&middot;</span>
                <span x-text="pagination.total + ' promotion' + (pagination.total !== 1 ? 's' : '')"></span>
            </div>
            <div class="flex items-center gap-2">
                <button
                    @click="fetchPromotions(pagination.current_page - 1)"
                    :disabled="!pagination.prev_page_url"
                    class="inline-flex items-center px-3 py-1.5 text-sm font-medium rounded-lg border border-gray-300 dark:border-white/10 text-gray-700 dark:text-gray-300 bg-white dark:bg-[#0f1535] hover:bg-gray-50 dark:hover:bg-gray-600 disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
                >
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                    Prev
                </button>
                <button
                    @click="fetchPromotions(pagination.current_page + 1)"
                    :disabled="!pagination.next_page_url"
                    class="inline-flex items-center px-3 py-1.5 text-sm font-medium rounded-lg border border-gray-300 dark:border-white/10 text-gray-700 dark:text-gray-300 bg-white dark:bg-[#0f1535] hover:bg-gray-50 dark:hover:bg-gray-600 disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
                >
                    Next
                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Modal --}}
    <div
        x-show="showModal"
        x-cloak
        x-trap.noscroll="showModal"
        class="fixed inset-0 z-50 overflow-y-auto"
        aria-labelledby="modal-title"
        role="dialog"
        aria-modal="true"
    >
        <div
            x-show="showModal"
            x-transition.opacity
            class="fixed inset-0 bg-slate-900/50 dark:bg-slate-900/70 backdrop-blur-sm"
            @click="showModal = false"
        ></div>

        <div class="flex flex flex-col h-full items-center justify-center p-4">
            <div
                x-show="showModal"
                x-transition
                @click.stop
                class="relative w-full max-w-lg bg-white dark:bg-[#1a1f3d] rounded-xl shadow-2xl border border-gray-200 dark:border-white/10"
            >
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-white/10">
                    <h3 id="modal-title" class="text-lg font-semibold text-gray-900 dark:text-white" x-text="editing ? 'Edit Promotion' : 'Add Promotion'"></h3>
                    <button
                        @click="showModal = false"
                        class="p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="save()" class="px-6 py-4 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                            Name <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            x-model="form.name"
                            required
                            placeholder="e.g. Summer Sale, Happy Hour"
                            class="w-full px-3 py-2 bg-white dark:bg-[#0f1535] border border-gray-300 dark:border-white/10 rounded-lg text-sm text-gray-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:border-blue-500 transition-colors"
                        />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                                Start Date <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="date"
                                x-model="form.start_date"
                                required
                                class="w-full px-3 py-2 bg-white dark:bg-[#0f1535] border border-gray-300 dark:border-white/10 rounded-lg text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:border-blue-500 transition-colors"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                                End Date <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="date"
                                x-model="form.end_date"
                                required
                                class="w-full px-3 py-2 bg-white dark:bg-[#0f1535] border border-gray-300 dark:border-white/10 rounded-lg text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:border-blue-500 transition-colors"
                            />
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Days of Week
                        </label>
                        <div class="flex flex-wrap gap-2">
                            <template x-for="(day, idx) in dayOptions" :key="day.value">
                                <label class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg border text-sm font-medium cursor-pointer transition-colors"
                                    :class="form.days_of_week.includes(day.value)
                                        ? 'bg-blue-50 dark:bg-blue-500/10 border-blue-300 dark:border-blue-500 text-blue-700 dark:text-blue-400'
                                        : 'bg-white dark:bg-[#0f1535] border-gray-300 dark:border-white/10 text-gray-700 dark:text-gray-300'"
                                >
                                    <input
                                        type="checkbox"
                                        :value="day.value"
                                        :checked="form.days_of_week.includes(day.value)"
                                        @change="toggleDay(day.value)"
                                        class="sr-only"
                                    />
                                    <span x-text="day.label.substring(0, 3)"></span>
                                </label>
                            </template>
                        </div>
                    </div>

                    <div x-show="editId">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Promotion Products
                        </label>
                        <div class="space-y-2 mb-3">
                            <template x-for="(item, idx) in promotionItems" :key="item.id">
                                <div class="flex items-center justify-between gap-2 px-3 py-2 bg-gray-50 dark:bg-[#0f1535] rounded-lg border border-gray-200 dark:border-white/10">
                                    <div class="flex-1 min-w-0">
                                        <span class="text-sm text-gray-900 dark:text-white" x-text="item.product_name || getProductName(item.uid)"></span>
                                        <span class="ml-2 text-xs" :class="item.discount_type == 0 ? 'text-blue-600 dark:text-blue-400' : 'text-green-600 dark:text-green-400'">
                                            <template x-if="item.discount_type == 0">
                                                <span x-text="parseFloat(item.value) + '%'"></span>
                                            </template>
                                            <template x-if="item.discount_type == 1">
                                                <span x-text="$store.currency.symbol + parseFloat(item.value).toFixed(2) + ' off'"></span>
                                            </template>
                                        </span>
                                    </div>
                                    <button
                                        type="button"
                                        @click="removePromotionItem(item.id, idx)"
                                        class="p-1 text-gray-400 hover:text-red-600 dark:hover:text-red-400 rounded transition-colors flex-shrink-0"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>
                            </template>
                            <template x-if="promotionItems.length === 0">
                                <p class="text-xs text-gray-400 dark:text-gray-500">No products assigned yet.</p>
                            </template>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-7 gap-2 items-end">
                            <div class="sm:col-span-3">
                                <select
                                    x-model="newItem.product_id"
                                    class="w-full px-3 py-2 bg-white dark:bg-[#0f1535] border border-gray-300 dark:border-white/10 rounded-lg text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:border-blue-500 transition-colors"
                                >
                                    <option value="">Select product...</option>
                                    <template x-for="p in allProducts" :key="p.id">
                                        <option :value="p.id" x-text="p.name + (p.code ? ' (' + p.code + ')' : '')"></option>
                                    </template>
                                </select>
                            </div>
                            <div class="sm:col-span-1">
                                <select
                                    x-model="newItem.discount_type"
                                    class="w-full px-2 py-2 bg-white dark:bg-[#0f1535] border border-gray-300 dark:border-white/10 rounded-lg text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:border-blue-500 transition-colors"
                                >
                                    <option value="0">Percent</option>
                                    <option value="1">Flat</option>
                                </select>
                            </div>
                            <div class="sm:col-span-2">
                                <input
                                    type="number"
                                    x-model="newItem.value"
                                    step="0.01"
                                    min="0"
                                    placeholder="Value"
                                    class="w-full px-3 py-2 bg-white dark:bg-[#0f1535] border border-gray-300 dark:border-white/10 rounded-lg text-sm text-gray-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:border-blue-500 transition-colors"
                                />
                            </div>
                            <div class="sm:col-span-1">
                                <button
                                    type="button"
                                    @click="addPromotionItem()"
                                    :disabled="!newItem.product_id || newItem.value <= 0"
                                    class="w-full px-3 py-2 text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 dark:bg-purple-500 dark:hover:bg-purple-600 rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:opacity-40 disabled:cursor-not-allowed"
                                >
                                    Add
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 py-1">
                        <button
                            type="button"
                            @click="form.is_enabled = !form.is_enabled"
                            class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800"
                            :class="form.is_enabled ? 'bg-blue-600 dark:bg-blue-500' : 'bg-gray-300 dark:bg-gray-600'"
                            :aria-checked="form.is_enabled"
                            role="switch"
                        >
                            <span
                                class="inline-block h-4 w-4 rounded-full bg-white shadow-sm transform transition-transform duration-200"
                                :class="form.is_enabled ? 'translate-x-6' : 'translate-x-1'"
                            ></span>
                        </button>
                        <span class="text-sm text-gray-700 dark:text-gray-300" x-text="form.is_enabled ? 'Enabled' : 'Disabled'"></span>
                    </div>

                    <div
                        x-show="error"
                        x-text="error"
                        class="text-sm text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 rounded-lg px-4 py-3"
                    ></div>

                    <div class="flex justify-end gap-3 pt-2">
                        <button
                            type="button"
                            @click="showModal = false"
                            class="px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-[#0f1535] border border-gray-300 dark:border-white/10 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="saving"
                            class="bg-blue-500 hover:bg-blue-600 text-white text-sm font-medium px-4 py-2 rounded-lg transition flex items-center gap-2 shrink-0 disabled:opacity-60 disabled:cursor-not-allowed"
                        >
                            <svg
                                x-show="saving"
                                class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                                fill="none"
                                viewBox="0 0 24 24"
                            >
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>
                            <span x-text="saving ? 'Saving...' : 'Save'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('promotionsManager', () => ({
        promotions: [],
        loading: false,
        pagination: {
            current_page: 1,
            last_page: 1,
            total: 0,
            per_page: 15,
            prev_page_url: null,
            next_page_url: null,
        },
        showModal: false,
        editing: false,
        saving: false,
        editId: null,
        error: '',
        promotionItems: [],
        allProducts: [],
        newItem: { product_id: '', discount_type: 0, value: 0 },
        dayOptions: [
            { value: 0, label: 'Sunday' },
            { value: 1, label: 'Monday' },
            { value: 2, label: 'Tuesday' },
            { value: 3, label: 'Wednesday' },
            { value: 4, label: 'Thursday' },
            { value: 5, label: 'Friday' },
            { value: 6, label: 'Saturday' },
        ],
        form: {
            name: '',
            start_date: '',
            end_date: '',
            days_of_week: [],
            is_enabled: true,
        },

        init() {
            this.fetchPromotions();
        },

        getToken() {
            return localStorage.getItem('auth_token');
        },

        formatDate(date) {
            if (!date) return '--';
            const d = new Date(date);
            return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
        },

        dayName(day) {
            return ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'][day] || '';
        },

        intToDayArray(val) {
            if (Array.isArray(val)) return val;
            const num = parseInt(val) || 0;
            const days = [];
            for (let i = 0; i < 7; i++) {
                if (num & (1 << i)) days.push(i);
            }
            return days.length > 0 ? days : [];
        },

        dayArrayToInt(arr) {
            if (!Array.isArray(arr)) return parseInt(arr) || 127;
            let val = 0;
            arr.forEach(d => { val |= (1 << d); });
            return val || 127;
        },

        async fetchPromotions(page = 1) {
            this.loading = true;
            try {
                const params = new URLSearchParams({ page, per_page: this.pagination.per_page });
                const res = await fetch(`/api/promotions?${params}`, {
                    headers: {
                        'Authorization': `Bearer ${this.getToken()}`,
                        'Accept': 'application/json',
                    },
                });
                const data = await res.json();
                this.promotions = data.data || [];
                this.pagination = {
                    current_page: data.meta?.current_page || data.current_page || 1,
                    last_page: data.meta?.last_page || data.last_page || 1,
                    total: data.meta?.total || data.total || 0,
                    per_page: data.meta?.per_page || data.per_page || 15,
                    prev_page_url: data.links?.prev || data.prev_page_url || null,
                    next_page_url: data.links?.next || data.next_page_url || null,
                };
            } catch (e) {
                console.error('Failed to fetch promotions:', e);
            } finally {
                this.loading = false;
            }
        },

        toggleDay(value) {
            const idx = this.form.days_of_week.indexOf(value);
            if (idx === -1) {
                this.form.days_of_week.push(value);
            } else {
                this.form.days_of_week.splice(idx, 1);
            }
        },

        getProductName(uid) {
            const p = this.allProducts.find(prod => prod.id == uid);
            return p ? p.name : 'Unknown (' + uid + ')';
        },

        async loadPromotionItems(promotionId) {
            try {
                const res = await fetch(`/api/promotions/${promotionId}/items`, {
                    headers: {
                        'Authorization': `Bearer ${this.getToken()}`,
                        'Accept': 'application/json',
                    },
                });
                const data = await res.json();
                this.promotionItems = data.data || [];
            } catch (e) {
                this.promotionItems = [];
            }
        },

        async loadAllProducts() {
            if (this.allProducts.length > 0) return;
            try {
                const res = await fetch('/api/products?per_page=1000', {
                    headers: {
                        'Authorization': `Bearer ${this.getToken()}`,
                        'Accept': 'application/json',
                    },
                });
                const data = await res.json();
                this.allProducts = data?.data?.data || data?.data || [];
            } catch (e) {
                this.allProducts = [];
            }
        },

        async addPromotionItem() {
            if (!this.newItem.product_id || this.newItem.value <= 0) return;
            try {
                const res = await fetch(`/api/promotions/${this.editId}/items`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${this.getToken()}`,
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    },
                    body: JSON.stringify({
                        product_id: this.newItem.product_id,
                        discount_type: parseInt(this.newItem.discount_type),
                        value: parseFloat(this.newItem.value),
                    }),
                });
                const data = await res.json();
                if (data.data) {
                    this.promotionItems.push(data.data);
                    this.newItem = { product_id: '', discount_type: 0, value: 0 };
                }
            } catch (e) {
                this.error = e.message;
            }
        },

        async removePromotionItem(itemId, idx) {
            try {
                await fetch(`/api/promotions/${this.editId}/items/${itemId}`, {
                    method: 'DELETE',
                    headers: {
                        'Authorization': `Bearer ${this.getToken()}`,
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    },
                });
                this.promotionItems.splice(idx, 1);
            } catch (e) {
                this.error = e.message;
            }
        },

        openAdd() {
            this.editing = false;
            this.editId = null;
            this.error = '';
            this.promotionItems = [];
            this.newItem = { product_id: '', discount_type: 0, value: 0 };
            this.form = {
                name: '',
                start_date: '',
                end_date: '',
                days_of_week: [],
                is_enabled: true,
            };
            this.showModal = true;
            this.loadAllProducts();
        },

        openEdit(promotion) {
            this.editing = true;
            this.editId = promotion.id;
            this.error = '';
            this.promotionItems = [];
            this.newItem = { product_id: '', discount_type: 0, value: 0 };
            this.form = {
                name: promotion.name || '',
                start_date: promotion.start_date ? promotion.start_date.substring(0, 10) : '',
                end_date: promotion.end_date ? promotion.end_date.substring(0, 10) : '',
                days_of_week: this.intToDayArray(promotion.days_of_week),
                is_enabled: promotion.is_enabled ?? true,
            };
            this.showModal = true;
            this.loadAllProducts();
            this.loadPromotionItems(promotion.id);
        },

        async save() {
            this.saving = true;
            this.error = '';
            try {
                const url = this.editing ? `/api/promotions/${this.editId}` : '/api/promotions';
                const method = this.editing ? 'PUT' : 'POST';

                const payload = {
                    name: this.form.name,
                    start_date: this.form.start_date,
                    end_date: this.form.end_date,
                    days_of_week: this.dayArrayToInt(this.form.days_of_week),
                    is_enabled: this.form.is_enabled,
                };

                const res = await fetch(url, {
                    method,
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${this.getToken()}`,
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    },
                    body: JSON.stringify(payload),
                });

                if (!res.ok) {
                    const err = await res.json();
                    throw new Error(err.message || 'Failed to save promotion.');
                }

                const savedData = await res.json();
                const promoData = savedData.data || savedData;
                this.showModal = false;
                if (promoData && promoData.id) {
                    this.editId = promoData.id;
                    this.editing = true;
                }
                await this.fetchPromotions(1);
            } catch (e) {
                this.error = e.message;
            } finally {
                this.saving = false;
            }
        },

        async deletePromotion(id) {
            if (!confirm('Are you sure you want to delete this promotion? This action cannot be undone.')) return;
            try {
                const res = await fetch(`/api/promotions/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${this.getToken()}`,
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    },
                });

                if (!res.ok) {
                    const err = await res.json();
                    throw new Error(err.message || 'Failed to delete promotion.');
                }

                await this.fetchPromotions(this.pagination.current_page);
            } catch (e) {
                alert(e.message);
            }
        },

        async toggleStatus(promotion) {
            const newStatus = !promotion.is_enabled;
            try {
                const res = await fetch(`/api/promotions/${promotion.id}`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${this.getToken()}`,
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    },
                    body: JSON.stringify({ is_enabled: newStatus }),
                });

                if (!res.ok) throw new Error('Failed to update status.');
                promotion.is_enabled = newStatus;
            } catch (e) {
                alert(e.message);
            }
        },
    }));
});
</script>
@endpush
