{{-- ===== Open Register Modal ===== --}}
<div x-show="showOpenRegister" x-cloak class="fixed inset-0 z-50 flex items-end sm:items-center justify-center" @keydown.escape="showOpenRegister = false">
    <div class="absolute inset-0 bg-black/60" @click="showOpenRegister = false"></div>
    <div class="relative bg-white dark:bg-[#1a1f3d] w-full sm:max-w-sm sm:rounded-2xl rounded-t-2xl shadow-2xl max-h-[85vh] overflow-y-auto animate-slide-up sm:animate-none pb-[env(safe-area-inset-bottom,0px)]">
        <div class="sm:hidden flex justify-center pt-3 pb-1"><div class="w-10 h-1 bg-gray-300 dark:bg-gray-600 rounded-full"></div></div>
        <div class="flex items-center justify-between px-5 pt-1 pb-3">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Open Register</h3>
            <button @click="showOpenRegister = false" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
        </div>
        <div class="px-5 space-y-4 pb-2">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Shift</label>
                <select x-model="openRegisterForm.shift_id" class="w-full px-3 py-3 bg-gray-50 dark:bg-[#0f1535] border-2 border-gray-200 dark:border-white/20 rounded-xl text-sm text-gray-900 dark:text-white focus:border-emerald-500 outline-none transition">
                    <option value="">Select Shift</option>
                    <template x-for="s in shifts" :key="s.id">
                        <option :value="s.id" x-text="s.name + (s.start_time ? ' (' + s.start_time + (s.end_time ? ' - ' + s.end_time : '') + ')' : '')"></option>
                    </template>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Opening Cash</label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm" x-text="$store.currency.symbol || '$'"></span>
                    <input type="number" x-model="openRegisterForm.opening_cash" step="0.01" min="0" inputmode="decimal" placeholder="0.00" class="w-full pl-8 pr-3 py-3 bg-gray-50 dark:bg-[#0f1535] border-2 border-gray-200 dark:border-white/20 rounded-xl text-gray-900 dark:text-white text-xl font-bold focus:border-emerald-500 outline-none transition">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Note (optional)</label>
                <input type="text" x-model="openRegisterForm.note" placeholder="e.g. Morning shift" class="w-full px-3 py-2.5 bg-gray-50 dark:bg-[#0f1535] border border-gray-200 dark:border-white/20 rounded-xl text-sm text-gray-900 dark:text-white">
            </div>
        </div>
        <div class="px-5 pt-3 pb-5">
            <button @click="openRegister()" :disabled="registerSaving" class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-40 text-white font-bold rounded-xl transition text-sm">
                <span x-show="!registerSaving">Open Register</span>
                <span x-show="registerSaving">Opening...</span>
            </button>
        </div>
    </div>
</div>

{{-- ===== Close Register Modal ===== --}}
<div x-show="showCloseRegister" x-cloak class="fixed inset-0 z-50 flex items-end sm:items-center justify-center" @keydown.escape="showCloseRegister = false">
    <div class="absolute inset-0 bg-black/60" @click="showCloseRegister = false"></div>
    <div class="relative bg-white dark:bg-[#1a1f3d] w-full sm:max-w-md sm:rounded-2xl rounded-t-2xl shadow-2xl max-h-[90vh] overflow-y-auto animate-slide-up sm:animate-none pb-[env(safe-area-inset-bottom,0px)]">
        <div class="sm:hidden flex justify-center pt-3 pb-1"><div class="w-10 h-1 bg-gray-300 dark:bg-gray-600 rounded-full"></div></div>
        <div class="flex items-center justify-between px-5 pt-1 pb-3">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Close Register</h3>
            <button @click="showCloseRegister = false" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
        </div>
        <div class="px-5 space-y-3">
            <div class="bg-gray-50 dark:bg-[#0f1535] rounded-xl divide-y divide-gray-200 dark:divide-white/10 border border-gray-200 dark:border-white/10">
                <div class="flex justify-between px-4 py-2.5 text-sm">
                    <span class="text-gray-500 dark:text-white/60">Opening Cash</span>
                    <span class="font-semibold text-gray-900 dark:text-white" x-text="formatMoney(register.summary?.opening_cash || 0)"></span>
                </div>
                <div class="flex justify-between px-4 py-2.5 text-sm">
                    <span class="text-gray-500 dark:text-white/60">Cash Sales</span>
                    <span class="font-semibold text-emerald-600 dark:text-emerald-400" x-text="'+ ' + formatMoney(register.summary?.cash_sales || 0)"></span>
                </div>
                <div class="flex justify-between px-4 py-2.5 text-sm">
                    <span class="text-gray-500 dark:text-white/60">Cash In</span>
                    <span class="font-semibold text-emerald-600 dark:text-emerald-400" x-text="'+ ' + formatMoney(register.summary?.cash_in || 0)"></span>
                </div>
                <div class="flex justify-between px-4 py-2.5 text-sm">
                    <span class="text-gray-500 dark:text-white/60">Cash Refund</span>
                    <span class="font-semibold text-red-600 dark:text-red-400" x-text="'- ' + formatMoney(register.summary?.cash_refund || 0)"></span>
                </div>
                <div class="flex justify-between px-4 py-2.5 text-sm">
                    <span class="text-gray-500 dark:text-white/60">Cash Out</span>
                    <span class="font-semibold text-red-600 dark:text-red-400" x-text="'- ' + formatMoney(register.summary?.cash_out || 0)"></span>
                </div>
                <div class="flex justify-between px-4 py-3 text-sm bg-gray-100 dark:bg-white/5 font-bold">
                    <span class="text-gray-800 dark:text-white">Expected Cash</span>
                    <span class="text-gray-900 dark:text-white" x-text="formatMoney(register.summary?.expected_cash || 0)"></span>
                </div>
            </div>

            <div class="flex gap-2">
                <button @click="openCashInOutModal('in')" class="flex-1 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold transition">+ Cash In</button>
                <button @click="openCashInOutModal('out')" class="flex-1 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-sm font-bold transition">− Cash Out</button>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Actual Cash Counted</label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm" x-text="$store.currency.symbol || '$'"></span>
                    <input type="number" x-model="closeRegisterForm.actual_cash" step="0.01" min="0" inputmode="decimal" placeholder="0.00" class="w-full pl-8 pr-3 py-3 bg-gray-50 dark:bg-[#0f1535] border-2 border-gray-200 dark:border-white/20 rounded-xl text-gray-900 dark:text-white text-xl font-bold focus:border-emerald-500 outline-none transition">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Note (optional)</label>
                <input type="text" x-model="closeRegisterForm.note" placeholder="Closing note" class="w-full px-3 py-2.5 bg-gray-50 dark:bg-[#0f1535] border border-gray-200 dark:border-white/20 rounded-xl text-sm text-gray-900 dark:text-white">
            </div>
        </div>
        <div class="px-5 pt-3 pb-5 space-y-2">
            <button @click="closeRegister()" :disabled="registerSaving" class="w-full py-3 bg-blue-600 hover:bg-blue-700 disabled:opacity-40 text-white font-bold rounded-xl transition text-sm">
                <span x-show="!registerSaving">Close Register</span>
                <span x-show="registerSaving">Closing...</span>
            </button>
            <button @click="showCloseRegister = false" class="w-full py-2.5 text-gray-400 hover:text-gray-600 text-sm font-medium rounded-lg transition">Cancel</button>
        </div>
    </div>
</div>

{{-- ===== Cash In / Out Modal ===== --}}
<div x-show="showCashInOut" x-cloak class="fixed inset-0 z-50 flex items-end sm:items-center justify-center" @keydown.escape="showCashInOut = false">
    <div class="absolute inset-0 bg-black/60" @click="showCashInOut = false"></div>
    <div class="relative bg-white dark:bg-[#1a1f3d] w-full sm:max-w-sm sm:rounded-2xl rounded-t-2xl shadow-2xl max-h-[85vh] overflow-y-auto animate-slide-up sm:animate-none pb-[env(safe-area-inset-bottom,0px)]">
        <div class="sm:hidden flex justify-center pt-3 pb-1"><div class="w-10 h-1 bg-gray-300 dark:bg-gray-600 rounded-full"></div></div>
        <div class="flex items-center justify-between px-5 pt-1 pb-3">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white" x-text="cashInOutForm.type === 'in' ? 'Cash In' : 'Cash Out'"></h3>
            <button @click="showCashInOut = false" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
        </div>
        <div class="px-5 space-y-4 pb-2">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Amount</label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm" x-text="$store.currency.symbol || '$'"></span>
                    <input type="number" x-model="cashInOutForm.amount" step="0.01" min="0" inputmode="decimal" placeholder="0.00" class="w-full pl-8 pr-3 py-3 bg-gray-50 dark:bg-[#0f1535] border-2 border-gray-200 dark:border-white/20 rounded-xl text-gray-900 dark:text-white text-xl font-bold focus:border-emerald-500 outline-none transition">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Reason</label>
                <select x-model="cashInOutForm.reason" class="w-full px-3 py-2.5 bg-gray-50 dark:bg-[#0f1535] border border-gray-200 dark:border-white/20 rounded-xl text-sm text-gray-900 dark:text-white">
                    <option value="">Select Reason</option>
                    <template x-for="r in (cashInOutForm.type === 'in' ? cashInReasons : cashOutReasons)" :key="r">
                        <option :value="r" x-text="r"></option>
                    </template>
                </select>
            </div>
        </div>
        <div class="px-5 pt-3 pb-5 space-y-2">
            <button @click="recordCashInOut()" :disabled="registerSaving" class="w-full py-3 text-white font-bold rounded-xl transition text-sm" :class="cashInOutForm.type === 'in' ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-rose-600 hover:bg-rose-700'">
                <span x-show="!registerSaving" x-text="cashInOutForm.type === 'in' ? 'Add Cash In' : 'Record Cash Out'"></span>
                <span x-show="registerSaving">Saving...</span>
            </button>
            <button @click="showCashInOut = false" class="w-full py-2.5 text-gray-400 hover:text-gray-600 text-sm font-medium rounded-lg transition">Cancel</button>
        </div>
    </div>
</div>

{{-- ===== Register History Modal ===== --}}
<div x-show="showRegisterHistory" x-cloak class="fixed inset-0 z-50 flex items-end sm:items-center justify-center" @keydown.escape="showRegisterHistory = false">
    <div class="absolute inset-0 bg-black/60" @click="showRegisterHistory = false"></div>
    <div class="relative bg-white dark:bg-[#1a1f3d] w-full sm:max-w-lg sm:rounded-2xl rounded-t-2xl shadow-2xl max-h-[85vh] flex flex-col animate-slide-up sm:animate-none pb-[env(safe-area-inset-bottom,0px)]">
        <div class="sm:hidden flex justify-center pt-3 pb-1 shrink-0"><div class="w-10 h-1 bg-gray-300 dark:bg-gray-600 rounded-full"></div></div>
        <div class="flex items-center justify-between px-5 pt-1 pb-3 border-b border-gray-200 dark:border-white/10 shrink-0">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Register History</h3>
            <button @click="showRegisterHistory = false" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
        </div>
        <div class="overflow-y-auto flex-1 px-5 py-3">
            <div x-show="registerHistoryLoading" class="text-center py-10 text-gray-400 text-sm">Loading...</div>
            <template x-if="!registerHistoryLoading">
                <div class="space-y-2">
                    <template x-if="!registerHistory.length">
                        <div class="text-center py-10 text-gray-400 dark:text-white/40 text-sm">No register history yet</div>
                    </template>
                    <template x-for="r in registerHistory" :key="r.id">
                        <div class="bg-gray-50 dark:bg-[#0f1535] rounded-xl border border-gray-200 dark:border-white/10 p-4">
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-semibold text-gray-900 dark:text-white" x-text="r.shift_name || 'Shift'"></span>
                                    <span x-show="r.user" class="text-xs text-gray-400 dark:text-white/40" x-text="'· ' + (r.user.first_name || '') + ' ' + (r.user.last_name || '')"></span>
                                </div>
                                <span class="text-xs px-2 py-0.5 rounded-full font-medium"
                                    :class="r.status === 'closed' ? 'bg-gray-200 text-gray-600 dark:bg-white/10 dark:text-white/60' : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400'"
                                    x-text="r.status === 'closed' ? 'Closed' : 'Open'"></span>
                            </div>
                            <div class="grid grid-cols-2 gap-x-3 gap-y-1 text-xs text-gray-500 dark:text-white/60">
                                <div class="flex justify-between"><span>Opened</span><span x-text="r.opened_at ? new Date(r.opened_at).toLocaleString() : '—'"></span></div>
                                <div class="flex justify-between"><span>Closed</span><span x-text="r.closed_at ? new Date(r.closed_at).toLocaleString() : '—'"></span></div>
                                <div x-show="r.status === 'open'" class="flex justify-between"><span>Last Activity</span><span x-text="r.last_activity_at ? new Date(r.last_activity_at).toLocaleString() : '—'"></span></div>
                                <div class="flex justify-between"><span>Opening Cash</span><span x-text="formatMoney(r.opening_cash)"></span></div>
                                <div class="flex justify-between"><span>Expected Cash</span><span x-text="r.expected_cash != null ? formatMoney(r.expected_cash) : '—'"></span></div>
                                <div class="flex justify-between"><span>Actual Cash</span><span x-text="r.actual_cash != null ? formatMoney(r.actual_cash) : '—'"></span></div>
                                <div class="flex justify-between font-semibold">
                                    <span>Difference</span>
                                    <span x-show="r.difference != null" :class="r.difference < 0 ? 'text-red-600 dark:text-red-400' : 'text-emerald-600 dark:text-emerald-400'" x-text="(r.difference > 0 ? '+' : '') + formatMoney(r.difference)"></span>
                                    <span x-show="r.difference == null">—</span>
                                </div>
                            </div>
                            <p x-show="r.note" class="mt-2 text-xs text-gray-400 italic" x-text="r.note"></p>
                            <div x-show="r.sessions && r.sessions.length" class="mt-3 pt-2 border-t border-gray-200 dark:border-white/10">
                                <p class="text-xs font-semibold text-gray-500 dark:text-white/50 uppercase mb-1.5">Cashier Sessions</p>
                                <div class="space-y-1">
                                    <template x-for="s in r.sessions" :key="s.id">
                                        <div class="flex items-center justify-between text-xs text-gray-500 dark:text-white/60">
                                            <span x-text="s.user ? (s.user.first_name || '') + ' ' + (s.user.last_name || '') : 'Unknown'"></span>
                                            <span x-text="(s.started_at ? new Date(s.started_at).toLocaleString() : '—') + (s.ended_at ? ' → ' + new Date(s.ended_at).toLocaleString() : ' → ongoing')"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </template>
        </div>
    </div>
</div>