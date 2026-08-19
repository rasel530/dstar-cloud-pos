@extends('layouts.app')

@section('title', 'Printer Settings')

@push('styles')
<style>
    [x-cloak] { display: none !important; }
</style>
@endpush

@section('content')
<div x-data="printersManager" class="flex flex-col h-full">

    {{-- Header --}}
    <div class="flex items-center justify-between px-4 sm:px-6 py-3 sm:py-4 shrink-0">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white">Printer Settings</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Configure receipt and kitchen printers</p>
        </div>
        <button
            @click="openAdd()"
            class="bg-blue-500 hover:bg-blue-600 text-white text-sm font-medium px-4 py-2 rounded-lg transition flex items-center gap-2 shrink-0"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            Add Printer
        </button>
    </div>

    {{-- Table --}}
    <div class="bg-white dark:bg-[#1a1f3d] rounded-xl shadow-sm border border-gray-200 dark:border-white/10 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-white/5">
                <thead class="bg-gray-50 dark:bg-white/5">
                    <tr>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Printer Name</th>
                        <th class="px-6 py-3.5 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Paper Width</th>
                        <th class="px-6 py-3.5 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3.5 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Cut Paper</th>
                        <th class="px-6 py-3.5 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Cash Drawer</th>
                        <th class="px-6 py-3.5 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Copies</th>
                        <th class="px-6 py-3.5 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-white/5 bg-white dark:bg-[#1a1f3d]">
                    <template x-if="loading">
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <div class="flex items-center justify-center gap-2 text-gray-400 dark:text-gray-500">
                                    <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                    </svg>
                                    <span class="text-sm">Loading printers...</span>
                                </div>
                            </td>
                        </tr>
                    </template>

                    <template x-if="!loading && printers.length === 0">
                        <tr>
                            <td colspan="7" class="px-6 py-16 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z"/>
                                </svg>
                                <h3 class="mt-3 text-sm font-medium text-gray-900 dark:text-white">No printers configured</h3>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Set up your first printer to start printing receipts.</p>
                            </td>
                        </tr>
                    </template>

                    <template x-for="pr in printers" :key="pr.id">
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/5/50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="h-9 w-9 flex-shrink-0 rounded-lg bg-gray-100 dark:bg-[#0f1535] flex items-center justify-center">
                                        <svg class="w-4 h-4 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z"/>
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white"                                                 x-text="pr.printer_name"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="text-sm text-gray-600 dark:text-gray-300" x-text="pr.paper_width + 'mm'"></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                    :class="pr.printer_type === 1 ? 'bg-orange-100 dark:bg-orange-500/20 text-orange-700 dark:text-orange-400' : 'bg-blue-100 dark:bg-blue-500/20 text-blue-700 dark:text-blue-400'"
                                    x-text="pr.printer_type === 1 ? 'Kitchen' : 'Receipt'"
                                ></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span
                                    class="text-sm"
                                    :class="pr.cut_paper ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-400 dark:text-gray-500'"
                                    x-text="pr.cut_paper ? 'Yes' : 'No'"
                                ></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span
                                    class="text-sm"
                                    :class="pr.open_cash_drawer ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-400 dark:text-gray-500'"
                                    x-text="pr.open_cash_drawer ? 'Yes' : 'No'"
                                ></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-600 dark:text-gray-300" x-text="pr.number_of_copies || 1"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <button
                                        @click="testPrint(pr.id)"
                                        class="inline-flex items-center px-2.5 py-1.5 text-xs font-medium text-blue-700 dark:text-blue-400 bg-blue-50 dark:bg-blue-500/10 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-500/20 transition-colors"
                                        title="Test Print"
                                    >
                                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z"/>
                                        </svg>
                                        Test
                                    </button>
                                    <button
                                        @click="openEdit(pr)"
                                        class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-medium bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-white/10 dark:text-gray-200 dark:hover:bg-white/15 border border-transparent transition whitespace-nowrap"
                                        title="Edit"
                                    >
                                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        <span>Edit</span>
                                    </button>
                                    <button
                                        @click="deletePrinter(pr.id)"
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
                <span x-text="pagination.total + ' printer' + (pagination.total !== 1 ? 's' : '')"></span>
            </div>
            <div class="flex items-center gap-2">
                <button
                    @click="fetchPrinters(pagination.current_page - 1)"
                    :disabled="!pagination.prev_page_url"
                    class="inline-flex items-center px-3 py-1.5 text-sm font-medium rounded-lg border border-gray-300 dark:border-white/10 text-gray-700 dark:text-gray-300 bg-white dark:bg-[#0f1535] hover:bg-gray-50 dark:hover:bg-gray-600 disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
                >
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                    Prev
                </button>
                <button
                    @click="fetchPrinters(pagination.current_page + 1)"
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

        <div class="flex min-h-full items-center justify-center p-4">
            <div
                x-show="showModal"
                x-transition
                @click.stop
                class="relative w-full max-w-lg bg-white dark:bg-[#1a1f3d] rounded-xl shadow-2xl border border-gray-200 dark:border-white/10"
            >
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-white/10">
                    <h3 id="modal-title" class="text-lg font-semibold text-gray-900 dark:text-white" x-text="editing ? 'Edit Printer' : 'Add Printer'"></h3>
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
                            Printer Name <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            x-model="form.printer_name"
                            required
                            placeholder="e.g. Receipt Printer, Kitchen Printer"
                            class="w-full px-3 py-2 bg-white dark:bg-[#0f1535] border border-gray-300 dark:border-white/10 rounded-lg text-sm text-gray-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:border-blue-500 transition-colors"
                        />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                                Paper Width (mm) <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="number"
                                x-model="form.paper_width"
                                required
                                min="1"
                                placeholder="80"
                                class="w-full px-3 py-2 bg-white dark:bg-[#0f1535] border border-gray-300 dark:border-white/10 rounded-lg text-sm text-gray-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:border-blue-500 transition-colors"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                                Number of Copies
                            </label>
                            <input
                                type="number"
                                x-model="form.number_of_copies"
                                min="1"
                                max="10"
                                placeholder="1"
                                class="w-full px-3 py-2 bg-white dark:bg-[#0f1535] border border-gray-300 dark:border-white/10 rounded-lg text-sm text-gray-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:border-blue-500 transition-colors"
                            />
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                            Printer Type
                        </label>
                        <select
                            x-model.number="form.printer_type"
                            class="w-full px-3 py-2 bg-white dark:bg-[#0f1535] border border-gray-300 dark:border-white/10 rounded-lg text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:border-blue-500 transition-colors"
                        >
                            <option value="0">Receipt Printer</option>
                            <option value="1">Kitchen Printer</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                            Header
                        </label>
                        <input
                            type="text"
                            x-model="form.header"
                            placeholder="Receipt header text"
                            class="w-full px-3 py-2 bg-white dark:bg-[#0f1535] border border-gray-300 dark:border-white/10 rounded-lg text-sm text-gray-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:border-blue-500 transition-colors"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                            Footer
                        </label>
                        <input
                            type="text"
                            x-model="form.footer"
                            placeholder="Receipt footer text"
                            class="w-full px-3 py-2 bg-white dark:bg-[#0f1535] border border-gray-300 dark:border-white/10 rounded-lg text-sm text-gray-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:border-blue-500 transition-colors"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                            Feed Lines (before/after cut)
                        </label>
                        <input
                            type="number"
                            x-model="form.feed_lines"
                            min="0"
                            max="20"
                            placeholder="3"
                            class="w-full px-3 py-2 bg-white dark:bg-[#0f1535] border border-gray-300 dark:border-white/10 rounded-lg text-sm text-gray-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:border-blue-500 transition-colors"
                        />
                    </div>

                    <div class="space-y-3 py-1">
                        <div class="flex items-center gap-3">
                            <button
                                type="button"
                                @click="form.cut_paper = !form.cut_paper"
                                class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800"
                                :class="form.cut_paper ? 'bg-blue-600 dark:bg-blue-500' : 'bg-gray-300 dark:bg-gray-600'"
                                :aria-checked="form.cut_paper"
                                role="switch"
                            >
                                <span
                                    class="inline-block h-4 w-4 rounded-full bg-white shadow-sm transform transition-transform duration-200"
                                    :class="form.cut_paper ? 'translate-x-6' : 'translate-x-1'"
                                ></span>
                            </button>
                            <span class="text-sm text-gray-700 dark:text-gray-300">Cut Paper</span>
                        </div>

                        <div class="flex items-center gap-3">
                            <button
                                type="button"
                                @click="form.open_cash_drawer = !form.open_cash_drawer"
                                class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800"
                                :class="form.open_cash_drawer ? 'bg-blue-600 dark:bg-blue-500' : 'bg-gray-300 dark:bg-gray-600'"
                                :aria-checked="form.open_cash_drawer"
                                role="switch"
                            >
                                <span
                                    class="inline-block h-4 w-4 rounded-full bg-white shadow-sm transform transition-transform duration-200"
                                    :class="form.open_cash_drawer ? 'translate-x-6' : 'translate-x-1'"
                                ></span>
                            </button>
                            <span class="text-sm text-gray-700 dark:text-gray-300">Open Cash Drawer</span>
                        </div>
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