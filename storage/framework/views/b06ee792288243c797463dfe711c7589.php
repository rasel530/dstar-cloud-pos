<?php $__env->startSection('title', 'Point of Sale'); ?>

<?php $__env->startSection('content'); ?>
<div x-data="posCart" x-init="init()" class="flex h-full overflow-hidden bg-gray-50 dark:bg-[#0f1535]">

    
    <div x-show="showAuthRedirect" x-cloak class="absolute inset-0 z-50 flex items-center justify-center bg-gray-900/80">
        <div class="bg-white dark:bg-[#1a1f3d] rounded-xl p-8 shadow-2xl text-center max-w-sm mx-4">
            <svg class="w-16 h-16 mx-auto text-blue-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Authentication Required</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Please sign in to access the POS system.</p>
            <a href="/login" class="inline-block bg-blue-500 hover:bg-blue-600 text-white font-medium px-6 py-2.5 rounded-lg transition">Sign In</a>
        </div>
    </div>

    
    <div class="flex-1 flex flex-col overflow-hidden">
        
        <div class="px-4 pt-4 pb-3 shrink-0">
            <div class="relative flex gap-2">
                <div class="relative flex-1">
                    <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400 dark:text-white/30 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input
                        type="text"
                        x-model="searchTerm"
                        @input.debounce.300ms="searchProducts()"
                        @keydown.enter.prevent="handleBarcodeSearch()"
                        placeholder="Search product, code or scan barcode..."
                        autofocus
                        class="w-full bg-gray-100 dark:bg-[#1a1f3d] border border-gray-200 dark:border-white/10 rounded-lg pl-11 pr-4 py-2.5 text-sm text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-white/40 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 dark:focus:border-blue-500 transition"
                    >
                </div>
                <button @click="document.getElementById('bulkFileInput').click()" title="Upload products CSV" class="shrink-0 px-3 py-2.5 rounded-lg bg-gray-100 dark:bg-[#1a1f3d] border border-gray-200 dark:border-white/10 text-gray-500 dark:text-white/40 hover:text-blue-500 dark:hover:text-blue-400 hover:border-blue-500/50 transition text-sm flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
                    <span class="hidden sm:inline">Import</span>
                </button>
                <input type="file" id="bulkFileInput" @change="handleFileUpload($event)" accept=".csv,.json" class="hidden">
                <div x-show="uploading" class="absolute inset-0 flex items-center justify-center bg-gray-900/50 rounded-lg z-10">
                    <svg class="animate-spin w-5 h-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                </div>
            </div>
        </div>

        
        <div class="px-4 pb-3 shrink-0" x-data="pillScroller" x-init @resize.window="checkOverflow()" @resize.window="checkOverflow()" @resize.window="checkOverflow()">
            <div class="relative">
                <button
                    x-show="canScrollLeft"
                    x-cloak
                    @click="scrollLeft()"
                    class="absolute -left-1 top-1/2 z-10 w-7 h-7 rounded-full bg-white dark:bg-gray-700 shadow border border-gray-200 dark:border-gray-600 flex items-center justify-center hover:bg-gray-50 dark:hover:bg-gray-600 transition -translate-y-1/2"
                    aria-label="Scroll pills left"
                >
                    <svg class="w-3.5 h-3.5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                </button>
                <button
                    x-show="canScrollRight"
                    x-cloak
                    @click="scrollRight()"
                    class="absolute -right-1 top-1/2 z-10 w-7 h-7 rounded-full bg-white dark:bg-gray-700 shadow border border-gray-200 dark:border-gray-600 flex items-center justify-center hover:bg-gray-50 dark:hover:bg-gray-600 transition -translate-y-1/2"
                    aria-label="Scroll pills right"
                >
                    <svg class="w-3.5 h-3.5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </button>

                <div
                    x-ref="pillTrack"
                    @scroll.debounce.50ms="checkOverflow()"
                    class="flex gap-2 overflow-x-auto hide-scrollbar scroll-smooth px-0.5"
                    style="-webkit-overflow-scrolling: touch; scroll-behavior: smooth;"
                >
                    <button
                        @click="activeCategory = null; loadProducts()"
                        :class="activeCategory === null
                            ? 'bg-blue-500 text-white shadow-sm'
                            : 'bg-white dark:bg-[#1a1f3d] text-gray-600 dark:text-white/50 hover:bg-gray-100 dark:hover:bg-white/5 border border-gray-200 dark:border-white/5'"
                        class="px-3 sm:px-4 py-1.5 rounded-full text-xs sm:text-sm font-medium whitespace-nowrap transition-all shrink-0"
                    >All</button>
                    <template x-for="cat in categories" :key="cat.id">
                        <button
                            @click="activeCategory = cat.id; loadProducts()"
                            :class="activeCategory === cat.id
                                ? 'bg-blue-500 text-white shadow-sm'
                                : 'bg-white dark:bg-[#1a1f3d] text-gray-600 dark:text-white/50 hover:bg-gray-100 dark:hover:bg-white/5 border border-gray-200 dark:border-white/5'"
                            class="px-3 sm:px-4 py-1.5 rounded-full text-xs sm:text-sm font-medium whitespace-nowrap transition-all shrink-0"
                            x-text="cat.name"
                        ></button>
                    </template>
                </div>
            </div>
        </div>

        
        <div class="flex-1 overflow-y-auto px-4 pb-4">
            <template x-if="filteredProducts.length === 0">
                <div class="flex items-center justify-center h-full text-gray-400 dark:text-white/20 text-lg font-medium">
                    <span x-text="searchTerm ? 'No products found' : 'No products available'"></span>
                </div>
            </template>
            <div class="grid gap-2" :style="gridStyle">
                <template x-for="(product, idx) in filteredProducts" :key="product.id">
                    <button
                        @click="addToCart(product)"
                        :disabled="product.track_inventory && stockMap[product.id] && stockMap[product.id].current_stock <= 0"
                        class="rounded-xl p-2 sm:p-3 lg:p-4 text-white text-center cursor-pointer hover:brightness-110 hover:scale-[1.02] active:scale-95 transition-all duration-150 shadow-md flex flex-col items-center justify-center gap-1 sm:gap-1.5 min-h-[72px] sm:min-h-[80px] lg:min-h-[90px] disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:brightness-100 disabled:hover:scale-100"
                        :style="'background-color: ' + colorForProduct(product)"
                    >
                        <span class="font-semibold text-xs sm:text-sm leading-tight" x-text="product.name"></span>
                        <span class="text-[10px] sm:text-xs opacity-80 font-mono tracking-tight" x-text="formatMoney(product.price)"></span>
                        <span
                            x-show="stockMap[product.id] && product.track_inventory"
                            class="text-[10px] opacity-70"
                            :class="stockMap[product.id]?.current_stock <= 0 ? 'text-red-200' : 'text-green-200'"
                            x-text="stockMap[product.id]?.current_stock > 0 ? 'Stock: ' + stockMap[product.id].current_stock : 'Out of Stock'"
                        ></span>
                    </button>
                </template>
            </div>
        </div>
    </div>

    
    <button @click="cartOpen = !cartOpen" class="lg:hidden fixed bottom-4 right-4 z-50 w-14 h-14 rounded-full bg-blue-600 hover:bg-blue-700 text-white shadow-xl flex items-center justify-center transition-transform active:scale-95">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/>
        </svg>
        <span x-show="items.length > 0" class="absolute -top-1 -right-1 bg-red-500 text-white text-xs w-5 h-5 rounded-full flex items-center justify-center font-bold" x-text="items.length"></span>
    </button>

    
    <div x-show="cartOpen" @click="cartOpen = false" class="lg:hidden fixed inset-0 bg-black/60 z-35" x-cloak></div>

    
    <div class="w-72 bg-white dark:bg-[#1a1f3d] flex flex-col shrink-0 border-l border-gray-200 dark:border-white/10 fixed lg:relative inset-y-0 right-0 z-40 transition-transform duration-300 lg:translate-x-0 shadow-2xl lg:shadow-none" :class="cartOpen ? 'translate-x-0 pointer-events-auto' : 'translate-x-full lg:translate-x-0 pointer-events-none lg:pointer-events-auto'" style="overflow: visible;">


        
        <div class="px-4 py-3 border-b border-gray-100 dark:border-white/10 flex items-center justify-between shrink-0">
            <h3 class="font-bold text-sm text-gray-800 dark:text-white/90 uppercase tracking-wide">Current Order</h3>
            <span
                x-show="items.length > 0"
                x-text="items.length"
                class="bg-blue-500 text-white text-xs w-5 h-5 rounded-full flex items-center justify-center font-bold"
            ></span>
        </div>
        
        <div class="px-3 py-2 border-b border-gray-100 dark:border-white/10 space-y-2 shrink-0">
            <div x-show="posSettings._settingsReady" class="space-y-2">
            <div class="flex gap-1.5">
                <button @click="serviceType = 0" x-show="posSettings.dine_in_enabled !== false"
                    :class="serviceType === 0 ? 'bg-blue-500 text-white' : 'bg-gray-100 dark:bg-white/10 text-gray-500 dark:text-white/60'"
                    class="flex-1 py-1.5 rounded text-xs font-medium transition-colors">Dine-in</button>
                <button @click="serviceType = 1" x-show="posSettings.takeaway_enabled !== false"
                    :class="serviceType === 1 ? 'bg-amber-500 text-white' : 'bg-gray-100 dark:bg-white/10 text-gray-500 dark:text-white/60'"
                    class="flex-1 py-1.5 rounded text-xs font-medium transition-colors">Takeaway</button>
            </div>
            <div x-show="serviceType === 0 && posSettings.table_management_enabled !== false" class="flex items-center gap-2">
                <span class="text-xs text-gray-400 dark:text-white/50 shrink-0">Table:</span>
                <input type="text" x-model="tableNumber" placeholder="e.g. A5"
                    class="flex-1 h-7 text-xs rounded border border-gray-200 dark:border-white/15 bg-gray-50 dark:bg-white/10 text-gray-800 dark:text-white/90 px-2 focus:outline-none focus:ring-1 focus:ring-blue-500/50">
            </div>
            </div>

            
            <div class="relative">
                <div class="flex items-center gap-1.5 text-xs">
                    <svg class="w-3.5 h-3.5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                    <span x-show="!showCustomerSearch" class="text-gray-500 dark:text-white/70 cursor-pointer" @click="showCustomerSearch = true"
                        x-text="selectedCustomer ? selectedCustomer.name + ' (selected)' : 'Walk-in Customer'"></span>
                </div>
                <div x-show="showCustomerSearch" class="mt-1">
                    <input type="text" x-model="customerSearch" @input.debounce.200ms="searchCustomers()" @keydown.escape="showCustomerSearch = false; searchedCustomers = []"
                        placeholder="Search name, phone, email or code..." autofocus
                        class="w-full h-7 text-xs rounded border border-blue-500 dark:border-blue-400 bg-white dark:bg-white/10 text-gray-800 dark:text-white/90 px-2 focus:outline-none">
                    <button x-show="showCustomerSearch" @click="showCustomerSearch = false; searchedCustomers = []" class="absolute right-1 top-0.5 text-gray-400 hover:text-gray-600 text-xs">&times;</button>
                </div>
            <div x-show="searchedCustomers.length > 0" class="absolute top-full left-0 right-0 mt-0.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-lg shadow-2xl max-h-40 overflow-y-auto" style="z-index: 100;">
                <template x-if="!showQuickCustomerForm">
                    <div class="px-3 py-1.5 border-b border-gray-100 dark:border-gray-700/50">
                        <button @click="showQuickCustomerForm = true; quickCustomerPhone = customerSearch" class="text-xs text-blue-600 dark:text-blue-400 hover:text-blue-700 font-medium">+ Quick Add Customer</button>
                    </div>
                </template>
                <template x-if="showQuickCustomerForm">
                    <div class="p-3 border-b border-gray-100 dark:border-gray-700/50 space-y-2">
                        <div>
                            <label class="text-[10px] text-gray-500 dark:text-gray-400 block mb-0.5">Phone Number *</label>
                            <input type="text" x-model="quickCustomerPhone" placeholder="01XXXXXXXXX" @keydown.enter="quickCreateCustomer()" @keydown.escape="showQuickCustomerForm = false"
                                class="w-full h-7 text-xs rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-white/10 text-gray-800 dark:text-white/90 px-2 focus:outline-none focus:ring-1 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="text-[10px] text-gray-500 dark:text-gray-400 block mb-0.5">Name (optional)</label>
                            <input type="text" x-model="quickCustomerName" placeholder="Customer name" @keydown.enter="quickCreateCustomer()" @keydown.escape="showQuickCustomerForm = false"
                                class="w-full h-7 text-xs rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-white/10 text-gray-800 dark:text-white/90 px-2 focus:outline-none focus:ring-1 focus:ring-blue-500">
                        </div>
                        <div class="flex gap-2">
                            <button @click="quickCreateCustomer()" :disabled="quickCustomerSaving" class="flex-1 px-2 py-1 text-xs font-medium text-white bg-green-600 hover:bg-green-700 disabled:opacity-50 rounded transition-colors">
                                <span x-show="!quickCustomerSaving">Save & Continue</span>
                                <span x-show="quickCustomerSaving">Saving...</span>
                            </button>
                            <button @click="showQuickCustomerForm = false; quickCustomerPhone = ''; quickCustomerName = ''" class="flex-1 px-2 py-1 text-xs text-gray-600 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded transition-colors">Cancel</button>
                        </div>
                    </div>
                </template>
                <template x-for="c in searchedCustomers" :key="c.id">
                    <div @click="selectCustomer(c)" class="w-full text-left px-3 py-2 text-xs hover:bg-blue-50 dark:hover:bg-blue-500/10 text-gray-800 dark:text-white/90 border-b border-gray-100 dark:border-gray-700/50 last:border-0 cursor-pointer flex items-center justify-between">
                        <span class="font-medium" x-text="c.name"></span>
                        <span class="text-gray-400 dark:text-gray-500 ml-2 truncate" x-text="c.phone_number || c.email || ''"></span>
                    </div>
                </template>
            </div>
                <div x-show="showCustomerSearch && customerSearch.length >= 2 && searchedCustomers.length === 0" class="absolute top-full left-0 right-0 mt-0.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-lg shadow-lg p-3" style="z-index: 100;">
                    <p class="text-xs text-gray-400 dark:text-gray-500 mb-2">No customers found</p>
                    <template x-if="!showQuickCustomerForm">
                        <div class="flex gap-2">
                            <button @click="showQuickCustomerForm = true; quickCustomerPhone = customerSearch" class="flex-1 px-3 py-1.5 text-xs font-medium text-white bg-blue-600 hover:bg-blue-700 rounded transition-colors">+ Quick Add</button>
                            <button @click="showCustomerSearch = false; searchedCustomers = []" class="flex-1 px-3 py-1.5 text-xs text-gray-600 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded transition-colors">Close</button>
                        </div>
                    </template>
                    <template x-if="showQuickCustomerForm">
                        <div class="space-y-2">
                            <div>
                                <label class="text-[10px] text-gray-500 dark:text-gray-400 block mb-0.5">Phone Number *</label>
                                <input type="text" x-model="quickCustomerPhone" placeholder="01XXXXXXXXX" @keydown.enter="quickCreateCustomer()" @keydown.escape="showQuickCustomerForm = false"
                                    class="w-full h-7 text-xs rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-white/10 text-gray-800 dark:text-white/90 px-2 focus:outline-none focus:ring-1 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="text-[10px] text-gray-500 dark:text-gray-400 block mb-0.5">Name (optional)</label>
                                <input type="text" x-model="quickCustomerName" placeholder="Customer name" @keydown.enter="quickCreateCustomer()" @keydown.escape="showQuickCustomerForm = false"
                                    class="w-full h-7 text-xs rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-white/10 text-gray-800 dark:text-white/90 px-2 focus:outline-none focus:ring-1 focus:ring-blue-500">
                            </div>
                            <div class="flex gap-2">
                                <button @click="quickCreateCustomer()" :disabled="quickCustomerSaving" class="flex-1 px-3 py-1.5 text-xs font-medium text-white bg-green-600 hover:bg-green-700 disabled:opacity-50 rounded transition-colors">
                                    <span x-show="!quickCustomerSaving">Save & Continue</span>
                                    <span x-show="quickCustomerSaving">Saving...</span>
                                </button>
                                <button @click="showQuickCustomerForm = false; quickCustomerPhone = ''; quickCustomerName = ''" class="flex-1 px-3 py-1.5 text-xs text-gray-600 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded transition-colors">Cancel</button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        
        <div class="flex-1 overflow-y-auto p-3 space-y-1.5">
            <template x-if="items.length === 0">
                <div class="flex items-center justify-center h-full text-gray-300 dark:text-white/25 text-sm font-medium">Cart is empty</div>
            </template>
            <template x-for="(item, idx) in items" :key="idx">
                <div class="flex flex-col gap-1 py-2 border-b border-gray-50 dark:border-white/10">
                    <div class="flex items-center gap-2.5">
                        <span
                            class="bg-blue-500 text-white w-5 h-5 rounded-full text-[10px] flex items-center justify-center font-bold shrink-0"
                            x-text="item.qty"
                        ></span>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-800 dark:text-white/90 truncate" x-text="item.name"></p>
                            <p class="text-[11px] text-gray-400 dark:text-white/50 font-mono" x-text="formatMoney(item.price) + ' × ' + item.qty"></p>
                        </div>
                        <p class="text-sm font-mono font-semibold text-gray-900 dark:text-white shrink-0 tabular-nums" x-text="formatMoney(item.price * item.qty)"></p>
                        <button @click="removeItem(idx)" class="text-gray-300 dark:text-white/30 hover:text-red-400 dark:hover:text-red-400 text-base leading-none shrink-0 transition-colors p-0.5" title="Remove">&times;</button>
                    </div>
                    <div class="flex items-center gap-1 ml-7">
                        <button @click="updateQty(idx, item.qty - 1)"
                            :disabled="item.qty <= 1"
                            class="w-6 h-6 rounded bg-gray-100 dark:bg-white/10 text-gray-500 dark:text-white/60 hover:bg-gray-200 dark:hover:bg-white/15 disabled:opacity-30 disabled:cursor-not-allowed text-sm font-bold flex items-center justify-center transition-colors">-</button>
                        <input
                            type="number"
                            x-model.number="item.qty"
                            @input.debounce.300ms="updateQty(idx, item.qty)"
                            min="1"
                            class="w-12 h-6 text-center text-xs rounded bg-gray-50 dark:bg-white/10 border border-gray-200 dark:border-white/15 text-gray-800 dark:text-white/90 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none focus:outline-none focus:ring-1 focus:ring-blue-500/50 focus:border-blue-500"
                        >
                        <button @click="updateQty(idx, item.qty + 1)"
                            class="w-6 h-6 rounded bg-gray-100 dark:bg-white/10 text-gray-500 dark:text-white/60 hover:bg-gray-200 dark:hover:bg-white/15 text-sm font-bold flex items-center justify-center transition-colors">+</button>
                    </div>
                </div>
            </template>
        </div>

        
        <div class="border-t border-gray-200 dark:border-white/10 px-4 py-3 space-y-1.5 text-sm shrink-0">
            
            <div class="flex items-center gap-1.5 pb-2 border-b border-gray-100 dark:border-white/10">
                <span class="text-xs text-gray-500 dark:text-white/60 whitespace-nowrap">Discount</span>
                <select x-model="discountType" class="h-7 text-xs rounded border border-gray-200 dark:border-white/15 bg-gray-50 dark:bg-white/10 text-gray-700 dark:text-white/80 focus:outline-none focus:ring-1 focus:ring-blue-500/50 px-1">
                    <option value="percent">%</option>
                    <option value="flat" x-text="$store.currency.symbol">$</option>
                </select>
                <input
                    type="number"
                    x-model.number="discountValue"
                    min="0"
                    step="0.01"
                    placeholder="0.00"
                    class="flex-1 h-7 text-xs rounded border border-gray-200 dark:border-white/15 bg-gray-50 dark:bg-white/10 text-gray-800 dark:text-white/90 px-2 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none focus:outline-none focus:ring-1 focus:ring-blue-500/50 focus:border-blue-500"
                >
            </div>
            <div class="flex justify-between text-gray-500 dark:text-white/60">
                <span>Subtotal</span>
                <span class="font-mono tabular-nums" x-text="formatMoney(subtotal)"></span>
            </div>
            <div class="flex justify-between text-gray-500 dark:text-white/60">
                <span>Discount</span>
                <span class="font-mono text-red-400 tabular-nums" x-text="'-' + formatMoney(discount)"></span>
            </div>
            <div class="flex justify-between text-green-500 dark:text-green-400" x-show="promoDiscount > 0">
                <span>Promo</span>
                <span class="font-mono tabular-nums" x-text="'-' + formatMoney(promoDiscount)"></span>
            </div>
            <div class="flex justify-between text-gray-500 dark:text-white/60">
                <span>Tax</span>
                <span class="font-mono tabular-nums" x-text="formatMoney(tax)"></span>
            </div>
            <div class="flex justify-between text-base font-bold border-t border-gray-200 dark:border-white/10 pt-2.5 mt-1 text-gray-900 dark:text-white">
                <span>Total</span>
                <span class="font-mono tabular-nums text-blue-600 dark:text-blue-400" x-text="formatMoney(grandTotal)"></span>
            </div>
        </div>

        
        <div class="border-t border-gray-200 dark:border-white/10 p-3 space-y-2 shrink-0">
            <div class="flex gap-2">
                <button
                    @click="openPayment('cash')"
                    :disabled="items.length === 0 || !hasBranch"
                    class="flex-1 bg-emerald-600 hover:bg-emerald-500 disabled:opacity-30 disabled:cursor-not-allowed text-white font-bold py-2.5 rounded-lg transition text-xs uppercase tracking-wide"
                >Cash</button>
                <button
                    @click="openPayment('card')"
                    :disabled="items.length === 0 || !hasBranch"
                    class="flex-1 bg-blue-600 hover:bg-blue-500 disabled:opacity-30 disabled:cursor-not-allowed text-white font-bold py-2.5 rounded-lg transition text-xs uppercase tracking-wide"
                >Card</button>
                <button
                    @click="openPayment('check')"
                    :disabled="items.length === 0 || !hasBranch"
                    class="flex-1 bg-gray-600 hover:bg-gray-500 disabled:opacity-30 disabled:cursor-not-allowed text-white font-bold py-2.5 rounded-lg transition text-xs uppercase tracking-wide"
                >Check</button>
            </div>
            <button
                @click="newSale()"
                :disabled="items.length === 0"
                class="w-full bg-red-500/10 dark:bg-red-500/15 hover:bg-red-500/20 dark:hover:bg-red-500/25 disabled:opacity-30 disabled:cursor-not-allowed text-red-500 dark:text-red-400 font-semibold py-2 rounded-lg transition text-sm"
            >New Sale</button>
        </div>
    </div>

    
    <?php if (isset($component)) { $__componentOriginal25f2e6165c82aa8acb5302308b63b7da = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal25f2e6165c82aa8acb5302308b63b7da = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.pos.payment-modal','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('pos.payment-modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal25f2e6165c82aa8acb5302308b63b7da)): ?>
<?php $attributes = $__attributesOriginal25f2e6165c82aa8acb5302308b63b7da; ?>
<?php unset($__attributesOriginal25f2e6165c82aa8acb5302308b63b7da); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal25f2e6165c82aa8acb5302308b63b7da)): ?>
<?php $component = $__componentOriginal25f2e6165c82aa8acb5302308b63b7da; ?>
<?php unset($__componentOriginal25f2e6165c82aa8acb5302308b63b7da); ?>
<?php endif; ?>

    
    <div x-show="showReceipt" class="fixed inset-0 z-[60] flex items-start justify-center pt-10" x-cloak @click.self="showReceipt = false; receiptData = null">
        <div class="fixed inset-0 bg-black/60"></div>
        <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-2xl w-full max-w-[420px] max-h-[85vh] overflow-y-auto border border-gray-200 dark:border-gray-700 mx-4">
            <div class="sticky top-0 bg-white dark:bg-gray-800 px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between z-10 rounded-t-xl">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Receipt</h3>
                <button @click="showReceipt = false; receiptData = null" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 text-2xl leading-none">&times;</button>
            </div>
            <div class="p-6">
                <iframe :srcdoc="receiptData?.receipt_html" class="w-full border-0 rounded" style="height:55vh; min-height:320px; background:#fff;" x-ref="receiptFrame"></iframe>
                <div class="flex gap-3 mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <button @click="$refs.receiptFrame?.contentWindow?.print()" class="flex-1 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">Print</button>
                    <button @click="downloadReceipt()" class="flex-1 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg transition-colors">Download PDF</button>
                    <button @click="showReceipt = false; receiptData = null" class="flex-1 px-4 py-2.5 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">Close</button>
                </div>
            </div>
        </div>
    </div>

    
    <div
        x-cloak
        x-show="toast.show"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-4"
        x-text="toast.message"
        class="fixed px-6 py-3 rounded-lg text-white text-sm font-semibold shadow-xl z-50 pointer-events-none"
        :class="[toast.type === 'error' ? 'bg-red-500' : 'bg-emerald-500', toastPositionClass()]"
    ></div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\POS Software\D Star Company\resources\views/pos/index.blade.php ENDPATH**/ ?>