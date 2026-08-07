<div x-show="showPayment" x-cloak class="fixed inset-0 z-50 flex items-center justify-center">
    <div class="absolute inset-0 bg-black/60" @click="showPayment = false"></div>
    <div class="relative bg-white dark:bg-[#1a1f3d] rounded-xl w-full max-w-md mx-4 p-6 shadow-2xl border border-gray-200 dark:border-white/10">
        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Complete Payment</h3>

        <div class="text-3xl font-bold text-gray-900 dark:text-white text-center mb-6">
            <span x-text="$store.currency.symbol"></span><span x-text="grandTotal.toFixed(2)"></span>
        </div>

        <div class="flex gap-3 mb-4">
            <button @click="paymentType = 'cash'; processPayment()"
                class="flex-1 py-3 rounded-xl bg-green-600 hover:bg-green-700 text-white font-bold text-sm transition">
                CASH
            </button>
            <button @click="paymentType = 'card'; processPayment()"
                class="flex-1 py-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm transition">
                CARD
            </button>
            <button @click="paymentType = 'check'; processPayment()"
                class="flex-1 py-3 rounded-xl bg-gray-600 hover:bg-gray-700 text-white font-bold text-sm transition">
                CHECK
            </button>
        </div>

        <div x-show="paymentType === 'cash'" class="mb-3">
            <label class="block text-sm text-gray-500 dark:text-white/60 mb-1">Amount Tendered</label>
            <input type="number" x-model="tenderAmount" step="0.01"
                class="w-full bg-gray-50 dark:bg-[#0f1535] border border-gray-300 dark:border-white/20 rounded-lg px-3 py-2 text-gray-900 dark:text-white text-lg text-center"
                @keydown.enter="processPayment()" autofocus>
            <div x-show="parseFloat(tenderAmount || 0) >= grandTotal" class="text-center mt-2 text-green-400 text-sm">
                Change: <span x-text="$store.currency.symbol"></span><span x-text="(parseFloat(tenderAmount || 0) - grandTotal).toFixed(2)"></span>
            </div>
        </div>

        <button @click="showPayment = false"
            class="w-full py-2 text-gray-400 dark:text-white/50 hover:text-gray-900 dark:hover:text-white text-sm transition mt-2">
            Cancel
        </button>
    </div>
</div>
