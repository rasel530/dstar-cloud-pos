@extends('layouts.app')
@section('title', 'Barcode Management')
@section('content')
<div x-data="barcodeManager" class="h-full flex flex-col">
    <div class="flex flex-wrap items-center justify-between gap-4 px-4 lg:px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">
        <div>
            <h1 class="text-lg font-bold text-gray-900 dark:text-white">Barcode Management</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Generate, manage & print product barcodes</p>
        </div>
        <div class="flex gap-2">
            <button @click="printBarcodes()" :disabled="printLoading || selectedIds.size === 0" class="px-4 py-2 bg-purple-600 text-white text-sm font-medium rounded-lg hover:bg-purple-700 transition-colors disabled:opacity-50">
                <span x-text="printLoading ? 'Preparing...' : ('Print Labels' + (selectedIds.size > 0 ? ' (' + selectedIds.size + ')' : ''))"></span>
            </button>
            <button @click="openGenerateModal()" class="px-4 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700 transition-colors">Generate Barcode</button>
            <button @click="openManualModal()" class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">Add Barcode</button>
        </div>
    </div>

    <div class="flex-1 overflow-auto p-4 lg:p-6 bg-gray-50 dark:bg-gray-900">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden mb-6">
            <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex flex-wrap gap-3 items-center">
                <input type="text" x-model="search" @input.debounce.300ms="fetchBarcodes()" placeholder="Search barcode value..." class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white w-full max-w-xs">
                <select x-model="filterType" @change="fetchBarcodes()" class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    <option value="">All Types</option>
                    <option value="CODE_128">CODE-128</option>
                    <option value="EAN_13">EAN-13</option>
                    <option value="UPC_A">UPC-A</option>
                </select>
                <select x-model="filterStatus" @change="fetchBarcodes()" class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    <option value="all">All Status</option>
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-700/50">
                        <tr>
                            <th class="px-4 py-3 text-center text-xs w-10">
                                <input type="checkbox" @change="toggleAll" :checked="allSelected" class="rounded border-gray-300 cursor-pointer">
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Product</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">SKU</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Barcode Image</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Barcode</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Type</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Primary</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Status</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <template x-if="loading"><tr><td colspan="9" class="px-6 py-12 text-center text-gray-400">Loading...</td></tr></template>
                        <template x-if="!loading && barcodes.length === 0"><tr><td colspan="9" class="px-6 py-12 text-center text-gray-400">No barcodes found</td></tr></template>
                        <template x-for="b in barcodes" :key="b.id">
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                <td class="px-4 py-4 text-center">
                                    <input type="checkbox" :checked="selectedIds.has(b.id)" @change="toggleSelect(b.id)" class="rounded border-gray-300 cursor-pointer">
                                </td>
                                <td class="px-4 py-4 text-gray-900 dark:text-white" x-text="b.product?.name || '—'"></td>
                                <td class="px-4 py-4 font-mono text-xs text-gray-500" x-text="b.product?.code || '—'"></td>
                                <td class="px-4 py-3">
                                    <svg x-barcode="b.value" class="h-8 max-w-[100px]"></svg>
                                </td>
                                <td class="px-4 py-4 font-mono text-sm font-medium text-gray-900 dark:text-white" x-text="b.value"></td>
                                <td class="px-4 py-4 text-gray-500 text-xs" x-text="b.barcode_type"></td>
                                <td class="px-4 py-4"><span class="px-2 py-0.5 rounded text-xs font-medium" :class="b.is_primary ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'" x-text="b.is_primary ? 'Yes' : 'No'"></span></td>
                                <td class="px-4 py-4"><span class="px-2 py-0.5 rounded text-xs font-medium" :class="b.is_enabled ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-500'" x-text="b.is_enabled ? 'Active' : 'Inactive'"></span></td>
                                <td class="px-4 py-4 text-right space-x-1">
                                    <button @click="copyBarcode(b.value, $el)" class="text-gray-500 hover:text-gray-700 text-xs p-1" title="Copy">[Copy]</button>
                                    <button @click="editBarcode(b)" class="text-blue-600 hover:text-blue-800 text-xs font-medium">Edit</button>
                                    <button @click="toggleStatus(b.id, b.is_enabled)" class="text-xs font-medium" :class="b.is_enabled ? 'text-red-600 hover:text-red-800' : 'text-green-600 hover:text-green-800'" x-text="b.is_enabled ? 'Deactivate' : 'Activate'"></button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
            <div x-show="page.last_page > 1" class="flex justify-center gap-2 p-4 border-t border-gray-200 dark:border-gray-700">
                <button @click="fetchBarcodes(page.current_page-1)" :disabled="page.current_page<=1" class="px-3 py-1.5 text-sm border rounded-lg disabled:opacity-40">Prev</button>
                <span class="px-3 py-1.5 text-sm text-gray-500" x-text="page.current_page + ' / ' + page.last_page"></span>
                <button @click="fetchBarcodes(page.current_page+1)" :disabled="page.current_page>=page.last_page" class="px-3 py-1.5 text-sm border rounded-lg disabled:opacity-40">Next</button>
            </div>
        </div>
    </div>

    {{-- Generate Barcode Modal --}}
    <div x-show="showGenerateModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click.self="showGenerateModal=false">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl w-full max-w-md mx-4">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Generate Barcode</h3>
                <button @click="showGenerateModal=false" class="text-gray-400 hover:text-gray-600 text-xl">&times;</button>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Product *</label>
                    <select x-model="genForm.product_id" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                        <option value="">Select Product</option>
                        <template x-for="p in products" :key="p.id">
                            <option :value="p.id" x-text="p.name + (p.code ? ' (' + p.code + ')' : '')"></option>
                        </template>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Barcode Type</label>
                    <select x-model="genForm.barcode_type" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                        <option value="CODE_128">CODE-128 (Recommended)</option>
                        <option value="EAN_13">EAN-13</option>
                        <option value="UPC_A">UPC-A</option>
                    </select>
                </div>
                <button @click="generateBarcode()" :disabled="genSaving" class="w-full px-4 py-2.5 text-sm text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 disabled:opacity-50" x-text="genSaving ? 'Generating...' : 'Generate Barcode'"></button>
                <div x-show="genResult" class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg text-center">
                    <p class="text-xs text-gray-500 mb-1">Generated Barcode</p>
                    <p class="text-2xl font-mono font-bold text-gray-900 dark:text-white" x-text="genResult"></p>
                    <p class="text-xs text-gray-400 mt-1">Saved to product</p>
                </div>
                <div x-show="genError" class="text-sm text-red-500" x-text="genError"></div>
            </div>
            <div class="flex justify-end gap-3 px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                <button @click="showGenerateModal=false" class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg">Close</button>
            </div>
        </div>
    </div>

    {{-- Add/Edit Barcode Modal --}}
    <div x-show="showManualModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click.self="showManualModal=false">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl w-full max-w-md mx-4">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white" x-text="editing ? 'Edit Barcode' : 'Add Barcode'"></h3>
                <button @click="showManualModal=false" class="text-gray-400 hover:text-gray-600 text-xl">&times;</button>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Product *</label>
                    <select x-model="form.product_id" :disabled="editing || products.length === 0" @change="onProductSelect()" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm disabled:opacity-50">
                        <option value="">Select Product</option>
                        <template x-for="p in products" :key="p.id">
                            <option :value="p.id" x-text="p.name + (p.code ? ' (' + p.code + ')' : '')"></option>
                        </template>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Barcode Value *</label>
                    <div class="flex gap-2">
                        <input type="text" x-model="form.value" placeholder="Enter or scan barcode number..." class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm font-mono">
                        <button type="button" x-show="form.product_id && !form.value && !existingBarcode" @click="generateNewBarcode()" :disabled="genSaving" class="px-3 py-2 bg-emerald-600 text-white text-sm rounded-lg hover:bg-emerald-700 disabled:opacity-50 whitespace-nowrap" x-text="genSaving ? '...' : 'Generate'"></button>
                    </div>
                    <p x-show="existingBarcode" class="mt-1 text-xs text-green-600 dark:text-green-400">
                        <span x-text="'Existing: ' + existingBarcode"></span>
                        <button type="button" @click="form.value = ''; existingBarcode = ''" class="ml-2 text-blue-600 hover:underline">Override</button>
                    </p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Barcode Type</label>
                    <select x-model="form.barcode_type" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                        <option value="CODE_128">CODE-128</option>
                        <option value="EAN_13">EAN-13</option>
                        <option value="UPC_A">UPC-A</option>
                    </select>
                </div>
                <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                    <input type="checkbox" x-model="form.is_primary" class="rounded border-gray-300"> Primary Barcode
                </label>
                <div x-show="formError" class="text-sm text-red-500" x-text="formError"></div>
            </div>
            <div class="flex justify-end gap-3 px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                <button @click="showManualModal=false" class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg">Cancel</button>
                <button @click="saveBarcode()" :disabled="saving" class="px-4 py-2 text-sm text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50" x-text="saving ? 'Saving...' : 'Save'"></button>
            </div>
        </div>
    </div>

    {{-- Print Preview Modal --}}
    <div x-show="showPrintModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click.self="showPrintModal=false">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl w-full max-w-2xl mx-4 max-h-[85vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Print Preview — <span x-text="selectedIds.size"></span> label(s)</h3>
                <button @click="showPrintModal=false" class="text-gray-400 hover:text-gray-600 text-xl">&times;</button>
            </div>
            <div class="p-6 space-y-4">
                <div class="flex items-center gap-3">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Label Size:</span>
                    <select x-model="printLabelSize" class="px-3 py-1.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        <option value="small">Small</option>
                        <option value="medium">Medium</option>
                        <option value="large">Large</option>
                    </select>
                    <span class="text-xs text-gray-400 ml-auto" x-text="selectedIds.size + ' label(s) selected'"></span>
                </div>

                {{-- Label Previews --}}
                <div class="grid gap-3" :class="printLabelSize==='small'?'grid-cols-3':'grid-cols-2'">
                    <template x-for="b in printItems" :key="b.id">
                        <div class="border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 p-3 flex flex-col items-center gap-1.5" :class="printLabelSize==='large'?'p-4':''">
                            <span class="text-[10px] font-semibold text-gray-900 dark:text-white truncate max-w-full text-center" x-text="b.product?.name || 'Product'"></span>
                            <span x-show="b.product?.code" class="text-[9px] text-gray-400 font-mono" x-text="b.product?.code"></span>
                            <svg x-barcode="b.value" class="w-full" :class="printLabelSize==='small'?'h-8':'h-10'" style="max-width:100%"></svg>
                            <span class="text-[10px] font-mono text-gray-600 dark:text-gray-300" x-text="b.value"></span>
                            <span x-show="b.product?.price" class="text-sm font-bold text-gray-900 dark:text-white" x-text="Alpine.store('currency')?.symbol + Number(b.product?.price || 0).toFixed(2)"></span>
                        </div>
                    </template>
                </div>

                <div class="flex justify-end gap-3">
                    <button @click="showPrintModal=false" class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg">Cancel</button>
                    <button @click="sendPrintJob()" :disabled="printSending" class="px-6 py-2.5 text-sm text-white bg-purple-600 rounded-lg hover:bg-purple-700 disabled:opacity-50 font-medium" x-text="printSending ? 'Sending...' : 'Print Now'"></button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
