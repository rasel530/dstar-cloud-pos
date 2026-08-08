<?php $__env->startSection('title', 'Orders'); ?>

<?php $__env->startSection('content'); ?>
<div x-data="ordersList" x-init="fetchOrders()" class="flex flex-col h-full">
    
    <div class="px-6 py-4 border-b border-gray-200 dark:border-white/10 flex items-center justify-between shrink-0">
        <h2 class="text-xl font-bold text-gray-900 dark:text-white">Orders</h2>
        <a href="<?php echo e(route('pos.index')); ?>" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
            New Sale
        </a>
    </div>

    
    <div class="px-6 py-3 flex items-center gap-3 border-b border-gray-100 dark:border-white/5 shrink-0">
        <div class="flex rounded-lg overflow-hidden border border-gray-200 dark:border-white/10">
            <button @click="statusFilter = 'all'; fetchOrders()"
                :class="statusFilter === 'all' ? 'bg-blue-500 text-white' : 'bg-white dark:bg-[#1a1f3d] text-gray-500 dark:text-white/60 hover:text-gray-900 dark:hover:text-white'"
                class="px-4 py-1.5 text-sm transition">All</button>
            <button @click="statusFilter = 'open'; fetchOrders()"
                :class="statusFilter === 'open' ? 'bg-blue-500 text-white' : 'bg-white dark:bg-[#1a1f3d] text-gray-500 dark:text-white/60 hover:text-gray-900 dark:hover:text-white'"
                class="px-4 py-1.5 text-sm transition border-x border-gray-200 dark:border-white/10">Open</button>
            <button @click="statusFilter = 'closed'; fetchOrders()"
                :class="statusFilter === 'closed' ? 'bg-blue-500 text-white' : 'bg-white dark:bg-[#1a1f3d] text-gray-500 dark:text-white/60 hover:text-gray-900 dark:hover:text-white'"
                class="px-4 py-1.5 text-sm transition">Closed</button>
        </div>
        <div class="relative flex-1 max-w-xs">
            <input
                type="text"
                x-model="searchQuery"
                @input.debounce.300ms="fetchOrders()"
                placeholder="Search by order # or customer..."
                class="w-full bg-gray-50 dark:bg-[#0f1535] border border-gray-200 dark:border-white/20 rounded-lg px-4 py-1.5 text-sm text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-white/40 focus:outline-none focus:border-blue-500 transition"
            >
        </div>
        <span class="text-gray-400 dark:text-white/40 text-sm ml-auto" x-text="totalOrders + ' orders'"></span>
    </div>

    
    <div class="flex-1 overflow-auto">
        <template x-if="loading">
            <div class="flex items-center justify-center h-40 text-gray-300 dark:text-white/30">Loading orders...</div>
        </template>
        <template x-if="!loading && orders.length === 0">
            <div class="flex items-center justify-center h-40 text-gray-300 dark:text-white/30">No orders found</div>
        </template>
        <template x-if="!loading && orders.length > 0">
            <div>
            <div class="overflow-x-auto"><table class="hidden md:table w-full text-sm min-w-[700px]">
                <thead class="sticky top-0 bg-gray-50 dark:bg-[#0f1535] border-b border-gray-200 dark:border-white/10">
                    <tr class="text-gray-500 dark:text-white/50 text-xs uppercase tracking-wider">
                        <th class="text-left px-6 py-3">Order #</th>
                        <th class="text-left px-6 py-3">Table / Name</th>
                        <th class="text-left px-6 py-3">Customer</th>
                        <th class="text-center px-6 py-3">Items</th>
                        <th class="text-right px-6 py-3">Total</th>
                        <th class="text-center px-6 py-3">Status</th>
                        <th class="text-center px-6 py-3 sticky right-0 bg-gray-50 dark:bg-[#0f1535] z-10">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="order in orders" :key="order.id">
                        <tr class="border-b border-gray-100 dark:border-white/5 hover:bg-gray-50 dark:hover:bg-white/5 transition group">
                            <td class="px-6 py-3 font-mono text-blue-400" x-text="'#' + (order.number || order.id?.substring(0, 8) || '--')"></td>
                            <td class="px-6 py-3" x-text="order.table_number || order.number || '--'"></td>
                            <td class="px-6 py-3 text-gray-500 dark:text-white/70" x-text="order.customer?.name || 'Walk-in'"></td>
                            <td class="px-6 py-3 text-center" x-text="order.pos_order_items_count || order.pos_order_items?.length || 0"></td>
                            <td class="px-6 py-3 text-right font-mono font-bold" x-text="order.total != null && order.total > 0 ? formatMoney(order.total) : '--'"></td>
                            <td class="px-6 py-3 text-center">
                                <span
                                    x-text="order.status"
                                    :class="{
                                        'bg-emerald-500/20 text-emerald-400 px-2 py-0.5 rounded-full text-xs font-medium capitalize': order.status === 'closed',
                                        'bg-amber-500/20 text-amber-400 px-2 py-0.5 rounded-full text-xs font-medium capitalize': order.status === 'open' || order.status === 'pending',
                                        'bg-red-500/20 text-red-400 px-2 py-0.5 rounded-full text-xs font-medium capitalize': order.status === 'cancelled',
                                    }"
                                ></span>
                            </td>
                            <td class="px-6 py-3 text-center sticky right-0 bg-white dark:bg-[#1a1f3d] group-hover:bg-gray-50 dark:group-hover:bg-white/5 z-10 shadow-[-4px_0_8px_-4px_rgba(0,0,0,0.1)] dark:shadow-[-4px_0_8px_-4px_rgba(0,0,0,0.4)]">
                                <div class="flex items-center justify-center gap-2">
                                    <button @click="viewOrder(order)" class="p-2 sm:p-1.5 rounded-md text-blue-500 dark:text-blue-400 hover:text-blue-600 dark:hover:text-blue-300 hover:bg-blue-50 dark:hover:bg-blue-900/30 transition-colors" title="View Order">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    </button>
                                    <button x-show="order.status === 'open'" @click="closeOrder(order)" class="p-2 sm:p-1.5 rounded-md text-emerald-500 dark:text-emerald-400 hover:text-emerald-600 dark:hover:text-emerald-300 hover:bg-emerald-50 dark:hover:bg-emerald-900/30 transition-colors" title="Complete Order">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </button>
                                    <button x-show="order.status === 'closed'" @click="refundOrder(order)" class="p-2 sm:p-1.5 rounded-md text-red-500 dark:text-red-400 hover:text-red-600 dark:hover:text-red-300 hover:bg-red-50 dark:hover:bg-red-900/30 transition-colors" title="Refund Order">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 14l-4-4m0 0l4-4m-4 4h11a4 4 0 010 8h-1"/></svg>
                                    </button>
                                    <button x-show="order.status === 'closed'" @click="downloadOrderReceipt(order)" class="p-2 sm:p-1.5 rounded-md text-indigo-500 dark:text-indigo-400 hover:text-indigo-600 dark:hover:text-indigo-300 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 transition-colors" title="Download Receipt">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table></div>
            
            <div class="md:hidden space-y-3">
                <template x-for="order in orders" :key="order.id">
                    <div class="bg-white dark:bg-[#1a1f3d] rounded-lg border border-gray-200 dark:border-white/10 p-4 shadow-sm">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-2">
                                <span class="font-mono text-blue-400 text-sm" x-text="'#' + (order.number || order.id?.substring(0, 8) || '--')"></span>
                                <span
                                    x-text="order.status"
                                    :class="{
                                        'bg-emerald-500/20 text-emerald-400 px-2 py-0.5 rounded-full text-xs font-medium capitalize': order.status === 'closed',
                                        'bg-amber-500/20 text-amber-400 px-2 py-0.5 rounded-full text-xs font-medium capitalize': order.status === 'open' || order.status === 'pending',
                                        'bg-red-500/20 text-red-400 px-2 py-0.5 rounded-full text-xs font-medium capitalize': order.status === 'cancelled',
                                    }"
                                ></span>
                            </div>
                            <span class="text-sm font-mono font-bold text-gray-900 dark:text-white" x-text="order.total != null && order.total > 0 ? formatMoney(order.total) : '--'"></span>
                        </div>
                        <div class="grid grid-cols-2 gap-2 text-xs text-gray-500 dark:text-white/50 mb-3">
                            <div><span class="text-gray-400">Table:</span> <span x-text="order.table_number || '--'"></span></div>
                            <div><span class="text-gray-400">Items:</span> <span x-text="order.pos_order_items_count || order.pos_order_items?.length || 0"></span></div>
                            <div class="col-span-2"><span class="text-gray-400">Customer:</span> <span x-text="order.customer?.name || 'Walk-in'"></span></div>
                        </div>
                        <div class="flex items-center gap-1 border-t border-gray-100 dark:border-white/10 pt-3">
                            <button @click="viewOrder(order)" class="flex items-center gap-1 px-3 py-1.5 rounded text-xs font-medium text-blue-500 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/30 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                View
                            </button>
                            <button x-show="order.status === 'open'" @click="closeOrder(order)" class="flex items-center gap-1 px-3 py-1.5 rounded text-xs font-medium text-emerald-500 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-900/30 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Complete
                            </button>
                            <button x-show="order.status === 'closed'" @click="refundOrder(order)" class="flex items-center gap-1 px-3 py-1.5 rounded text-xs font-medium text-red-500 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 14l-4-4m0 0l4-4m-4 4h11a4 4 0 010 8h-1"/></svg>
                                Refund
                            </button>
                            <button x-show="order.status === 'closed'" @click="downloadOrderReceipt(order)" class="flex items-center gap-1 px-3 py-1.5 rounded text-xs font-medium text-indigo-500 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                                Receipt
                            </button>
                        </div>
                    </div>
            </div>
                </template>
            </div>
        </template>
    </div>

    
    <div x-show="totalPages > 1" class="px-6 py-3 border-t border-gray-200 dark:border-white/10 flex items-center justify-between shrink-0">
        <button @click="changePage(currentPage - 1)" :disabled="currentPage <= 1"
            class="px-3 py-1 rounded bg-white dark:bg-[#1a1f3d] text-gray-500 dark:text-white/60 hover:text-gray-900 dark:hover:text-white disabled:opacity-30 disabled:cursor-not-allowed text-sm transition">
            Previous
        </button>
        <span class="text-gray-400 dark:text-white/40 text-sm" x-text="'Page ' + currentPage + ' of ' + totalPages"></span>
        <button @click="changePage(currentPage + 1)" :disabled="currentPage >= totalPages"
            class="px-3 py-1 rounded bg-white dark:bg-[#1a1f3d] text-gray-500 dark:text-white/60 hover:text-gray-900 dark:hover:text-white disabled:opacity-30 disabled:cursor-not-allowed text-sm transition">
            Next
        </button>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function ordersList() {
    return {
        orders: [],
        statusFilter: 'all',
        searchQuery: '',
        currentPage: 1,
        totalPages: 1,
        totalOrders: 0,
        loading: false,

        async fetchOrders() {
            this.loading = true;
            try {
                let url = `/api/orders?page=${this.currentPage}`;
                if (this.statusFilter !== 'all') url += `&status=${this.statusFilter}`;
                if (this.searchQuery) url += `&q=${encodeURIComponent(this.searchQuery)}`;

                const data = await window.POS.api(url);
                this.orders = data.data?.data || data.data || [];
                this.currentPage = data.meta?.current_page || data.data?.current_page || 1;
                this.totalPages = data.meta?.last_page || data.data?.last_page || 1;
                this.totalOrders = data.meta?.total || data.data?.total || this.orders.length;
            } catch (e) {
                this.orders = [];
            } finally {
                this.loading = false;
            }
        },

        async closeOrder(order) {
            if (!confirm(`Close order #${order.order_number}?`)) return;
            try {
                await window.POS.api(`/api/orders/${order.id}/close`, { method: 'POST' });
                await this.fetchOrders();
            } catch (e) {
                alert('Failed to close order');
            }
        },

        viewOrder(order) {
            window.location.href = `/pos/orders/${order.id}`;
        },

        changePage(page) {
            if (page < 1 || page > this.totalPages) return;
            this.currentPage = page;
            this.fetchOrders();
        },

        formatMoney(amount) {
            return window.POS.formatCurrency(amount);
        },
    };
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\POS Software\D Star Company\resources\views/pos/orders.blade.php ENDPATH**/ ?>