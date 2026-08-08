@extends('layouts.app')

@section('title', 'Taxes')

@push('styles')
<style>
    [x-cloak] { display: none !important; }
</style>
@endpush

@section('content')
<div x-data="taxesManager" x-init="init()" class="flex flex-col h-full">

    {{-- Header --}}
    <div class="flex items-center justify-between px-4 sm:px-6 py-3 sm:py-4 shrink-0">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white">Taxes</h1>
            <p class="text-gray-500 dark:text-gray-400 text-sm mt-1">Manage tax rates</p>
        </div>
        <button
            @click="openAdd()"
            class="bg-blue-500 hover:bg-blue-600 text-white text-sm font-medium px-4 py-2 rounded-lg transition flex items-center gap-2 shrink-0"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            Add Tax
        </button>
    </div>

    {{-- Table --}}
    <div class="bg-white dark:bg-[#1a1f3d] rounded-xl shadow-sm border border-gray-200 dark:border-white/10 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-white/5">
                <thead class="bg-gray-50 dark:bg-white/5">
                    <tr>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3.5 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Rate</th>
                        <th class="px-6 py-3.5 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Type</th>
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
                                    <span class="text-sm">Loading taxes...</span>
                                </div>
                            </td>
                        </tr>
                    </template>

                    <template x-if="!loading && taxes.length === 0">
                        <tr>
                            <td colspan="5" class="px-6 py-16 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 14.25l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM10 8.25a.375.375 0 11-.75 0 .375.375 0 01.75 0zm5 5a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/>
                                </svg>
                                <h3 class="mt-3 text-sm font-medium text-gray-900 dark:text-white">No taxes found</h3>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Get started by adding your first tax rate.</p>
                            </td>
                        </tr>
                    </template>

                    <template x-for="t in taxes" :key="t.id">
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/5/50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="h-9 w-9 flex-shrink-0 rounded-lg bg-amber-100 dark:bg-amber-500/20 flex items-center justify-center">
                                        <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 14.25l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/>
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white" x-text="t.name"></div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400" x-text="t.code || ''"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <span
                                    class="inline-flex items-center text-sm font-semibold"
                                    :class="t.is_fixed ? 'text-blue-600 dark:text-blue-400' : 'text-gray-700 dark:text-gray-300'"
                                    x-text="t.is_fixed ? '$' + parseFloat(t.rate).toFixed(2) : parseFloat(t.rate).toFixed(1) + '%'"
                                ></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                    :class="t.is_fixed ? 'bg-blue-100 dark:bg-blue-500/20 text-blue-700 dark:text-blue-400' : 'bg-emerald-100 dark:bg-emerald-500/20 text-emerald-700 dark:text-emerald-400'"
                                    x-text="t.is_fixed ? 'Fixed' : 'Percentage'"
                                ></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <button
                                    @click="toggleStatus(t)"
                                    type="button"
                                    class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800"
                                    :class="t.is_enabled ? 'bg-blue-600 dark:bg-blue-500' : 'bg-gray-300 dark:bg-gray-600'"
                                    :aria-checked="t.is_enabled"
                                    role="switch"
                                >
                                    <span
                                        class="inline-block h-4 w-4 rounded-full bg-white shadow-sm transform transition-transform duration-200"
                                        :class="t.is_enabled ? 'translate-x-6' : 'translate-x-1'"
                                    ></span>
                                </button>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <button
                                        @click="openEdit(t)"
                                        class="p-2 text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-500/10 rounded-lg transition-colors"
                                        title="Edit"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </button>
                                    <button
                                        @click="deleteTax(t.id)"
                                        class="p-2 text-gray-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10 rounded-lg transition-colors"
                                        title="Delete"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
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
                <span x-text="pagination.total + ' tax' + (pagination.total !== 1 ? 'es' : '')"></span>
            </div>
            <div class="flex items-center gap-2">
                <button
                    @click="fetchTaxes(pagination.current_page - 1)"
                    :disabled="!pagination.prev_page_url"
                    class="inline-flex items-center px-3 py-1.5 text-sm font-medium rounded-lg border border-gray-300 dark:border-white/10 text-gray-700 dark:text-gray-300 bg-white dark:bg-[#0f1535] hover:bg-gray-50 dark:hover:bg-gray-600 disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
                >
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                    Prev
                </button>
                <button
                    @click="fetchTaxes(pagination.current_page + 1)"
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
                    <h3 id="modal-title" class="text-lg font-semibold text-gray-900 dark:text-white" x-text="editing ? 'Edit Tax' : 'Add Tax'"></h3>
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
                            placeholder="e.g. VAT, GST, Sales Tax"
                            class="w-full px-3 py-2 bg-white dark:bg-[#0f1535] border border-gray-300 dark:border-white/10 rounded-lg text-sm text-gray-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:border-blue-500 transition-colors"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                            Rate <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input
                                type="number"
                                x-model="form.rate"
                                required
                                min="0"
                                :step="form.is_fixed ? '0.01' : '0.1'"
                                placeholder="0.00"
                                class="w-full px-3 py-2 bg-white dark:bg-[#0f1535] border border-gray-300 dark:border-white/10 rounded-lg text-sm text-gray-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:border-blue-500 transition-colors"
                            />
                            <span class="absolute inset-y-0 right-0 pr-3 flex items-center text-sm text-gray-500 dark:text-gray-400" x-text="form.is_fixed ? '$' : '%'"></span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                            Code
                        </label>
                        <input
                            type="text"
                            x-model="form.code"
                            placeholder="e.g. VAT-20"
                            class="w-full px-3 py-2 bg-white dark:bg-[#0f1535] border border-gray-300 dark:border-white/10 rounded-lg text-sm text-gray-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 font-mono focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:border-blue-500 transition-colors"
                        />
                    </div>

                    <div class="flex items-center gap-3 py-1">
                        <button
                            type="button"
                            @click="form.is_fixed = !form.is_fixed"
                            class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800"
                            :class="form.is_fixed ? 'bg-blue-600 dark:bg-blue-500' : 'bg-gray-300 dark:bg-gray-600'"
                            :aria-checked="form.is_fixed"
                            role="switch"
                        >
                            <span
                                class="inline-block h-4 w-4 rounded-full bg-white shadow-sm transform transition-transform duration-200"
                                :class="form.is_fixed ? 'translate-x-6' : 'translate-x-1'"
                            ></span>
                        </button>
                        <span class="text-sm text-gray-700 dark:text-gray-300" x-text="form.is_fixed ? 'Fixed Amount' : 'Percentage'"></span>
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
                            class="inline-flex items-center px-4 py-2.5 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:opacity-60 disabled:cursor-not-allowed"
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