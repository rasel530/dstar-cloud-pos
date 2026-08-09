@extends('layouts.app')

@section('title', 'Products')

@section('content')
<div x-data="productsManager" class="flex flex-col h-full">
    <div class="flex items-center justify-between px-4 sm:px-6 py-3 sm:py-4 shrink-0">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white">Products</h1>
            <p class="text-gray-500 dark:text-gray-400 text-sm mt-1">Manage your product catalogue</p>
        </div>
        <div class="flex items-center gap-2">
            <button @click="openAdd()" class="bg-blue-500 hover:bg-blue-600 text-white text-sm font-medium px-4 py-2 rounded-lg transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Add Product
            </button>
            <button @click="showTransferModal = true" x-cloak x-show="$store.sys.isMulti()" class="bg-purple-500 hover:bg-purple-600 text-white text-sm font-medium px-4 py-2 rounded-lg transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                Transfer Stock
            </button>
            <button @click="$refs.stockFile.click()" :disabled="uploadingStock" class="bg-green-500 hover:bg-green-600 text-white text-sm font-medium px-4 py-2 rounded-lg transition flex items-center gap-2 disabled:opacity-50">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                <span x-show="!uploadingStock">Upload Stock</span>
                <span x-cloak x-show="uploadingStock">Uploading...</span>
            </button>
            <input type="file" x-ref="stockFile" @change="handleStockUpload" accept=".csv" class="hidden"/>
        </div>
    </div>

    <div class="px-4 sm:px-6 flex-1 overflow-hidden flex flex-col pb-4">
        <div class="bg-white dark:bg-[#1a1f3d] rounded-xl border border-gray-100 dark:border-white/5 flex flex-col flex-1 overflow-hidden">
            <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-100 dark:border-white/5 flex flex-col sm:flex-row gap-3 sm:items-center sm:justify-between shrink-0">
                <div class="relative flex-1 max-w-md">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 dark:text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" x-model="search" @input.debounce.300ms="fetchProducts()" placeholder="Search products by name, code..." class="w-full bg-gray-50 dark:bg-[#0f1535] border border-gray-300 dark:border-white/10 rounded-lg pl-10 pr-4 py-2 text-sm text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                </div>
                <span class="text-xs text-gray-500 dark:text-white/50" x-show="products.length" x-text="'Showing ' + products.length + ' results'"></span>
            </div>

            <div x-show="loading" class="flex justify-center py-12 flex-1">
                <svg class="animate-spin h-6 w-6 text-gray-400 dark:text-white/30" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
            </div>

            <template x-if="!loading && products.length">
                <div class="overflow-x-auto flex-1">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-100 dark:border-white/5">
                                <th class="text-left text-xs font-medium text-gray-500 dark:text-white/50 uppercase tracking-wider px-6 py-3">Name</th>
                                <th class="text-left text-xs font-medium text-gray-500 dark:text-white/50 uppercase tracking-wider px-6 py-3">Code</th>
                                <th class="text-right text-xs font-medium text-gray-500 dark:text-white/50 uppercase tracking-wider px-6 py-3">Price</th>
                                <th class="text-right text-xs font-medium text-gray-500 dark:text-white/50 uppercase tracking-wider px-6 py-3">Cost</th>
                                <th class="text-right text-xs font-medium text-gray-500 dark:text-white/50 uppercase tracking-wider px-6 py-3">Stock</th>
                                 <th class="text-right text-xs font-medium text-gray-500 dark:text-white/50 uppercase tracking-wider px-6 py-3" x-show="$store.sys.isMulti()">Branch Stock</th>
                                <th class="text-center text-xs font-medium text-gray-500 dark:text-white/50 uppercase tracking-wider px-6 py-3">Status</th>
                                <th class="text-right text-xs font-medium text-gray-500 dark:text-white/50 uppercase tracking-wider px-6 py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="p in products" :key="p.id">
                                <tr class="border-b border-gray-100 dark:border-white/5 hover:bg-gray-50 dark:hover:bg-white/5 transition">
                                    <td class="px-6 py-3 font-medium text-gray-900 dark:text-white">
                                        <div x-text="p.name"></div>
                                        <div class="text-xs text-gray-500 dark:text-white/50" x-text="p.product_group?.name || '\u2014'"></div>
                                    </td>
                                    <td class="px-6 py-3">
                                        <code class="text-xs px-2 py-0.5 rounded bg-gray-100 dark:bg-white/10 text-gray-500 dark:text-white/50 font-mono" x-text="p.code || '\u2014'"></code>
                                    </td>
                                    <td class="px-6 py-3 text-right font-mono text-xs text-gray-700 dark:text-gray-300" x-text="formatMoney(p.price)"></td>
                                    <td class="px-6 py-3 text-right font-mono text-xs text-gray-500 dark:text-gray-400" x-text="formatMoney(p.cost)"></td>
                                    <td class="px-6 py-3 text-right">
                                        <span class="inline-flex text-sm font-medium" :class="p.stock <= 0 ? 'text-red-600 dark:text-red-400' : (p.stock < 10 ? 'text-amber-600 dark:text-amber-400' : 'text-gray-700 dark:text-gray-300')" x-text="p.stock"></span>
                                    </td>
                                    <td class="px-6 py-3 text-right" x-show="$store.sys.isMulti()">
                                        <template x-if="p.branch_stocks && p.branch_stocks.length > 0">
                                            <div class="text-xs space-y-0.5">
                                                <template x-for="b in p.branch_stocks" :key="b.branch_id">
                                                    <div class="flex justify-end gap-2"><span class="text-gray-400 dark:text-white/30" x-text="b.branch_name?.substring(0,3) || '--'"></span><span class="font-medium" :class="b.stock <= 0 ? 'text-red-500' : (b.stock < b.minimum ? 'text-amber-500' : 'text-gray-700 dark:text-gray-300')" x-text="b.stock"></span></div>
                                                </template>
                                            </div>
                                        </template>
                                        <span x-show="!p.branch_stocks || p.branch_stocks.length === 0" class="text-xs text-gray-400">--</span>
                                    </td>
                                    <td class="px-6 py-3 text-center">
                                        <button @click="toggleStatus(p)" class="relative inline-flex h-5 w-9 items-center rounded-full transition" :class="p.is_enabled ? 'bg-blue-500' : 'bg-gray-200 dark:bg-white/10'">
                                            <span class="inline-block h-3.5 w-3.5 transform rounded-full bg-white transition shadow-sm" :class="p.is_enabled ? 'translate-x-[18px]' : 'translate-x-[3px]'"></span>
                                        </button>
                                    </td>
                                    <td class="px-6 py-3 text-right">
                                        <div class="flex items-center justify-end gap-1">
                                            <button @click="openEdit(p)" class="p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-white/5 text-gray-400 dark:text-white/50 hover:text-blue-400 transition" title="Edit">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            </button>
                                            <button @click="deleteProduct(p.id)" class="p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-white/5 text-gray-400 dark:text-white/50 hover:text-red-400 transition" title="Delete">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </template>

            <div x-show="!loading && products.length === 0" class="text-center py-12 text-gray-400 dark:text-white/30 flex-1 flex items-center justify-center">
                <svg class="w-12 h-12 mx-auto mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                <p class="text-sm">No products found</p>
            </div>

            <div x-show="pagination && pagination.total > 0" class="flex items-center justify-between px-4 sm:px-6 py-3 border-t border-gray-100 dark:border-white/5 shrink-0">
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    <span x-text="'Page ' + pagination.current_page + ' of ' + pagination.last_page"></span>
                    <span class="mx-1 text-gray-400">&middot;</span>
                    <span x-text="pagination.total + ' products'"></span>
                </p>
                <div class="flex gap-2">
                    <button @click="fetchProducts(pagination.current_page - 1)" :disabled="!pagination.prev_page_url" class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 disabled:opacity-40 disabled:cursor-not-allowed transition"><svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>Prev</button>
                    <button @click="fetchProducts(pagination.current_page + 1)" :disabled="!pagination.next_page_url" class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 disabled:opacity-40 disabled:cursor-not-allowed transition">Next<svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg></button>
                </div>
            </div>
        </div>
    </div>

    {{-- Add/Edit Product Modal --}}
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-start justify-center pt-10" @click.self="showModal = false">
        <div class="fixed inset-0 bg-black/60"></div>
        <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-2xl w-full max-w-lg mx-4 max-h-[85vh] overflow-y-auto border border-gray-200 dark:border-gray-700">
            <div class="sticky top-0 bg-white dark:bg-gray-800 px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between z-10 rounded-t-xl">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white" x-text="editing ? 'Edit Product' : 'Add Product'"></h2>
                <button @click="showModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 text-2xl leading-none">&times;</button>
            </div>
            <form @submit.prevent="save()" class="p-6 space-y-4">
                <div><label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Name *</label><input type="text" x-model="form.name" required placeholder="Product name" class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div><label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Code / SKU</label><input type="text" x-model="form.code" placeholder="e.g. PROD001" class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
                    <div><label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">PLU</label><input type="text" x-model="form.plu" placeholder="e.g. 1001" class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div><label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Price *</label><input type="number" x-model="form.price" step="0.01" min="0" required placeholder="0.00" class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
                    <div><label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Cost</label><input type="number" x-model="form.cost" step="0.01" min="0" placeholder="0.00" class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
                </div>
                <div><label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Product Group</label>
                    <div class="flex gap-2">
                        <select x-model="form.product_group_id" class="flex-1 px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">&mdash; Select Group &mdash;</option>
                            <template x-for="g in productGroups" :key="g.id"><option :value="g.id" x-text="g.name"></option></template>
                        </select>
                        <button type="button" @click="showNewGroup = !showNewGroup" class="px-3 py-2 bg-gray-100 dark:bg-gray-700 text-sm rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors hidden md:inline-block"><span x-text="showNewGroup ? '\u2715' : '+ New'"></span></button>
                    </div>
                    <div x-show="showNewGroup" class="mt-2 flex gap-2"><input type="text" x-model="newGroupName" placeholder="Group name" class="flex-1 px-3 py-1.5 text-sm border rounded-lg"><button type="button" @click="addNewGroup()" class="px-3 py-1.5 bg-blue-600 text-white text-sm rounded-lg">Add</button></div>
                </div>
                <div><label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Measurement Unit</label>
                    <div class="flex gap-2">
                        <select x-model="form.measurement_unit" class="flex-1 px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">&mdash; Select Unit &mdash;</option>
                            <template x-for="(label, value) in measurementUnits" :key="value"><option :value="value" x-text="label"></option></template>
                        </select>
                        <button type="button" @click="showNewUnit = !showNewUnit" class="px-3 py-2 bg-gray-100 dark:bg-gray-700 text-sm rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors hidden md:inline-block"><span x-text="showNewUnit ? '\u2715' : '+ New'"></span></button>
                    </div>
                    <div x-show="showNewUnit" class="mt-2 flex gap-2"><input type="text" x-model="newUnitKey" placeholder="Key (e.g. cup)" class="w-24 px-3 py-1.5 text-sm border rounded-lg"><input type="text" x-model="newUnitName" placeholder="Label (e.g. Cup)" class="flex-1 px-3 py-1.5 text-sm border rounded-lg"><button type="button" @click="addNewUnit()" class="px-3 py-1.5 bg-blue-600 text-white text-sm rounded-lg">Add</button></div>
                </div>
                <div class="flex items-center gap-3 py-1"><button type="button" @click="form.track_inventory = !form.track_inventory" class="relative inline-flex h-5 w-9 items-center rounded-full transition" :class="form.track_inventory ? 'bg-blue-500' : 'bg-gray-200 dark:bg-white/10'"><span class="inline-block h-3.5 w-3.5 transform rounded-full bg-white transition shadow-sm" :class="form.track_inventory ? 'translate-x-[18px]' : 'translate-x-[3px]'"></span></button><span class="text-sm text-gray-700 dark:text-gray-300" x-text="form.track_inventory ? 'Track Inventory' : 'No Inventory Tracking'"></span></div>
                <div class="flex items-center gap-3 py-1" x-show="$store.sys.isMulti()"><button type="button" @click="form.is_global = !form.is_global" class="relative inline-flex h-5 w-9 items-center rounded-full transition" :class="form.is_global ? 'bg-blue-500' : 'bg-gray-200 dark:bg-white/10'"><span class="inline-block h-3.5 w-3.5 transform rounded-full bg-white transition shadow-sm" :class="form.is_global ? 'translate-x-[18px]' : 'translate-x-[3px]'"></span></button><span class="text-sm text-gray-700 dark:text-gray-300" x-text="form.is_global ? 'Global (All Branches)' : 'Branch-Only Product'"></span></div>
                <div x-show="form.track_inventory"><label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Warehouse Stock</label><input type="number" x-model="form.stock_qty" min="0" step="1" class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
                <div x-show="$store.sys.isMulti() && form.track_inventory && branches.length > 0"><label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Branch Stock</label><div class="space-y-1.5"><template x-for="b in branches" :key="b.id"><div class="flex items-center gap-2"><span class="text-xs text-gray-500 dark:text-gray-400 w-20 truncate" x-text="b.name"></span><input type="number" x-model="form.branch_stocks[b.id]" min="0" step="1" class="flex-1 px-2 py-1.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-1 focus:ring-blue-500"></div></template></div></div>
                <div class="flex items-center gap-3 py-1"><button type="button" @click="form.is_enabled = !form.is_enabled" class="relative inline-flex h-5 w-9 items-center rounded-full transition" :class="form.is_enabled ? 'bg-blue-500' : 'bg-gray-200 dark:bg-white/10'"><span class="inline-block h-3.5 w-3.5 transform rounded-full bg-white transition shadow-sm" :class="form.is_enabled ? 'translate-x-[18px]' : 'translate-x-[3px]'"></span></button><span class="text-sm text-gray-700 dark:text-gray-300" x-text="form.is_enabled ? 'Enabled' : 'Disabled'"></span></div>
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <button type="button" @click="showModal = false" class="px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">Cancel</button>
                    <button type="submit" :disabled="saving" class="inline-flex items-center px-4 py-2.5 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors disabled:opacity-60 disabled:cursor-not-allowed"><svg x-show="saving" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg><span x-text="saving ? 'Saving...' : 'Save'"></span></button>
                </div>
            </form>
        </div>
    </div>

    {{-- Transfer Stock Modal --}}
    <div x-show="showTransferModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center" @click.self="showTransferModal = false">
        <div class="fixed inset-0 bg-black/50"></div>
        <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-md p-6 border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Transfer Stock</h3>
                <button @click="showTransferModal = false" class="text-gray-400 hover:text-gray-600">&times;</button>
            </div>
            <div class="space-y-3">
                <div><label class="block text-sm font-medium mb-1">Product Code</label><input type="text" x-model="transferForm.product_code" placeholder="e.g. BEV002" class="w-full px-3 py-2 border rounded-lg text-sm"></div>
                <div><label class="block text-sm font-medium mb-1">Quantity</label><input type="number" x-model="transferForm.quantity" min="1" placeholder="1" class="w-full px-3 py-2 border rounded-lg text-sm"></div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div><label class="block text-sm font-medium mb-1">From Branch</label><select x-model="transferForm.from_branch" class="w-full px-3 py-2 border rounded-lg text-sm"><option value="">Select...</option><template x-for="b in branches" :key="b.id"><option :value="b.name" x-text="b.name"></option></template></select></div>
                    <div><label class="block text-sm font-medium mb-1">To Branch</label><select x-model="transferForm.to_branch" class="w-full px-3 py-2 border rounded-lg text-sm"><option value="">Select...</option><template x-for="b in branches" :key="b.id"><option :value="b.name" x-text="b.name"></option></template></select></div>
                </div>
                <button @click="transferStock()" :disabled="transferring" class="w-full px-4 py-2 bg-purple-600 text-white rounded-lg text-sm font-medium disabled:opacity-50"><span x-show="!transferring">Transfer Stock</span><span x-show="transferring">Transferring...</span></button>
                <p x-show="transferMessage" x-text="transferMessage" class="text-sm text-center" :class="transferError ? 'text-red-500' : 'text-green-600'"></p>
            </div>
        </div>
    </div>

</div>
@endsection
