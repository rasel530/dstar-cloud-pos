@extends('layouts.app')

@section('title', 'Inventory')

@section('content')
<div x-data="inventoryManager" class="flex flex-col h-full">
    <div class="flex items-center justify-between px-4 sm:px-6 py-3 sm:py-4 shrink-0">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white">Inventory</h1>
            <p class="text-gray-500 dark:text-gray-400 text-sm mt-1">Manage warehouse stock levels</p>
        </div>
        <div class="flex items-center gap-2">
            <button @click="openAddWarehouse()" class="bg-blue-500 hover:bg-blue-600 text-white text-sm font-medium px-4 py-2 rounded-lg transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Add Warehouse
            </button>
            <button @click="$refs.stockFile.click()" :disabled="uploadingStock" class="bg-green-500 hover:bg-green-600 text-white text-sm font-medium px-4 py-2 rounded-lg transition flex items-center gap-2 disabled:opacity-50">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                <span x-show="!uploadingStock">Upload Stock</span>
                <span x-cloak x-show="uploadingStock">Uploading...</span>
            </button>
            <input type="file" x-ref="stockFile" @change="handleStockUpload" accept=".csv" class="hidden"/>
        </div>
    </div>

    <div class="px-4 sm:px-6 flex-1 flex flex-col overflow-hidden space-y-4 sm:space-y-6">
        {{-- Warehouse Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4 shrink-0">
            <template x-for="w in warehouses" :key="w.id">
                 <div class="bg-white dark:bg-[#1a1f3d] rounded-xl border border-gray-100 dark:border-white/5 p-5 hover:shadow-md transition-shadow"
                      :class="selectedWarehouse === w.id ? 'ring-2 ring-blue-500' : ''">
                     <div class="flex items-center justify-between mb-3">
                         <div class="cursor-pointer" @click="selectWarehouse(w.id)">
                             <h3 class="text-lg font-semibold text-gray-900 dark:text-white" x-text="w.name"></h3>
                             <span class="text-xs text-gray-400" x-text="w.is_default ? 'Default Warehouse' : ''"></span>
                         </div>
                         <div class="flex items-center gap-1">
                             <span class="px-2 py-0.5 rounded-full text-xs font-medium" :class="w.is_default ? 'bg-blue-500/10 text-blue-400' : 'bg-gray-500/10 text-gray-400'" x-text="w.is_default ? 'Default' : ''"></span>
                             <button @click.stop="openEditWarehouse(w)" class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-xs font-medium text-gray-500 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/30 transition-colors" title="Edit">
                                 <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/></svg>
                                 <span>Edit</span>
                             </button>
                             <button @click.stop="deleteWarehouse(w.id)" class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-xs font-medium text-gray-500 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30 transition-colors" title="Delete">
                                 <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                                 <span>Delete</span>
                             </button>
                         </div>
                     </div>
                    <div class="grid grid-cols-3 gap-3 text-center">
                        <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-2">
                            <div class="text-xs text-gray-500 dark:text-gray-400">Products</div>
                            <div class="text-lg font-bold text-gray-900 dark:text-white" x-text="w.stocks_count || 0"></div>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-2">
                            <div class="text-xs text-gray-500 dark:text-gray-400">Documents</div>
                            <div class="text-lg font-bold text-gray-900 dark:text-white" x-text="w.documents_count || 0"></div>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-2">
                            <div class="text-xs text-gray-500 dark:text-gray-400">Movements</div>
                            <div class="text-lg font-bold text-gray-900 dark:text-white" x-text="w.stock_movements_count || 0"></div>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        {{-- Stock Table for Selected Warehouse --}}
        <div x-show="selectedWarehouse" class="bg-white dark:bg-[#1a1f3d] rounded-xl border border-gray-100 dark:border-white/5 flex flex-col flex-1 overflow-hidden min-h-0">
            <div class="px-4 sm:px-6 py-3 border-b border-gray-100 dark:border-white/5 flex items-center justify-between shrink-0">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white" x-text="'Stock in ' + (selectedWarehouseName || 'Warehouse')"></h3>
                <button @click="selectedWarehouse = null" class="text-gray-400 hover:text-gray-600">&times;</button>
            </div>
            <div x-show="stockLoading" class="flex justify-center py-12 flex-1 items-center">
                <svg class="animate-spin h-6 w-6 text-gray-400 dark:text-white/30" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
            </div>
            <template x-if="!stockLoading && warehouseStocks.length">
                <div class="overflow-auto flex-1">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-100 dark:border-white/5">
                                <th class="text-left text-xs font-medium text-gray-500 dark:text-white/50 uppercase px-3 sm:px-6 py-3">Product</th>
                                <th class="text-left text-xs font-medium text-gray-500 dark:text-white/50 uppercase px-3 sm:px-6 py-3">Code</th>
                                <th class="text-right text-xs font-medium text-gray-500 dark:text-white/50 uppercase px-3 sm:px-6 py-3">Quantity</th>
                                 <th class="text-right text-xs font-medium text-gray-500 dark:text-white/50 uppercase px-3 sm:px-6 py-3" x-show="$store.sys.isMulti()">Branch Stock</th>
                                <th class="text-right text-xs font-medium text-gray-500 dark:text-white/50 uppercase px-3 sm:px-6 py-3">Adjust</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="s in warehouseStocks" :key="s.product_id">
                                <tr class="border-b border-gray-100 dark:border-white/5 hover:bg-gray-50 dark:hover:bg-white/5">
                                    <td class="px-3 sm:px-6 py-3 font-medium text-gray-900 dark:text-white" x-text="s.product_name"></td>
                                    <td class="px-3 sm:px-6 py-3 font-mono text-xs text-gray-500 dark:text-white/50" x-text="s.product_code || '--'"></td>
                                    <td class="px-3 sm:px-6 py-3 text-right">
                                        <span class="font-medium" :class="s.quantity <= 0 ? 'text-red-500' : 'text-gray-900 dark:text-white'" x-text="s.quantity"></span>
                                    </td>
                                     <td class="px-6 py-3 text-right text-xs text-gray-500" x-show="$store.sys.isMulti()" x-text="s.branch_summary || '--'"></td>
                                    <td class="px-3 sm:px-6 py-3 text-right">
                                        <div class="flex items-center justify-end gap-1">
                                            <input type="number" x-model="adjustQty[s.product_id]" step="1" class="w-20 px-2 py-1 border rounded text-xs text-right" :placeholder="s.quantity">
                                            <button @click="quickAdjust(s.product_id)" class="px-2 py-1 bg-blue-500 text-white text-xs rounded hover:bg-blue-600">Set</button>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table></div>
            </template>
            <div x-show="!stockLoading && stockLastPage > 1" class="flex items-center justify-between gap-3 px-4 sm:px-6 py-3 border-t border-gray-100 dark:border-white/5 shrink-0">
                <span class="text-xs text-gray-500 dark:text-white/50" x-text="stockTotal + ' products'"></span>
                <span class="text-xs text-gray-500 dark:text-white/50" x-text="'Page ' + stockPage + ' of ' + stockLastPage"></span>
                <div class="flex items-center gap-2">
                    <button @click="loadStockPage(stockPage - 1)" :disabled="stockPage <= 1" class="px-3 py-1.5 text-xs font-medium rounded-lg border border-gray-200 dark:border-white/10 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-white/5 disabled:opacity-40 disabled:cursor-not-allowed transition">Prev</button>
                    <button @click="loadStockPage(stockPage + 1)" :disabled="stockPage >= stockLastPage" class="px-3 py-1.5 text-xs font-medium rounded-lg border border-gray-200 dark:border-white/10 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-white/5 disabled:opacity-40 disabled:cursor-not-allowed transition">Next</button>
                </div>
            </div>
            <div x-show="!stockLoading && !warehouseStocks.length" class="p-4 sm:p-6 text-center flex-1 flex flex-col items-center justify-center">
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">No stock records in this warehouse yet.</p>
                <div class="max-w-sm mx-auto flex flex-col sm:flex-row gap-2">
                    <select x-model="addProductId" class="flex-1 px-3 py-2 border rounded-lg text-sm">
                        <option value="">— Select product —</option>
                        <template x-for="p in allProducts" :key="p.id">
                            <option :value="p.id" x-text="p.name + ' (' + (p.code || 'no code') + ')'"></option>
                        </template>
                    </select>
                    <input type="number" x-model="addProductQty" min="1" placeholder="Qty" class="w-20 px-3 py-2 border rounded-lg text-sm">
                    <button @click="addStockToWarehouse()" :disabled="!addProductId || !addProductQty" class="px-4 py-2 bg-blue-500 text-white text-sm rounded-lg hover:bg-blue-600 disabled:opacity-50">Add</button>
                </div>
                <p class="text-xs text-gray-400 mt-3">Or click <strong>Upload Stock</strong> to bulk import via CSV</p>
            </div>
        </div>
    </div>

    {{-- Add Warehouse Modal --}}
    <div x-show="showWarehouseModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center" @click.self="showWarehouseModal = false">
        <div class="fixed inset-0 bg-black/50"></div>
        <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-sm p-6 border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white" x-text="editingWarehouseId ? 'Edit Warehouse' : 'Add Warehouse'"></h3>
                <button @click="showWarehouseModal = false" class="text-gray-400 hover:text-gray-600">&times;</button>
            </div>
            <div class="space-y-3">
                <div><label class="block text-sm font-medium mb-1">Name *</label><input type="text" x-model="warehouseForm.name" required class="w-full px-3 py-2 border rounded-lg text-sm"></div>
                <div class="flex items-center gap-2"><input type="checkbox" x-model="warehouseForm.is_default" id="whDef"><label for="whDef" class="text-sm">Set as default</label></div>
                <button @click="saveWarehouse()" :disabled="warehouseSaving" class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium disabled:opacity-50" x-text="editingWarehouseId ? 'Update' : 'Save'"></button>
            </div>
        </div>
    </div>
</div>
@endsection
