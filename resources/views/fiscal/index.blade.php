@extends('layouts.app')

@section('title', 'Fiscal Items')

@section('content')
<div x-data="fiscalItemsManager" class="flex flex-col h-full">

    {{-- Header --}}
    <div class="flex items-center justify-between px-4 sm:px-6 py-3 sm:py-4 shrink-0">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white">Fiscal Items</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Manage PLU-to-VAT mappings</p>
        </div>
        <button
            @click="openAdd()"
            class="bg-blue-500 hover:bg-blue-600 text-white text-sm font-medium px-4 py-2 rounded-lg transition flex items-center gap-2 shrink-0"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            Add Fiscal Item
        </button>
    </div>

    {{-- Search --}}
    <div class="relative">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <svg class="h-5 w-5 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
        </div>
        <input
            type="text"
            x-model="search"
            @input.debounce.500ms="fetchItems()"
            placeholder="Search by PLU or name..."
            class="w-full pl-10 pr-4 py-2.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-lg text-sm text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:border-blue-500 transition-colors"
        />
    </div>

    {{-- Table --}}
    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                <thead class="bg-slate-50 dark:bg-slate-800/50">
                    <tr>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">PLU</th>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3.5 text-right text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">VAT (%)</th>
                        <th class="px-6 py-3.5 text-right text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700 bg-white dark:bg-slate-800">
                    <template x-if="loading">
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center">
                                <div class="flex items-center justify-center gap-2 text-slate-400 dark:text-slate-500">
                                    <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                    </svg>
                                    <span class="text-sm">Loading fiscal items...</span>
                                </div>
                            </td>
                        </tr>
                    </template>

                    <template x-if="!loading && items.length === 0">
                        <tr>
                            <td colspan="4" class="px-6 py-16 text-center">
                                <svg class="mx-auto h-12 w-12 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 14.25l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM10 8.25a.375.375 0 11-.75 0 .375.375 0 01.75 0zm5 5a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/>
                                </svg>
                                <h3 class="mt-3 text-sm font-medium text-slate-900 dark:text-white">No fiscal items found</h3>
                                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Get started by adding your first fiscal item.</p>
                            </td>
                        </tr>
                    </template>

                    <template x-for="item in items" :key="item.plu">
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="h-9 w-9 flex-shrink-0 rounded-lg bg-amber-100 dark:bg-amber-500/20 flex items-center justify-center">
                                        <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 14.25l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/>
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <div class="text-sm font-medium text-slate-900 dark:text-white font-mono" x-text="item.plu"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-700 dark:text-slate-300" x-text="item.name"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-slate-700 dark:text-slate-300" x-text="parseFloat(item.vat).toFixed(1) + '%'"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <button
                                        @click="openEdit(item)"
                                        class="p-2 text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-500/10 rounded-lg transition-colors"
                                        title="Edit"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </button>
                                    <button
                                        @click="deleteItem(item.plu)"
                                        class="p-2 text-slate-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10 rounded-lg transition-colors"
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
        <div class="flex items-center justify-between px-6 py-3 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50" x-show="pagination && pagination.last_page > 1">
            <div class="text-sm text-slate-600 dark:text-slate-400">
                <span x-text="'Page ' + pagination.current_page + ' of ' + pagination.last_page"></span>
                <span class="mx-1 text-slate-400 dark:text-slate-500">&middot;</span>
                <span x-text="pagination.total + ' item' + (pagination.total !== 1 ? 's' : '')"></span>
            </div>
            <div class="flex items-center gap-2">
                <button
                    @click="fetchItems(pagination.current_page - 1)"
                    :disabled="pagination.current_page <= 1"
                    class="inline-flex items-center px-3 py-1.5 text-sm font-medium rounded-lg border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-700 hover:bg-slate-50 dark:hover:bg-slate-600 disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
                >
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                    Prev
                </button>
                <button
                    @click="fetchItems(pagination.current_page + 1)"
                    :disabled="pagination.current_page >= pagination.last_page"
                    class="inline-flex items-center px-3 py-1.5 text-sm font-medium rounded-lg border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-700 hover:bg-slate-50 dark:hover:bg-slate-600 disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
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
                class="relative w-full max-w-md bg-white dark:bg-slate-800 rounded-xl shadow-2xl border border-slate-200 dark:border-slate-700"
            >
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 dark:border-slate-700">
                    <h3 id="modal-title" class="text-lg font-semibold text-slate-900 dark:text-white" x-text="editing ? 'Edit Fiscal Item' : 'Add Fiscal Item'"></h3>
                    <button
                        @click="showModal = false"
                        class="p-1.5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition-colors"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="save()" class="px-6 py-4 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                            PLU <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            x-model="form.plu"
                            required
                            :disabled="editing"
                            placeholder="e.g. 1001"
                            class="w-full px-3 py-2 bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg text-sm text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:border-blue-500 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                            Name <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            x-model="form.name"
                            required
                            placeholder="Product name"
                            class="w-full px-3 py-2 bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg text-sm text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:border-blue-500 transition-colors"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                            VAT (%) <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="number"
                            x-model="form.vat"
                            required
                            step="0.01"
                            min="0"
                            max="100"
                            placeholder="0.00"
                            class="w-full px-3 py-2 bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg text-sm text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:border-blue-500 transition-colors"
                        />
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
                            class="px-4 py-2.5 text-sm font-medium text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-600 transition-colors focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2 dark:focus:ring-offset-slate-800"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="saving"
                            class="inline-flex items-center px-4 py-2.5 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-slate-800 disabled:opacity-60 disabled:cursor-not-allowed"
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

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('fiscalItemsManager', () => ({
        items: [],
        loading: true,
        search: '',
        pagination: null,
        showModal: false,
        editing: false,
        saving: false,
        error: '',
        form: { plu: '', name: '', vat: 0 },

        async init() {
            await this.fetchItems();
        },

        async fetchItems(page = 1) {
            this.loading = true;
            try {
                let url = '/api/fiscal-items?page=' + page;
                if (this.search) url += '&search=' + encodeURIComponent(this.search);
                const r = await window.POS.api(url);
                this.items = r.data?.data || r.data || [];
                this.pagination = r.meta || r.data?.meta || { current_page: 1, last_page: 1, total: 0 };
            } catch (e) {
                this.items = [];
            } finally {
                this.loading = false;
            }
        },

        openAdd() {
            this.editing = false;
            this.error = '';
            this.form = { plu: '', name: '', vat: 0 };
            this.showModal = true;
        },

        openEdit(item) {
            this.editing = true;
            this.error = '';
            this.form = { ...item };
            this.showModal = true;
        },

        async save() {
            this.saving = true;
            this.error = '';
            try {
                const method = this.editing ? 'PUT' : 'POST';
                const url = this.editing ? '/api/fiscal-items/' + this.form.plu : '/api/fiscal-items';
                await window.POS.api(url, { method, body: JSON.stringify(this.form) });
                this.showModal = false;
                this.fetchItems();
            } catch (e) {
                this.error = e.message || 'Failed to save fiscal item.';
            } finally {
                this.saving = false;
            }
        },

        async deleteItem(plu) {
            if (!confirm('Delete fiscal item ' + plu + '?')) return;
            try {
                await window.POS.api('/api/fiscal-items/' + plu, { method: 'DELETE' });
                this.fetchItems();
            } catch (e) {
                alert(e.message);
            }
        },
    }));
});
</script>
@endsection
