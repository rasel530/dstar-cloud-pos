<div x-show="showPayment" x-cloak class="fixed inset-0 z-50 flex items-end sm:items-center justify-center" @keydown.escape="showPayment = false">
    <div class="absolute inset-0 bg-black/60" @click="showPayment = false"></div>
    <div class="relative bg-white dark:bg-[#1a1f3d] w-full sm:w-[400px] sm:rounded-2xl rounded-t-2xl shadow-2xl animate-slide-up sm:animate-none"
        style="max-height: calc(100vh - 40px); overflow-y: auto;">

        <div class="sm:hidden flex justify-center pt-3 pb-1 sticky top-0 bg-white dark:bg-[#1a1f3d] z-10">
            <div class="w-10 h-1 bg-gray-300 dark:bg-gray-600 rounded-full"></div>
        </div>

        <div class="flex items-center justify-between px-5 pt-1 pb-3">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Complete Payment</h3>
            <button @click="showPayment = false" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
        </div>

        <div class="px-5 space-y-3">

            <div class="text-3xl font-bold text-gray-900 dark:text-white text-center py-2">
                <span x-text="$store.currency.symbol"></span><span x-text="grandTotal.toFixed(2)"></span>
            </div>

            <template x-if="quickPaymentTypes.length <= 3">
                <div class="flex gap-2">
                    <template x-for="pt in quickPaymentTypes" :key="pt.id || pt.code">
                        <button @click="paymentType = pt; tenderAmount = grandTotal"
                            class="flex-1 py-3 rounded-xl text-white font-bold text-xs uppercase tracking-wide transition active:scale-[0.97]"
                            :class="(paymentType?.id || paymentType?.code) === (pt.id || pt.code) ? '' : 'opacity-50'"
                            :style="{ backgroundColor: pt.color === 'emerald' ? '#059669' : pt.color === 'blue' ? '#2563eb' : pt.color === 'violet' ? '#7c3aed' : pt.color === 'amber' ? '#d97706' : pt.color === 'rose' ? '#e11d48' : pt.color === 'cyan' ? '#0891b2' : pt.color === 'gray' ? '#4b5563' : pt.color === 'green' ? '#16a34a' : pt.color === 'red' ? '#dc2626' : pt.color === 'indigo' ? '#4f46e5' : pt.color === 'teal' ? '#0d9488' : pt.color === 'orange' ? '#ea580c' : pt.color === 'pink' ? '#db2777' : '#059669' }"
                            x-text="pt.name?.toUpperCase() || pt.code?.toUpperCase()">
                        </button>
                    </template>
                </div>
            </template>

            <template x-if="quickPaymentTypes.length > 3">
                <div class="grid gap-2" :class="quickPaymentTypes.length > 6 ? 'grid-cols-3' : 'grid-cols-2'">
                    <template x-for="(pt, idx) in quickPaymentTypes" :key="pt.id || pt.code">
                        <button @click="paymentType = pt; tenderAmount = grandTotal"
                            class="py-2.5 rounded-xl text-white font-bold text-[11px] uppercase tracking-wide transition active:scale-[0.97] flex items-center justify-center gap-1.5"
                            :class="(paymentType?.id || paymentType?.code) === (pt.id || pt.code) ? '' : 'opacity-50'"
                            :style="{ backgroundColor: pt.color === 'emerald' ? '#059669' : pt.color === 'blue' ? '#2563eb' : pt.color === 'violet' ? '#7c3aed' : pt.color === 'amber' ? '#d97706' : pt.color === 'rose' ? '#e11d48' : pt.color === 'cyan' ? '#0891b2' : pt.color === 'gray' ? '#4b5563' : pt.color === 'green' ? '#16a34a' : pt.color === 'red' ? '#dc2626' : pt.color === 'indigo' ? '#4f46e5' : pt.color === 'teal' ? '#0d9488' : pt.color === 'orange' ? '#ea580c' : pt.color === 'pink' ? '#db2777' : '#059669' }">
                            <span class="text-[9px] font-mono bg-white/20 rounded px-1 py-0.5" x-text="'F'+(idx+1)"></span>
                            <span x-text="pt.name?.toUpperCase() || pt.code?.toUpperCase()"></span>
                        </button>
                    </template>
                </div>
            </template>

            <div x-show="paymentType?.code === 'cash' || paymentType?.name?.toLowerCase() === 'cash' || (typeof paymentType === 'string' && paymentType.toLowerCase() === 'cash')">
                <input type="number" x-model="tenderAmount" step="0.01" inputmode="decimal"
                    class="w-full bg-gray-50 dark:bg-[#0f1535] border-2 border-gray-200 dark:border-white/20 rounded-xl px-4 py-3 text-gray-900 dark:text-white text-xl text-center font-bold focus:border-emerald-500 outline-none"
                    placeholder="Enter amount" @keydown.enter="processPayment()" autofocus>
                <div x-show="parseFloat(tenderAmount || 0) >= grandTotal" class="text-center mt-1.5 text-emerald-600 dark:text-emerald-400 text-sm font-medium">
                    Change: <span class="font-bold" x-text="$store.currency.symbol + (parseFloat(tenderAmount || 0) - grandTotal).toFixed(2)"></span>
                </div>
            </div>

            <div x-show="paymentType && !(paymentType?.code === 'cash' || paymentType?.name?.toLowerCase() === 'cash' || (typeof paymentType === 'string' && paymentType.toLowerCase() === 'cash'))"
                class="bg-gray-50 dark:bg-[#0f1535] rounded-xl px-4 py-3 text-center border border-gray-200 dark:border-white/10">
                <p class="text-sm text-gray-500 dark:text-white/60"><span class="font-semibold text-gray-900 dark:text-white" x-text="paymentType?.name || paymentType?.code"></span> — <span class="font-bold" x-text="$store.currency.symbol + grandTotal.toFixed(2)"></span></p>
            </div>
        </div>

        <div class="px-5 pt-3 pb-5 space-y-2">
            <button @click="processPayment()" :disabled="!paymentType || processingPayment"
                class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 disabled:opacity-40 text-white font-bold rounded-xl transition text-sm">
                Complete Payment
            </button>
            <button @click="showPayment = false"
                class="w-full py-2.5 text-gray-400 hover:text-gray-600 dark:text-white/50 dark:hover:text-white text-sm font-medium rounded-lg transition">
                Cancel
            </button>
        </div>
    </div>
</div>
