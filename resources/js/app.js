import './bootstrap';
import Alpine from 'alpinejs';
import focus from '@alpinejs/focus';
import JsBarcode from 'jsbarcode';
import '../css/app.css';
import '../css/rtl.css';
import POS_SOUNDS from './sounds.js';

Alpine.plugin(focus);

// --- Custom Alpine barcode directive ---
Alpine.directive('barcode', (el, { expression }, { evaluate }) => {
    const val = evaluate(expression);
    if (val) {
        try {
            JsBarcode(el, val, {
                format: 'CODE128',
                width: 1.5,
                height: 30,
                displayValue: false,
                margin: 2,
                background: el.closest('.dark') ? '#1f2937' : '#ffffff',
                lineColor: el.closest('.dark') ? '#e5e7eb' : '#000000',
            });
        } catch(e) {}
    }
});

// --- Global Theme Store --- accessible from any Alpine scope via $store.theme
Alpine.store('theme', {
    dark: localStorage.getItem('theme') === 'dark'
        || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches),

    init() {
        this.apply();
    },

    toggle() {
        this.dark = !this.dark;
        localStorage.setItem('theme', this.dark ? 'dark' : 'light');
        this.apply();
    },

    apply() {
        document.documentElement.classList.toggle('dark', this.dark);
    },
});

// --- Global Currency Store ---
Alpine.store('currency', {
    code: localStorage.getItem('pos_currency_code') || 'USD',
    symbol: localStorage.getItem('pos_currency_symbol') || '$',
    decimalPlaces: parseInt(localStorage.getItem('pos_currency_decimals')) || 2,
});


// --- Global Screen Store ---
Alpine.store('screen', { width: window.innerWidth });
window.addEventListener('resize', () => { Alpine.store('screen').width = window.innerWidth; });

// --- Global System Store ---
Alpine.store('sys', {
    mode: localStorage.getItem('system_mode') || 'multi_branch',
    isSingle() { return this.mode === 'single'; },
    isMulti() { return this.mode !== 'single'; }
});

// --- Layout Data Component ---
Alpine.data('layoutData', () => ({
    rtlMode: localStorage.getItem('pos_dir') === 'rtl',
    sidebarOpen: window.innerWidth >= 1024,
    currentTime: '',
    user: null,
    branches: [],
    activeBranch: '',
    currentBranchId: '',
    companyLogo: null,
    companyName: '',
    systemMode: localStorage.getItem('system_mode') || 'multi_branch',

    init() {
        document.documentElement.dir = this.rtlMode ? 'rtl' : 'ltr';
        const token = localStorage.getItem('auth_token');
        if (token) {
            fetch('/api/auth/me', {
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json',
                }
            })
            .then(res => res.ok ? res.json() : Promise.reject(res))
            .then(data => {
                this.user = data.data || data;
                if (this.user.access_level !== undefined) localStorage.setItem('access_level', this.user.access_level);
                if (this.user.can_edit_price !== undefined) localStorage.setItem('can_edit_price', this.user.can_edit_price ? '1' : '0');
                if (this.user.system_mode !== undefined) {
                    this.systemMode = this.user.system_mode;
                    localStorage.setItem('system_mode', this.user.system_mode);
                    Alpine.store('sys').mode = this.user.system_mode;
                }
            })
            .catch(() => {});

            this.fetchBranches();
            window.POS.loadCurrencySettings();
            this.loadCompanyInfo();
        }

        window.addEventListener('resize', () => {
            this.sidebarOpen = window.innerWidth >= 1024;
        });
    },

    async loadCompanyInfo() {
        try {
            const data = await window.POS.api('/api/settings');
            if (data?.data) {
                        if (data.data.logo_url) this.companyLogo = data.data.logo_url;
                        else if (data.data.logo) this.companyLogo = data.data.logo;
                if (data.data.company_name) this.companyName = data.data.company_name;
            }
        } catch (e) { /* ignore */ }
    },

    async fetchBranches() {
        const token = localStorage.getItem('auth_token');
        if (!token) return;
        let allBranches = [];
        try {
            const res = await fetch('/api/branches', {
                headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
            });
            if (res.ok) {
                const data = await res.json();
                allBranches = data.data || data;
                const userLevel = parseInt(localStorage.getItem('access_level') || '0');
                if (userLevel >= 9) {
                    this.branches = allBranches;
                } else {
                    await this.fetchMyBranches(allBranches, token);
                }
                if (this.branches.length === 1) {
                    const bid = this.branches[0].id;
                    this.activeBranch = bid;
                    this.currentBranchId = bid;
                    localStorage.setItem('active_branch_id', bid);
                    fetch('/api/branches/' + bid + '/switch', {
                        method: 'POST',
                        headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
                    }).catch(() => {});
                } else {
                    const savedBranchId = localStorage.getItem('active_branch_id');
                    if (savedBranchId && this.branches.some(b => b.id == savedBranchId)) {
                        this.activeBranch = savedBranchId;
                        this.currentBranchId = savedBranchId;
                    }
                }
            }
        } catch (e) { this.branches = allBranches; }
    },

    async fetchMyBranches(allBranches, token) {
        try {
            const res = await fetch('/api/auth/me', {
                headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
            });
            if (res.ok) {
                const udata = await res.json();
                const user = udata.data || udata;
                if (user.branches && user.branches.length > 0) {
                    const myIds = user.branches.map(b => b.id);
                    this.branches = allBranches.filter(b => myIds.includes(b.id));
                } else if (user.branch_id) {
                    this.branches = allBranches.filter(b => b.id == user.branch_id);
                } else {
                    this.branches = allBranches;
                }
            } else {
                this.branches = allBranches;
            }
        } catch (e) { this.branches = allBranches; }
    },

    switchBranch(branchId) {
        if (!branchId) {
            localStorage.removeItem('active_branch_id');
            this.currentBranchId = '';
            this.activeBranch = '';
            window.dispatchEvent(new CustomEvent('branch-changed'));
            return;
        }
        this.currentBranchId = branchId;
        localStorage.setItem('active_branch_id', branchId);
        this.activeBranch = branchId;
        const token = localStorage.getItem('auth_token');
        if (token) {
            fetch('/api/branches/' + branchId + '/switch', {
                method: 'POST',
                headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
            }).finally(() => {
                window.location.reload();
            });
        }
    }
}));

// --- POS API Helper ---
window.POS = {
    token() {
        return localStorage.getItem('auth_token');
    },
    async api(url, options = {}) {
        const headers = {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
        };
        const token = this.token();
        if (token) headers['Authorization'] = 'Bearer ' + token;
        const branchId = localStorage.getItem('active_branch_id');
        if (branchId && localStorage.getItem('system_mode') !== 'single') headers['X-Active-Branch'] = branchId;
        options.headers = { ...headers, ...(options.headers || {}) };
        const res = await fetch(url, options);
        if (res.status === 401) { localStorage.removeItem('auth_token'); window.location.href = '/login'; return null; }
        if (!res.ok) {
            const err = await res.json().catch(() => ({ message: res.statusText }));
            throw new Error(err.message || 'Request failed');
        }
        if (res.status === 204) return null;
        return res.json();
    },
    formatCurrency(amount) {
        const symbol = Alpine.store('currency')?.symbol || '$';
        const decimals = Alpine.store('currency')?.decimalPlaces ?? 2;
        const num = parseFloat(amount) || 0;
        return symbol + num.toFixed(decimals);
    },
    async loadCurrencySettings() {
        try {
            const data = await this.api('/api/settings');
            if (data?.data) {
                const settings = data.data;
                const code = settings.currency || 'USD';
                const symbols = JSON.parse(document.querySelector('meta[name="currency-symbols"]')?.content || '{}');
                const symbol = symbols[code] || settings.currency_symbol || '$';
                Alpine.store('currency').code = code;
                Alpine.store('currency').symbol = symbol;
                localStorage.setItem('pos_currency_code', code);
                localStorage.setItem('pos_currency_symbol', symbol);
            }
        } catch (e) { /* use defaults */ }
    },
};

// Layout component is defined inline in layouts/app.blade.php

// --- POS Cart Component ---
  Alpine.data('posCart', () => ({
    cartOpen: false, mode: 'browse', showCartSheet: false,
    hasBranch: true,
    items: [], searchTerm: '', searchResults: [], activeCategory: null, products: [],
    canEditPrice: localStorage.getItem('can_edit_price') === '1',
    editingPriceIdx: null, editingPriceValue: '',
    categories: [], selectedCustomer: null, defaultCustomer: null,
    serviceType: 0, tableNumber: '',
    showCustomerSearch: false, customerSearch: '', searchedCustomers: [],
    discountType: 'percent', discountValue: 0,
    showPayment: false, paymentType: 'cash', tenderAmount: null, changeAmount: 0,
    quickPaymentTypes: [],
    register: { is_open: false, register: null, summary: null },
    showOpenRegister: false, showCloseRegister: false, showCashInOut: false, showRegisterHistory: false,
    registerHistory: [], registerHistoryLoading: false,
    openRegisterForm: { opening_cash: '', shift_id: '', note: '' },
    closeRegisterForm: { actual_cash: '', note: '' },
    cashInOutForm: { type: 'in', amount: '', reason: '' },
    registerLoading: false, registerSaving: false,
    shifts: [], cashInReasons: [], cashOutReasons: [],
    processingPayment: false, toast: { show: false, message: '', type: 'success' },
    showReceipt: false, receiptData: null, receiptApiUrl: null,
    showHoldOrders: false, holdOrders: [], holdOrdersLoading: false,
    showAuthRedirect: false, uploading: false, existingOrderId: null, promoDiscount: 0, orderTotal: null,
    showQuickCustomerForm: false, quickCustomerPhone: '', quickCustomerName: '', quickCustomerSaving: false,
    showShortcutsHelp: false,
    stockMap: {}, allowNegativeStock: false, productPage: 1, hasMoreProducts: false, _scanning: false,
    _productCache: {}, _promoCache: { ts: 0, data: 0 },
    get hasBranch() { return !!(localStorage.getItem('active_branch_id')) || (localStorage.getItem('system_mode') === 'single'); },
    posSettings: {
        grid_columns: 4, grid_rows: 4, rounding_rule: 'none',
        sound_effects: true, payment_confirmation: true,
        notification_duration: 3, notification_position: 'bottom-center',
        receipt_auto_print: false,
        dine_in_enabled: null, takeaway_enabled: null, table_management_enabled: null,
        _settingsReady: false, _uiReady: false,
    },

    get discount() {
        if (!this.discountValue || this.discountValue <= 0) return 0;
        if (this.discountType === 'percent') return this.subtotal * (this.discountValue / 100);
        return parseFloat(this.discountValue);
    },
    get subtotal() { return this.items.reduce((sum, i) => sum + i.price * i.qty, 0); },
    taxRate: 0, taxIsFixed: false, fiscalItems: {},
    get tax() {
        let total = 0;
        const subtotal = this.subtotal;
        const discountAmount = this.discount + this.promoDiscount;
        for (const item of this.items) {
            const product = this.products.find(p => p.id === item.id);
            const itemTotal = item.price * item.qty;
            const discountShare = subtotal > 0 ? discountAmount * (itemTotal / subtotal) : 0;
            const taxable = itemTotal - discountShare;
            if (product && product.plu && this.fiscalItems[product.plu]) {
                const rate = parseFloat((this.fiscalItems[product.plu] || '').replace(/[^0-9.]/g, ''));
                if (rate > 0) total += taxable * (rate / 100);
                else if (this.taxRate > 0) total += taxable * (this.taxRate / 100);
            } else if (this.taxIsFixed) {
                total = this.taxRate;
            } else if (this.taxRate > 0) {
                total += taxable * (this.taxRate / 100);
            }
        }
        return Math.round(total * 100) / 100;
    },
    applyRounding(amount) {
        const rule = this.posSettings.rounding_rule || 'none';
        if (rule === 'none') return amount;
        if (rule === 'nearest_001') return Math.round(amount * 100) / 100;
        if (rule === 'nearest_005') return Math.round(amount * 20) / 20;
        if (rule === 'nearest_010') return Math.round(amount * 10) / 10;
        if (rule === 'nearest_050') return Math.round(amount * 2) / 2;
        if (rule === 'nearest_1') return Math.round(amount);
        return amount;
    },
    get grandTotal() { return this.orderTotal != null ? this.orderTotal : this.applyRounding(this.subtotal - this.discount - this.promoDiscount + this.tax); },
    get registerInactiveMins() {
        if (!this.register.is_open) return null;
        const la = this.register.register?.last_activity_at;
        if (!la) return null;
        return Math.max(0, Math.floor((Date.now() - new Date(la).getTime()) / 60000));
    },
    get filteredProducts() {
        let base = this.products;
        if (this.searchTerm) {
            const q = this.searchTerm.toLowerCase();
            base = this.products.filter(p =>
                p.name.toLowerCase().includes(q) || (p.code && p.code.toLowerCase().includes(q)));
        }
        return [...base].sort((a, b) => String(a.name || '').localeCompare(String(b.name || '')));
    },

    get gridStyle() {
        const w = this.$store.screen.width;
        const cols = this.posSettings.grid_columns || 4;
        if (w < 640) return `grid-template-columns: repeat(2, minmax(0, 1fr))`;
        if (w < 768) return `grid-template-columns: repeat(3, minmax(0, 1fr))`;
        const rawMin = getComputedStyle(document.documentElement).getPropertyValue('--pos-card-min').trim();
        const rootFont = parseFloat(getComputedStyle(document.documentElement).fontSize) || 16;
        const minCard = (rawMin.endsWith('rem') ? (parseFloat(rawMin) || 168) * rootFont : (parseFloat(rawMin) || 168));
        const container = document.querySelector('.pos-product-grid')?.clientWidth || w;
        const fit = Math.max(2, Math.floor(container / minCard));
        return `grid-template-columns: repeat(${Math.min(cols, fit)}, minmax(0, 1fr))`;
    },

    async init() {
        const token = window.POS.token();
        if (!token) { this.showAuthRedirect = true; return; }
        await this.loadPosSettings();
        this.posSettings._uiReady = true;
        await Promise.all([
            this.loadProducts(),
            this.loadCategories(),
            this.loadFiscalItems(),
            this.loadTaxRate(),
            this.loadDefaultCustomer(),
            this.loadPaymentTypes(),
            this.loadRegisterStatus(),
            this.loadRegisterConfig(),
            this.loadUserPermission(),
        ]);
        window.addEventListener('branch-changed', () => {
            this.items = [];
            this.discountValue = 0;
            this.promoDiscount = 0;
            this.existingOrderId = null;
            this.orderTotal = null;
            this.showPayment = false;
            this.loadProducts();
            this.saveCart();
        });
        this.loadCart();
        this.$watch('discountValue', () => this.saveCart());
        this.$watch('discountType', () => this.saveCart());
        this.$watch('tableNumber', () => this.saveCart());
        this.$watch('serviceType', () => this.saveCart());
        const params = new URLSearchParams(window.location.search);
        const orderId = params.get('order');
        if (orderId) { await this.loadOrder(orderId); }
        this._kbHandler = (e) => {
            if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA' || e.target.isContentEditable) return;
            const pts = this.quickPaymentTypes;
            if (pts.length > 0 && e.key === 'F1') { e.preventDefault(); this.openPayment(pts[0]); }
            else if (pts.length > 1 && e.key === 'F2') { e.preventDefault(); this.openPayment(pts[1]); }
            else if (pts.length > 2 && e.key === 'F3') { e.preventDefault(); this.openPayment(pts[2]); }
            else if (pts.length > 3 && e.key === 'F4') { e.preventDefault(); this.openPayment(pts[3]); }
            else if (pts.length > 4 && e.key === 'F5') { e.preventDefault(); this.openPayment(pts[4]); }
            else if (e.key === 'F8') { e.preventDefault(); this.newSale(); }
            else if (e.key === 'F10') { e.preventDefault(); this.$refs.receiptFrame?.contentWindow?.print(); }
            else if (e.key === 'Escape' && this.showPayment) { e.preventDefault(); this.showPayment = false; }
            else if (e.key === '?' && e.shiftKey) { e.preventDefault(); this.showShortcutsHelp = !this.showShortcutsHelp; }
        };
        window.addEventListener('keydown', this._kbHandler);
    },
    async loadPosSettings() {
        try {
            const data = await window.POS.api('/api/settings');
            if (data?.data) {
                const s = data.data;
                if (s.grid_columns) this.posSettings.grid_columns = parseInt(s.grid_columns) || 4;
                if (s.grid_rows) this.posSettings.grid_rows = parseInt(s.grid_rows) || 4;
                if (s.rounding_rule) this.posSettings.rounding_rule = s.rounding_rule;
                if (s.sound_effects !== undefined) this.posSettings.sound_effects = s.sound_effects === 'true' || s.sound_effects === true;
                if (s.payment_confirmation !== undefined) this.posSettings.payment_confirmation = s.payment_confirmation === 'true' || s.payment_confirmation === true;
                if (s.notification_duration) this.posSettings.notification_duration = parseInt(s.notification_duration) || 3;
                if (s.notification_position) this.posSettings.notification_position = s.notification_position;
                if (s.receipt_auto_print !== undefined) this.posSettings.receipt_auto_print = s.receipt_auto_print === 'true' || s.receipt_auto_print === true;
                if (s.dine_in_enabled !== undefined) this.posSettings.dine_in_enabled = s.dine_in_enabled === 'true' || s.dine_in_enabled === true;
                if (s.takeaway_enabled !== undefined) this.posSettings.takeaway_enabled = s.takeaway_enabled === 'true' || s.takeaway_enabled === true;
                if (s.table_management_enabled !== undefined) this.posSettings.table_management_enabled = s.table_management_enabled === 'true' || s.table_management_enabled === true;
            }
        } catch (e) { /* use defaults */ } finally { this.posSettings._settingsReady = true; }
    },
    async loadDefaultCustomer() {
        try {
            const data = await window.POS.api('/api/customers?search=Walk-in');
            const customers = Array.isArray(data?.data) ? data.data : (data?.data?.data || []);
            if (customers.length > 0) {
                this.selectedCustomer = customers[0];
                this.defaultCustomer = customers[0];
            }
        } catch (e) { /* ignore */ }
    },
    async loadStockSummary() {
        try {
            const data = await window.POS.api('/api/stock/pos-summary');
            this.stockMap = {};
            const list = Array.isArray(data?.data) ? data.data : (data?.data?.data || []);
            list.forEach(s => { this.stockMap[s.product_id] = s; });
            this.allowNegativeStock = list.length > 0 ? !!(list._allow_negative) : false;
        } catch (e) { /* stock tracking optional */ }
    },
    async loadPaymentTypes() {
        try {
            const d = await window.POS.api('/api/payment-types/all');
            const types = d?.data || [];
            const colors = ['emerald', 'blue', 'violet', 'amber', 'rose', 'cyan', 'indigo', 'teal',
                           'orange', 'pink', 'lime', 'sky', 'purple', 'yellow', 'red', 'green'];
            this.quickPaymentTypes = types.filter(t => t.is_quick_payment && t.is_enabled).map((t, i) => ({
                ...t,
                color: colors[i % colors.length],
            }));
            if (this.quickPaymentTypes.length === 0) {
                this.quickPaymentTypes = [
                    { id: 'cash', name: 'Cash', code: 'cash', color: 'emerald' },
                    { id: 'card', name: 'Card', code: 'card', color: 'blue' },
                    { id: 'check', name: 'Check', code: 'check', color: 'gray' },
                ];
            }
        } catch (e) {
            this.quickPaymentTypes = [
                { id: 'cash', name: 'Cash', code: 'cash', color: 'emerald' },
                { id: 'card', name: 'Card', code: 'card', color: 'blue' },
                { id: 'check', name: 'Check', code: 'check', color: 'gray' },
            ];
        }
    },
    async loadRegisterStatus() {
        try {
            const d = await window.POS.api('/api/cash-register/status');
            this.register = d?.data || { is_open: false, register: null, summary: null };
        } catch (e) {
            this.register = { is_open: false, register: null, summary: null };
        }
    },
    async loadUserPermission() {
        try {
            const d = await window.POS.api('/api/auth/me');
            const u = d?.data || d;
            this.canEditPrice = u.can_edit_price === true || u.can_edit_price === 1;
            localStorage.setItem('can_edit_price', this.canEditPrice ? '1' : '0');
        } catch (e) { /* keep current */ }
    },
    async loadRegisterConfig() {        try {
            const [shiftsRes, settingsRes] = await Promise.all([
                window.POS.api('/api/shifts'),
                window.POS.api('/api/settings'),
            ]);
            this.shifts = (shiftsRes?.data || []).filter(s => s.is_enabled);
            const settings = settingsRes?.data || {};
            const parse = (v) => {
                if (Array.isArray(v)) return v;
                if (typeof v === 'string') { try { const p = JSON.parse(v); return Array.isArray(p) ? p : []; } catch (e) { return []; } }
                return [];
            };
            this.cashInReasons = parse(settings.cash_in_reasons);
            this.cashOutReasons = parse(settings.cash_out_reasons);
        } catch (e) {
            this.shifts = [];
            this.cashInReasons = [];
            this.cashOutReasons = [];
        }
    },
    async openRegisterHistory() {
        this.showRegisterHistory = true;
        this.registerHistoryLoading = true;
        this.registerHistory = [];
        try {
            const d = await window.POS.api('/api/cash-register/history?per_page=50');
            this.registerHistory = d?.data?.data || d?.data || [];
        } catch (e) {
            this.registerHistory = [];
        } finally {
            this.registerHistoryLoading = false;
        }
    },
    openRegisterModal() { this.openRegisterForm = { opening_cash: '', shift_id: '', note: '' }; this.showOpenRegister = true; },    async openRegister() {
        const amount = parseFloat(this.openRegisterForm.opening_cash);
        if (!amount || amount < 0) { alert('Enter a valid opening cash amount.'); return; }
        this.registerSaving = true;
        try {
            await window.POS.api('/api/cash-register/open', {
                method: 'POST',
                body: JSON.stringify({ opening_cash: amount, shift_id: this.openRegisterForm.shift_id || null, note: this.openRegisterForm.note }),
            });
            this.showOpenRegister = false;
            await this.loadRegisterStatus();
            this.toastMsg('Register opened', 'success');
        } catch (e) { alert(e.message || 'Failed to open register'); }
        finally { this.registerSaving = false; }
    },
    openCloseRegisterModal() { this.closeRegisterForm = { actual_cash: '', note: '' }; this.showCloseRegister = true; },
    async closeRegister() {
        const actual = parseFloat(this.closeRegisterForm.actual_cash);
        if (isNaN(actual) || actual < 0) { alert('Enter the actual cash counted.'); return; }
        this.registerSaving = true;
        try {
            await window.POS.api('/api/cash-register/close', {
                method: 'POST',
                body: JSON.stringify({ actual_cash: actual, note: this.closeRegisterForm.note }),
            });
            this.showCloseRegister = false;
            await this.loadRegisterStatus();
            this.toastMsg('Register closed', 'success');
        } catch (e) { alert(e.message || 'Failed to close register'); }
        finally { this.registerSaving = false; }
    },
    openCashInOutModal(type) { this.cashInOutForm = { type: type, amount: '', reason: '' }; this.showCashInOut = true; },
    async recordCashInOut() {
        const amount = parseFloat(this.cashInOutForm.amount);
        if (!amount || amount <= 0) { alert('Enter a valid amount.'); return; }
        this.registerSaving = true;
        try {
            await window.POS.api('/api/cash-register/cash-in-out', {
                method: 'POST',
                body: JSON.stringify({ type: this.cashInOutForm.type, amount: amount, reason: this.cashInOutForm.reason }),
            });
            this.showCashInOut = false;
            await this.loadRegisterStatus();
            this.toastMsg('Cash ' + (this.cashInOutForm.type === 'in' ? 'in' : 'out') + ' recorded', 'success');
        } catch (e) { alert(e.message || 'Failed to record cash movement'); }
        finally { this.registerSaving = false; }
    },
    async loadDefaultStocks() {
        if (this.items.length === 0) return;
        try {
            const ids = [...new Set(this.items.map(i => i.product_id))];
            const data = await window.POS.api('/api/stock/pos-summary?product_ids=' + ids.join(','));
            const list = Array.isArray(data?.data) ? data.data : (data?.data?.data || []);
            list.forEach(s => { this.stockMap[s.product_id] = s; });
        } catch (e) {}
    },
    async loadOrder(orderId) {        try {
            const data = await window.POS.api('/api/orders/' + orderId);
            const order = data?.data;
            if (!order) { this.toastMsg('Order not found', 'error'); return; }
            if (!order.pos_order_items || order.pos_order_items.length === 0) { this.toastMsg('Order has no items', 'error'); return; }
            this.existingOrderId = orderId;
            this.promoDiscount = 0;
            this.orderTotal = parseFloat(order.total) || null;
            this.items = order.pos_order_items.map(i => ({
                    id: i.id, product_id: i.product_id,
                    name: i.product?.name || 'Item',
                    price: parseFloat(i.price), qty: parseFloat(i.quantity)
                }));
                const storedDiscount = parseFloat(order.discount) || 0;
                const storedDiscountType = parseInt(order.discount_type ?? 0);
                this.discountType = storedDiscountType === 1 ? 'flat' : 'percent';
                if (this.discountType === 'percent') {
                    const sub = this.items.reduce((s, i) => s + i.price * i.qty, 0);
                    this.discountValue = sub > 0 ? Math.round((storedDiscount / sub) * 10000) / 100 : 0;
                } else {
                    this.discountValue = storedDiscount;
                }
                this.tableNumber = order.table_number || '';
                this.selectedCustomer = order.customer || null;
                this.serviceType = order.service_type || 0;
        } catch (e) { this.toastMsg('Failed to load order', 'error'); }
    },
    async openHoldOrders() {
        this.showHoldOrders = true;
        await this.loadHoldOrders();
    },
    async loadHoldOrders() {
        this.holdOrdersLoading = true;
        try {
            const d = await window.POS.api('/api/orders/hold-list');
            this.holdOrders = d?.data || [];
        } catch (e) { this.holdOrders = []; }
        finally { this.holdOrdersLoading = false; }
    },
    async holdCurrentOrder() {
        if (!this.items.length) return;
        try {
            if (this.existingOrderId) {
                await window.POS.api('/api/orders/' + this.existingOrderId + '/hold', { method: 'POST' });
            } else {
                const payload = { items: this.items.map(i => ({ product_id: i.product_id || i.id, quantity: i.qty, price: i.price })), customer_id: this.selectedCustomer?.id || null, table_number: this.tableNumber || null, service_type: this.serviceType, discount: this.discount, discount_type: this.discountType === 'percent' ? 0 : 1, total: this.grandTotal, tax_amount: this.tax };
                const r = await window.POS.api('/api/orders', { method: 'POST', body: JSON.stringify(payload) });
                const oid = r?.data?.id;
                if (oid) await window.POS.api('/api/orders/' + oid + '/hold', { method: 'POST' });
            }
            this.items = []; this.discountType = 'percent'; this.discountValue = 0; this.promoDiscount = 0; this.selectedCustomer = this.defaultCustomer || null; this.existingOrderId = null; this.orderTotal = null;
            this.saveCart();
            this.toastMsg('Order held', 'success');
            await this.loadHoldOrders();
        } catch (e) { this.toastMsg('Failed to hold order', 'error'); }
    },
    async resumeHoldOrder(id) {
        this.showHoldOrders = false;
        try {
            await window.POS.api('/api/orders/' + id + '/resume', { method: 'POST' });
        } catch (e) { /* order may already be open — still load it */ }
        try {
            await this.loadOrder(id);
            this.saveCart();
            if (this.items.length) this.mode = 'build';
        } catch (e) { this.toastMsg('Failed to load order', 'error'); }
    },
    async cancelHoldOrder(id) {
        if (!confirm('Cancel this order?')) return;
        try {
            await window.POS.api('/api/orders/' + id + '/cancel', { method: 'POST' });
            await this.loadHoldOrders();
            this.toastMsg('Order cancelled', 'success');
        } catch (e) { this.toastMsg('Failed to cancel order', 'error'); }
    },
    async loadCategories() {
        try {
            const data = await window.POS.api('/api/product-groups');
            this.categories = Array.isArray(data?.data) ? data.data : (data?.data?.data || []);
        } catch (e) { this.categories = []; }
    },
    async loadFiscalItems() {
        try {
            const data = await window.POS.api('/api/fiscal-items');
            const items = data?.data?.data || data?.data || [];
            this.fiscalItems = {};
            items.forEach(fi => {
                if (fi.plu && fi.vat) this.fiscalItems[fi.plu] = fi.vat;
            });
        } catch (e) { this.fiscalItems = {}; }
    },
    async loadTaxRate() {
        try {
            const data = await window.POS.api('/api/taxes');
            const taxes = data?.data || [];
            const enabled = taxes.filter(t => t.is_enabled);
            if (enabled.length > 0) {
                this.taxRate = parseFloat(enabled[0].rate);
                this.taxIsFixed = !!enabled[0].is_fixed;
            } else {
                this.taxRate = 0;
                this.taxIsFixed = false;
            }
        } catch (e) { this.taxRate = 0; this.taxIsFixed = false; }
    },
    async loadProducts(append = false) {
        try {
            if (!append) { this.productPage = 1; this.hasMoreProducts = false; }
            let url = `/api/products?pos=1&per_page=200&page=${this.productPage}`;
            if (this.activeCategory && !this.searchTerm) url += `&product_group_id=${this.activeCategory}`;
            if (this.searchTerm) url += `&search=${encodeURIComponent(this.searchTerm)}`;
            const res = await window.POS.api(url);
            const meta = res?.data;
            const data = Array.isArray(meta?.data) ? meta.data : [];
            if (append) { this.products = [...this.products, ...data]; }
            else { this.products = data; }
            data.forEach(p => {
                if (p.current_stock !== undefined) {
                    this.stockMap[p.id] = { product_id: p.id, current_stock: p.current_stock, product_name: p.name };
                }
            });
            this.hasMoreProducts = data.length > 0 && (meta?.current_page || 0) < (meta?.last_page || 0);
            this.productPage++;
        } catch (e) { this.toastMsg('Failed to load products', 'error'); this.products = []; }
    },
    async loadMoreProducts() { if (this.hasMoreProducts) await this.loadProducts(true); },
    _searchTimer: null,
    onPaste() {
        clearTimeout(this._searchTimer);
        this._searchTimer = setTimeout(() => {
            const term = this.searchTerm?.trim();
            if (term && term.length >= 2) {
                this.searchBarcode(term);
            }
        }, 50);
    },
    async searchBarcode(term) {
        if (this._scanning) return;
        this._scanning = true;
        try {
            const res = await window.POS.api('/api/products?pos=1&search=' + encodeURIComponent(term));
            const items = Array.isArray(res?.data?.data) ? res.data.data : [];
            items.forEach(p => {
                if (p.current_stock !== undefined) this.stockMap[p.id] = { product_id: p.id, current_stock: p.current_stock, product_name: p.name };
            });
            this.products = items;
        } catch(e) { this.products = []; }
        finally { this._scanning = false; }
    },
    smartSearch() {
        clearTimeout(this._searchTimer);
        const isBarcode = /^\d{5,}$/.test(this.searchTerm.trim());
        const delay = isBarcode ? 50 : 300;
        this._searchTimer = setTimeout(() => this.searchProducts(), delay);
    },
    async searchProducts() {
        if (!this.searchTerm || this.searchTerm.length < 2) { await this.loadProducts(); this.searchResults = []; return; }
        await this.loadProducts(false);
        this.searchResults = this.filteredProducts.slice(0, 6);
    },async handleBarcodeSearch() {
        if (this._scanning) return; const term = this.searchTerm.trim(); if (!term) return;
        this._scanning = true;
        try { const data = await window.POS.api('/api/products?pos=1&search=' + encodeURIComponent(term)); const items = Array.isArray(data?.data?.data) ? data.data.data : []; const p = items[0]; if (p) { if (p.current_stock !== undefined) this.stockMap[p.id] = { product_id: p.id, current_stock: p.current_stock, product_name: p.name }; this.addToCart(p); this.searchTerm = ''; this.searchResults = []; } else this.toastMsg('Product not found', 'error'); } catch (e) { this.toastMsg('Product not found', 'error'); }
        finally { this._scanning = false; }
    },
    async handleFileUpload(event) {
        const file = event.target.files[0]; if (!file) return;
        this.uploading = true;
        try {
            const text = await file.text(); let products = [];
            if (file.name.endsWith('.csv')) { products = this.parseCSV(text); }
            else if (file.name.endsWith('.json')) { const j = JSON.parse(text); products = Array.isArray(j) ? j : (j.data || j.products || []); }
            if (!products.length) { this.toastMsg('No products found', 'error'); return; }
            let imported = 0;
            const groups = await window.POS.api('/api/product-groups');
            const groupMap = {};
            if (Array.isArray(groups?.data?.data)) groups.data.data.forEach(g => { groupMap[g.name.toLowerCase()] = g.id; });
            else if (Array.isArray(groups?.data)) groups.data.forEach(g => { groupMap[g.name.toLowerCase()] = g.id; });
            for (const p of products) {
                const payload = { name: p.name || p.product_name || '', code: p.code || p.sku || '', price: parseFloat(p.price) || 0, cost: parseFloat(p.cost) || 0 };
                if (p.group && !groupMap[p.group.toLowerCase()]) {
                    try {
                        const ng = await window.POS.api('/api/product-groups', { method: 'POST', body: JSON.stringify({ name: p.group }) });
                        groupMap[p.group.toLowerCase()] = ng?.data?.id || ng?.id;
                    } catch (e) {}
                }
                if (p.group && groupMap[p.group.toLowerCase()]) payload.product_group_id = groupMap[p.group.toLowerCase()];
                try { await window.POS.api('/api/products', { method: 'POST', body: JSON.stringify(payload) }); imported++; } catch (e) {}
            }
            await this.loadProducts(); this.toastMsg('Imported ' + imported + ' products', 'success');
        } catch (e) { this.toastMsg('Failed to parse file', 'error'); }
        finally { this.uploading = false; event.target.value = ''; }
    },
    parseCSV(text) {
        const lines = text.split('\n').filter(l => l.trim()); if (lines.length < 2) return [];
        const h = lines[0].split(',').map(c => c.trim().toLowerCase().replace(/"/g, ''));
        const ni = h.indexOf('name'), pi = h.indexOf('price'), ci = h.indexOf('code') >= 0 ? h.indexOf('code') : h.indexOf('sku'), oi = h.indexOf('cost'), gi = h.indexOf('group');
        return lines.slice(1).map(l => { const c = l.split(',').map(v => v.trim().replace(/"/g, '')); return ni >= 0 && c[ni] ? { name: c[ni], price: parseFloat(c[pi]) || 0, code: ci >= 0 ? c[ci] : '', cost: oi >= 0 ? (parseFloat(c[oi]) || 0) : 0, group: gi >= 0 ? c[gi] : '' } : null; }).filter(Boolean);
    },
    async refreshProducts() { await this.loadProducts(); this.toastMsg('Products refreshed', 'success'); },
    addToCart(product) {
        const stock = this.stockMap[product.id];
        if (product.track_inventory && stock && stock.current_stock <= 0) {
            this.toastMsg('Out of stock: ' + product.name, 'error');
            return;
        }
        const existing = this.items.find(i => i.id === product.id);
        if (existing) { existing.qty++; } else { this.items.push({ id: product.id, product_id: product.id, name: product.name, price: parseFloat(product.price), qty: 1 });
            if (this.posSettings.sound_effects) POS_SOUNDS.addItem(); }
        this.showToast('Added: ' + product.name);
        this.refreshPromoDiscounts();
        this.saveCart();
    },
    async searchCustomers() {
        const term = (this.customerSearch || '').trim();
        if (term.length < 1) { this.searchedCustomers = []; return; }
        try {
            const data = await window.POS.api('/api/customers?search=' + encodeURIComponent(term));
            if (data && data.data) {
                if (Array.isArray(data.data)) {
                    this.searchedCustomers = data.data.slice(0, 5);
                } else if (data.data.data && Array.isArray(data.data.data)) {
                    this.searchedCustomers = data.data.data.slice(0, 5);
                } else {
                    this.searchedCustomers = [];
                }
            } else {
                this.searchedCustomers = [];
            }
        } catch (e) { this.searchedCustomers = []; }
    },
    selectCustomer(customer) {
        this.selectedCustomer = customer;
        this.customerSearch = '';
        this.searchedCustomers = [];
        this.showCustomerSearch = false;
        this.saveCart();
    },
    async quickCreateCustomer() {
        const phone = (this.quickCustomerPhone || '').trim();
        if (!phone || phone.length < 7) { this.toastMsg('Please enter a valid phone number', 'error'); return; }
        this.quickCustomerSaving = true;
        try {
            const res = await window.POS.api('/api/customers/quick', { method: 'POST', body: JSON.stringify({ phone_number: phone, name: (this.quickCustomerName || '').trim() || null }) });
            const customer = res?.data;
            if (!customer) { this.toastMsg('Failed to create customer', 'error'); return; }
            this.selectedCustomer = customer;
            this.showQuickCustomerForm = false;
            this.showCustomerSearch = false;
            this.quickCustomerPhone = '';
            this.quickCustomerName = '';
            this.customerSearch = '';
            this.searchedCustomers = [];
            const msg = res?.existing ? 'Customer found & selected' : 'Customer created & selected';
            this.toastMsg(msg, 'success');
        } catch (e) { this.toastMsg('Failed', 'error'); }
        finally { this.quickCustomerSaving = false; }
    },
    removeItem(idx) { const item = this.items[idx]; this.items.splice(idx, 1); this.showToast('Removed: ' + item.name);
            if (this.posSettings.sound_effects) POS_SOUNDS.removeItem(); this.refreshPromoDiscounts(); this.saveCart(); },
    updateQty(idx, qty) { if (qty < 1) return; this.items[idx].qty = qty; this.refreshPromoDiscounts(); this.saveCart(); },
    startEditPrice(idx) {
        if (!this.canEditPrice) return;
        this._priceCancel = false;
        this.editingPriceIdx = idx;
        this.editingPriceValue = this.items[idx].price;
    },
    commitPrice(idx) {
        if (this._priceCancel) { this._priceCancel = false; this.editingPriceIdx = null; this.editingPriceValue = ''; return; }
        const v = parseFloat(this.editingPriceValue);
        const product = this.products.find(p => p.id === this.items[idx].product_id);
        const maxPrice = product ? (parseFloat(product.price) * 5) : null;
        if (isNaN(v) || v < 0) { this.editingPriceIdx = null; this.editingPriceValue = ''; return; }
        if (maxPrice !== null && v > maxPrice) {
            alert('Price cannot exceed 5x the product price (' + this.formatMoney(maxPrice) + ').');
            this.editingPriceIdx = null;
            this.editingPriceValue = '';
            return;
        }
        this.items[idx].price = Math.round(v * 100) / 100;
        this.editingPriceIdx = null;
        this.editingPriceValue = '';
        this.refreshPromoDiscounts();
        this.saveCart();
    },
    cancelPriceEdit() { this._priceCancel = true; this.editingPriceIdx = null; this.editingPriceValue = ''; },
    isCustomPrice(idx) {
        const item = this.items[idx];
        const product = this.products.find(p => p.id === item.product_id);
        return product ? (parseFloat(item.price) !== parseFloat(product.price)) : false;
    },
    newSale() { if (this.items.length && !confirm('Clear order?')) return; this.items = []; this.discountType = 'percent'; this.discountValue = 0; this.promoDiscount = 0; this.selectedCustomer = null; this.orderTotal = null; this.existingOrderId = null; this.saveCart(); },
    setMode(m) { this.mode = m; this.searchTerm = ''; this.searchResults = []; if (m === 'browse') this.loadProducts(); },
    saveCart() {
        try {
            const cart = {
                items: this.items.map(i => ({ id: i.id, product_id: i.product_id, name: i.name, price: i.price, qty: i.qty })),
                discountType: this.discountType,
                discountValue: this.discountValue,
                selectedCustomer: this.selectedCustomer ? { id: this.selectedCustomer.id, name: this.selectedCustomer.name } : null,
                tableNumber: this.tableNumber,
                serviceType: this.serviceType,
                savedAt: Date.now(),
            };
            if (cart.items.length) localStorage.setItem('pos_cart', JSON.stringify(cart));
            else localStorage.removeItem('pos_cart');
        } catch (e) { /* ignore */ }
    },
    loadCart() {
        try {
            const raw = localStorage.getItem('pos_cart');
            if (!raw) return;
            const cart = JSON.parse(raw);
            if (cart && Array.isArray(cart.items) && cart.items.length) {
                this.items = cart.items;
                this.discountType = cart.discountType || 'percent';
                this.discountValue = cart.discountValue || 0;
                this.tableNumber = cart.tableNumber || '';
                this.serviceType = cart.serviceType || 0;
                if (cart.selectedCustomer && cart.selectedCustomer.id) this.selectedCustomer = cart.selectedCustomer;
                this.toastMsg('Restored previous cart', 'success');
            }
        } catch (e) { /* ignore */ }
    },
    openPayment(paymentType) { if (!this.items.length) return; this.paymentType = paymentType; this.tenderAmount = this.grandTotal; this.showPayment = true; },
    async calcPromotionDiscounts() {
        const now = Date.now();
        if (now - this._promoCache.ts < 10000) return this._promoCache.data;
        let totalPromoDiscount = 0;
        try {
            const token = window.POS.token();
            const res = await fetch('/api/promotions?is_enabled=true', {
                headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' },
            });
            const data = await res.json();
            const promotions = data.data || data.data?.data || [];
            const today = new Date().getDay();

            for (const promo of promotions) {
                const daysOfWeek = parseInt(promo.days_of_week) || 127;
                if (!(daysOfWeek & (1 << today))) continue;

                const itemsRes = await fetch(`/api/promotions/${promo.id}/items`, {
                    headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' },
                });
                const itemsData = await itemsRes.json();
                const promoItems = itemsData.data || [];

                for (const pi of promoItems) {
                    for (const cartItem of this.items) {
                        if (cartItem.id == pi.uid) {
                            const basePrice = parseFloat(cartItem.price);
                            if (parseInt(pi.discount_type) === 0) {
                                totalPromoDiscount += basePrice * (parseFloat(pi.value) / 100) * cartItem.qty;
                            } else {
                                totalPromoDiscount += parseFloat(pi.value) * cartItem.qty;
                            }
                        }
                    }
                }
            }
        } catch (e) { /* ignore promo fetch errors */ }
        this._promoCache = { ts: now, data: totalPromoDiscount };
        return totalPromoDiscount;
    },
    async refreshPromoDiscounts() {
        if (!this.items.length) { this.promoDiscount = 0; return; }
        this.promoDiscount = await this.calcPromotionDiscounts();
    },
    async processPayment() {
        if (this.processingPayment || !this.items.length) return;
        if (!this.posSettings.payment_confirmation || confirm('Confirm payment of ' + window.POS.formatCurrency(this.grandTotal) + '?')) {
            this.processingPayment = true;
        } else { return; }
        this.showPayment = false;
        try {

            const paymentTypeName = this.paymentType?.code || this.paymentType?.name || this.paymentType;

            const payload = {
                items: this.items.map(i => ({ product_id: i.product_id || i.id, quantity: i.qty, price: i.price })),
                discount: this.discount + this.promoDiscount, total: this.grandTotal, payment_type: paymentTypeName,
                paid_amount: this.tenderAmount || this.grandTotal,
                customer_id: this.selectedCustomer?.id || null,
                table_number: this.tableNumber || null,
                service_type: this.serviceType,
            };
            let checkoutRes = null;
            if (this.existingOrderId) {
                try {
                    const existing = await window.POS.api('/api/orders/' + this.existingOrderId);
                    const existingItems = existing?.data?.pos_order_items || [];
                    await Promise.all(existingItems.map(oi =>
                        window.POS.api('/api/orders/' + this.existingOrderId + '/items/' + oi.id, { method: 'DELETE' })
                    ));
                } catch (e) { /* ignore sync errors */ }
                await Promise.all(payload.items.map(item =>
                    window.POS.api('/api/orders/' + this.existingOrderId + '/items', { method: 'POST', body: JSON.stringify({ product_id: item.product_id, quantity: item.quantity, price: item.price }) })
                ));
                checkoutRes = await window.POS.api('/api/orders/' + this.existingOrderId + '/checkout', { method: 'POST', body: JSON.stringify({ payment_type: paymentTypeName, paid_amount: payload.paid_amount, discount: this.discount, discount_type: this.discountType === 'percent' ? 0 : 1, promo_discount: this.promoDiscount || 0, total: this.grandTotal, customer_id: payload.customer_id, table_number: payload.table_number }) });
                this.receiptApiUrl = '/api/receipts/' + this.existingOrderId;
            } else {
                const orderRes = await window.POS.api('/api/orders', { method: 'POST', body: JSON.stringify(payload) });
                const orderId = orderRes?.data?.id;
                if (orderId) {
                    this.receiptApiUrl = '/api/receipts/' + orderId;
                    checkoutRes = await window.POS.api('/api/orders/' + orderId + '/checkout', { method: 'POST', body: JSON.stringify({ payment_type: paymentTypeName, paid_amount: payload.paid_amount, discount: this.discount, discount_type: this.discountType === 'percent' ? 0 : 1, promo_discount: this.promoDiscount || 0, total: this.grandTotal, customer_id: payload.customer_id, table_number: payload.table_number }) });
                }
            }
            const receipt = checkoutRes?.data?.receipt;
            if (receipt) {
                this.receiptData = receipt;
                this.showReceipt = true;
                if (this.posSettings.receipt_auto_print) {
                    setTimeout(() => { this.$refs.receiptFrame?.contentWindow?.print(); }, 300);
                }
            } else {
                this.toastMsg('Sale completed!', 'success');
            }
            try { if (this.posSettings.sound_effects) POS_SOUNDS.paymentComplete(); } catch (e) {}
            try {
                this.items.forEach(i => {
                    const s = this.stockMap[i.product_id];
                    if (s) {
                        s.quantity = Math.max(0, (parseFloat(s.quantity) || 0) - (i.qty || 1));
                        s.available_stock = s.quantity;
                    }
                });
            } catch (e) {}
            this.items = []; this.discountType = 'percent'; this.discountValue = 0; this.promoDiscount = 0; this.existingOrderId = null; this.orderTotal = null;
            this.selectedCustomer = this.defaultCustomer || null; this.tableNumber = null;
            this.cartOpen = false;
            this.saveCart();
        } catch (e) { this.toastMsg('Payment failed', 'error');
            this.showPayment = false;
            if (this.posSettings.sound_effects) POS_SOUNDS.error(); } finally { this.processingPayment = false; }
    },
    downloadReceipt() {
        if (!this.receiptApiUrl) return;
        const w = window.open('', '_blank');
        if (!w) return;
        window.POS.api(this.receiptApiUrl).then(r => {
            const receipt = r?.data;
            const html = receipt?.pdf_html || receipt?.receipt_html;
            if (html) {
                w.document.write(html);
                w.document.close();
                setTimeout(() => { w.print(); }, 600);
            } else { w.close(); }
        }).catch(() => { w.close(); });
    },
    formatMoney(amount) { return window.POS.formatCurrency(amount); },
    truncate(str, len) { if (!str) return ''; return str.length > len ? str.substring(0, len) + '\u2026' : str; },
    colorForProduct(product) { const colors = ['#3b82f6','#8b5cf6','#06b6d4','#f59e0b','#10b981','#ef4444','#ec4899','#6366f1','#14b8a6','#f97316']; const c = product?.color; if (c && c.startsWith('#')) return c; let hash = 0; const str = product?.name || ''; for (let i = 0; i < str.length; i++) hash = str.charCodeAt(i) + ((hash << 5) - hash); return colors[Math.abs(hash) % colors.length]; },
    textColorForProduct(product) { const bg = this.colorForProduct(product); if (!bg || !bg.startsWith('#')) return '#ffffff'; const r = parseInt(bg.slice(1,3), 16); const g = parseInt(bg.slice(3,5), 16); const b = parseInt(bg.slice(5,7), 16); const lum = (0.299 * r + 0.587 * g + 0.114 * b) / 255; return lum > 0.60 ? '#111827' : '#ffffff'; },

    stockLevel(product, stock) {
        if (!product?.track_inventory) return 'in';
        if (stock === null || stock === undefined) return 'none';
        if (stock <= 0) return 'out';
        if (stock <= 10) return 'low';
        return 'in';
    },

    stockBadgeClass(product, stock) {
        const level = this.stockLevel(product, stock);
        if (level === 'out') return 'bg-red-500 text-white';
        if (level === 'low') return 'bg-amber-400 text-amber-950';
        if (level === 'in') return 'bg-emerald-500 text-white';
        return 'bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-white/60';
    },

    stockBadgeText(product, stock) {
        const level = this.stockLevel(product, stock);
        if (level === 'out') return 'Out of Stock';
        if (level === 'low') return 'Low: ' + stock;
        if (level === 'in') {
            if (!product?.track_inventory || stock === null || stock === undefined) return 'In Stock';
            return 'In Stock: ' + stock;
        }
        return '';
    },

    stockCardClass(product, stock) {
        const level = this.stockLevel(product, stock);
        if (level === 'out') return 'border-red-300 dark:border-red-900/80';
        if (level === 'low') return 'border-amber-300 dark:border-amber-900/70';
        return '';
    },
    showToast(msg) { this.toastMsg(msg, 'success'); },
    toastMsg(message, type = 'success') { this.toast = { show: true, message, type }; clearTimeout(this._t); const duration = (this.posSettings.notification_duration || 3) * 1000; this._t = setTimeout(() => { this.toast.show = false; }, duration); },
    toastPositionClass() {
        const pos = this.posSettings.notification_position || 'bottom-center';
        const map = {
            'top-left': 'top-6 left-6', 'top-center': 'top-6 left-1/2 -translate-x-1/2', 'top-right': 'top-6 right-6',
            'bottom-left': 'bottom-6 left-6', 'bottom-center': 'bottom-6 left-1/2 -translate-x-1/2', 'bottom-right': 'bottom-6 right-6',
        };
        return map[pos] || 'bottom-6 left-1/2 -translate-x-1/2';
    },
}));

// --- Pill Scroller Component ---
Alpine.data('pillScroller', () => ({
    canScrollLeft: false,
    canScrollRight: false,
    _observer: null,

    init() {
        const track = this.$refs.pillTrack;
        if (!track) return;
        this.checkOverflow();
        this._observer = new MutationObserver(() => this.checkOverflow());
        this._observer.observe(track, { childList: true, subtree: true });
        this._dirObserver = new MutationObserver(() => this.checkOverflow());
        this._dirObserver.observe(document.documentElement, { attributes: true, attributeFilter: ['dir'] });
    },

    isRtl() {
        return (document.documentElement.dir || '').toLowerCase() === 'rtl';
    },

    checkOverflow() {
        const el = this.$refs.pillTrack;
        if (!el) { this.canScrollLeft = false; this.canScrollRight = false; return; }
        const t = 4;
        const max = el.scrollWidth - el.clientWidth;
        if (this.isRtl()) {
            this.canScrollRight = el.scrollLeft > -max + t;
            this.canScrollLeft = el.scrollLeft < -t;
        } else {
            this.canScrollLeft = el.scrollLeft > t;
            this.canScrollRight = el.scrollLeft < max - t;
        }
    },

    scrollLeft() {
        const el = this.$refs.pillTrack;
        if (!el) return;
        const amount = el.clientWidth * 0.6;
        if (this.isRtl()) {
            el.scrollBy({ left: amount, behavior: 'smooth' });
        } else {
            el.scrollBy({ left: -amount, behavior: 'smooth' });
        }
    },

    scrollRight() {
        const el = this.$refs.pillTrack;
        if (!el) return;
        const amount = el.clientWidth * 0.6;
        if (this.isRtl()) {
            el.scrollBy({ left: -amount, behavior: 'smooth' });
        } else {
            el.scrollBy({ left: amount, behavior: 'smooth' });
        }
    },
}));// --- Dashboard Component ---
Alpine.data('dashboard', () => ({
    data: null, loading: true,
    get maxRevenue() { if (!this.data?.revenue_chart?.length) return 1; return Math.max(...this.data.revenue_chart.map(d => d.value), 1); },
    get gridStyle() {
        const w = this.$store.screen.width;
        const cols = this.posSettings.grid_columns || 4;
        if (w < 640) return `grid-template-columns: repeat(2, minmax(0, 1fr))`;
        if (w < 768) return `grid-template-columns: repeat(3, minmax(0, 1fr))`;
        return `grid-template-columns: repeat(${cols}, minmax(0, 1fr))`;
    },

    async init() {
        try { this.data = await window.POS.api('/api/dashboard'); } catch (e) { this.data = { todays_sales: 0, orders_count: 0, products_count: 0, low_stock_count: 0, revenue_chart: [], recent_orders: [], avg_order_value: 0, customers_count: 0, pending_orders: 0, completed_today: 0 }; } finally { this.loading = false; }
    },
}));

// --- Products Manager Component ---
Alpine.data('productsManager', () => ({
    products: [], loading: true, search: '', pagination: {},
    showModal: false, editing: false, saving: false, productGroups: [], branches: [],
    showNewGroup: false, newGroupName: '', showManage: false, uploadingStock: false, toast: { show: false, message: '', type: 'success' },
    measurementUnits: [], showNewUnit: false, newUnitName: '', newUnitKey: '', showManageUnit: false,
    showTransferModal: false, transferring: false, transferForm: { product_code: '', quantity: 1, from_branch: '', to_branch: '' }, transferMessage: '', transferError: false,
    form: { name: '', code: '', plu: '', price: 0, cost: 0, product_group_id: null, measurement_unit: '', is_enabled: true, track_inventory: true, is_global: true, stock_qty: 0, branch_stocks: {}, barcode: '', barcode_type: 'CODE_128' },
    genLoading: false,
    get gridStyle() {
        const w = this.$store.screen.width;
        const cols = this.posSettings.grid_columns || 4;
        if (w < 640) return `grid-template-columns: repeat(2, minmax(0, 1fr))`;
        if (w < 768) return `grid-template-columns: repeat(3, minmax(0, 1fr))`;
        return `grid-template-columns: repeat(${cols}, minmax(0, 1fr))`;
    },

    async init() {
        await Promise.all([this.fetchProducts(), this.fetchGroups(), this.fetchBranches(), this.loadUnits()]);
    },
    async fetchGroups() {
        try { const r = await window.POS.api('/api/product-groups'); this.productGroups = Array.isArray(r?.data) ? r.data : (r.data?.data || []); } catch(e) { this.productGroups = []; }
    },
    async fetchBranches() {
        try { const r = await window.POS.api('/api/branches'); this.branches = Array.isArray(r?.data) ? r.data : (r.data?.data || []); } catch(e) { this.branches = []; }
    },
    async loadUnits() {
        try {
            const r = await window.POS.api('/api/settings');
            let settings = r?.data;
            if (!settings) { this.measurementUnits = {}; return; }
            let unitValue = null;
            if (!Array.isArray(settings)) {
                unitValue = settings['measurement_units'] || settings.measurement_units;
            } else {
                const s = settings.find(x => x.key === 'measurement_units');
                unitValue = s?.value;
            }
            if (unitValue) {
                this.measurementUnits = typeof unitValue === 'string' && unitValue.startsWith('{') ? JSON.parse(unitValue) : (typeof unitValue === 'object' ? unitValue : {});
            }
        } catch(e) { this.measurementUnits = {}; }
    },
    async addNewGroup() {
        const name = this.newGroupName.trim();
        if (!name) return;
        try {
            const r = await window.POS.api('/api/product-groups', { method: 'POST', body: JSON.stringify({ name }) });
            const newGroup = r?.data;
            if (newGroup) {
                this.productGroups.push(newGroup);
                this.form.product_group_id = newGroup.id;
                this.generateProductCode();
            } else {
                await this.fetchGroups();
            }
            this.newGroupName = '';
            this.showNewGroup = false;
        } catch (e) {
            let msg = e.message || 'Unknown error';
            if (e.errors?.name) { msg = e.errors.name[0]; }
            else if (e.response?.data?.errors?.name) { msg = e.response.data.errors.name[0]; }
            alert('Failed to create group: ' + msg);
        }
    },
    async deleteGroup(id) {
        if (!confirm('Delete this product group? Products in this group will be unassigned.')) return;
        try {
            await window.POS.api('/api/product-groups/' + id, { method: 'DELETE' });
            this.productGroups = this.productGroups.filter(g => g.id !== id);
            if (this.form.product_group_id === id) {
                this.form.product_group_id = null;
                this.form.code = '';
            }
            this.toastMsg('Product group deleted', 'success');
        } catch (e) {
            this.toastMsg('Failed to delete group', 'error');
        }
    },
    addNewUnit() {
        const key = this.newUnitKey.trim().toLowerCase();
        const name = this.newUnitName.trim();
        if (!key || !name) return;
        this.measurementUnits[key] = name;
        this.form.measurement_unit = key;
        this.newUnitKey = '';
        this.newUnitName = '';
        this.showNewUnit = false;
        this.saveMeasurementUnits();
    },
    async saveMeasurementUnits() {
        try { await window.POS.api('/api/settings', { method: 'POST', body: JSON.stringify({ key: 'measurement_units', value: JSON.stringify(this.measurementUnits) }) }); } catch(e) {}
    },
    deleteUnit(key) {
        if (!confirm('Delete measurement unit "' + (this.measurementUnits[key] || key) + '"?')) return;
        delete this.measurementUnits[key];
        if (this.form.measurement_unit === key) {
            this.form.measurement_unit = '';
        }
        this.saveMeasurementUnits();
        this.toastMsg('Measurement unit deleted', 'success');
    },
    async fetchProducts(page = 1) {
        this.loading = true;
        try { const r = await window.POS.api('/api/products?page=' + page + (this.search ? '&search=' + this.search : '')); this.products = r?.data?.data || r?.data || []; this.pagination = r?.data || { current_page: 1, last_page: 1, total: this.products.length, prev_page_url: null, next_page_url: null }; } catch (e) { this.products = []; this.pagination = { current_page: 1, last_page: 1, total: 0 }; } finally { this.loading = false; }
    },
    async generateBarcode() {
        this.genLoading = true;
        try {
            const d = await window.POS.api('/api/barcodes/generate', {
                method: 'POST',
                body: JSON.stringify({ product_id: this.form.id || undefined, barcode_type: this.form.barcode_type || 'CODE_128' }),
            });
            if (d?.data?.value) {
                this.form.barcode = d.data.value;
            }
        } catch(e) { /* ignore errors - barcode might have been generated already */ }
        finally { this.genLoading = false; }
    },
    async generateProductCode() {
        if (!this.form.product_group_id) return;
        try {
            const d = await window.POS.api('/api/products/next-code?product_group_id=' + this.form.product_group_id);
            if (d?.data?.code) {
                this.form.code = d.data.code;
            }
        } catch(e) { /* ignore */ }
    },
    openAdd() { this.editing = false; this.form = { name: '', code: '', price: 0, cost: 0, product_group_id: null, measurement_unit: '', is_enabled: true, track_inventory: true, is_global: true, stock_qty: 0, branch_stocks: {}, barcode: '', barcode_type: 'CODE_128' }; this.showModal = true; },
    openEdit(p) { this.editing = true; const primaryBarcode = p.barcodes?.find(b => b.is_primary) || p.barcodes?.[0]; this.form = { ...p, stock_qty: p.stock || 0, branch_stocks: {}, is_global: p.is_global !== false, barcode: primaryBarcode?.value || '', barcode_type: primaryBarcode?.barcode_type || 'CODE_128' }; if (p.branch_stocks) { p.branch_stocks.forEach(b => { this.form.branch_stocks[b.branch_id] = b.stock; }); } this.showModal = true; },
    async save() {
        this.saving = true;
        try {
            const method = this.editing ? 'PUT' : 'POST';
            const url = this.editing ? '/api/products/' + this.form.id : '/api/products';
            const payload = { name: this.form.name, code: this.form.code, price: this.form.price, cost: this.form.cost, product_group_id: this.form.product_group_id, plu: this.form.plu, measurement_unit: this.form.measurement_unit, is_enabled: this.form.is_enabled, track_inventory: this.form.track_inventory, is_global: this.form.is_global, barcode: this.form.barcode, barcode_type: this.form.barcode_type };
            const res = await window.POS.api(url, { method, body: JSON.stringify(payload) });
            const productId = this.editing ? this.form.id : (res?.data?.id || res?.id);
            if (this.form.track_inventory && productId) {
                const stockPayload = { items: [{ product_code: this.form.code, quantity: parseFloat(this.form.stock_qty) || 0 }] };
                await window.POS.api('/api/stock/bulk-update', { method: 'POST', body: JSON.stringify(stockPayload) });
                const branchIds = Object.keys(this.form.branch_stocks);
                for (const bid of branchIds) {
                    const b = this.branches.find(b => b.id === bid);
                    if (b) {
                        await window.POS.api('/api/stock/bulk-update', { method: 'POST', body: JSON.stringify({ branch_code: b.name, items: [{ product_code: this.form.code, quantity: parseFloat(this.form.branch_stocks[bid]) || 0 }] }) });
                    }
                }
            }
            this.showModal = false; this.fetchProducts();
        } catch (e) { alert(e.message); } finally { this.saving = false; }
    },
    async deleteProduct(id) { if (!confirm('Delete this product?')) return; this.products = this.products.filter(p => p.id !== id); try { await window.POS.api('/api/products/' + id, { method: 'DELETE' }); this.toastMsg('Product deleted', 'success'); } catch (e) { this.toastMsg('Delete failed', 'error'); this.fetchProducts(); } },
    async toggleStatus(product) { try { await window.POS.api('/api/products/' + product.id, { method: 'PUT', body: JSON.stringify({ is_enabled: !product.is_enabled }) }); product.is_enabled = !product.is_enabled; this.toastMsg(product.is_enabled ? 'Product enabled' : 'Product disabled', 'success'); } catch (e) { this.toastMsg('Toggle failed', 'error'); } },
    toastMsg(message, type = 'success') { this.toast = { show: true, message, type }; clearTimeout(this._t); this._t = setTimeout(() => { this.toast.show = false; }, 2500); },
    async handleStockUpload(event) {
        const file = event.target.files[0]; if (!file) return;
        this.uploadingStock = true;
        try {
            const text = await file.text();
            const lines = text.split('\n').filter(l => l.trim()); if (lines.length < 2) { this.toastMsg('File must have header + data rows', 'error'); return; }
            const h = lines[0].split(',').map(c => c.trim().toLowerCase().replace(/"/g, ''));
            const ci = h.indexOf('code') >= 0 ? h.indexOf('code') : h.indexOf('sku'), qi = h.indexOf('stock') >= 0 ? h.indexOf('stock') : h.indexOf('quantity'), bi = h.indexOf('branch');
            if (ci < 0 || qi < 0) { this.toastMsg('CSV must have "code" and "stock" columns', 'error'); return; }
            const items = lines.slice(1).map(l => { const c = l.split(',').map(v => v.trim().replace(/"/g, '')); if (!(ci >= 0 && c[ci])) return null; const item = { product_code: c[ci], quantity: parseFloat(c[qi]) || 0 }; if (bi >= 0 && c[bi]) item.branch_code = c[bi]; return item; }).filter(Boolean);
            if (!items.length) { this.toastMsg('No valid rows found', 'error'); return; }
            const payload = { items };
            if (items[0].branch_code) payload.branch_code = items[0].branch_code;
            await window.POS.api('/api/stock/bulk-update', { method: 'POST', body: JSON.stringify(payload) });
            await this.fetchProducts();
            this.toastMsg('Stock updated for ' + items.length + ' products', 'success');
        } catch (e) { this.toastMsg('Stock upload failed', 'error'); }
        finally { this.uploadingStock = false; event.target.value = ''; }
    },
    async transferStock() {
        if (!this.transferForm.product_code || !this.transferForm.quantity || !this.transferForm.from_branch || !this.transferForm.to_branch) { this.transferMessage = 'All fields required'; this.transferError = true; return; }
        this.transferring = true; this.transferMessage = ''; this.transferError = false;
        try {
            const res = await window.POS.api('/api/stock/transfer', { method: 'POST', body: JSON.stringify(this.transferForm) });
            this.transferMessage = res?.message || 'Transfer complete';
            this.transferError = false;
            this.transferForm = { product_code: '', quantity: 1, from_branch: '', to_branch: '' };
            await this.fetchProducts();
        } catch (e) { this.transferMessage = e?.message || 'Transfer failed'; this.transferError = true; }
        finally { this.transferring = false; }
    },
    formatMoney(amount) { if (!amount && amount !== 0) return '--'; const num = parseFloat(amount); if (isNaN(num)) return '--'; return (window.POS && window.POS.formatCurrency) ? window.POS.formatCurrency(num) : (Alpine.store('currency')?.symbol || '') + num.toFixed(2); },
}));

// --- Customers Manager Component ---
Alpine.data('customersManager', () => ({
    customers: [], loading: true, search: '', pagination: {},
    showModal: false, editing: false, saving: false,
    toast: { show: false, message: '', type: 'success' },
    form: { name: '', email: '', phone_number: '', code: '', is_enabled: true },
    showPaymentModal: false, showStatementModal: false,
    paymentForm: { amount: '', payment_method: 'cash' }, paymentSaving: false, paymentCustomer: null,
    statement: null, statementLoading: false,
    get gridStyle() {
        const w = this.$store.screen.width;
        const cols = this.posSettings.grid_columns || 4;
        if (w < 640) return `grid-template-columns: repeat(2, minmax(0, 1fr))`;
        if (w < 768) return `grid-template-columns: repeat(3, minmax(0, 1fr))`;
        return `grid-template-columns: repeat(${cols}, minmax(0, 1fr))`;
    },

    async init() { await this.fetchCustomers(); },
    async fetchCustomers(page = 1) {
        this.loading = true;
        try { const r = await window.POS.api('/api/customers?page=' + page + (this.search ? '&search=' + this.search : '')); this.customers = r?.data?.data || r?.data || []; this.pagination = r?.meta || r?.data?.meta || { current_page: 1, last_page: 1, total: this.customers.length }; } catch (e) { this.customers = []; this.pagination = { current_page: 1, last_page: 1, total: 0 }; } finally { this.loading = false; }
    },
    openAdd() { this.editing = false; this.form = { name: '', email: '', phone_number: '', code: '', is_enabled: true }; this.showModal = true; },
    openEdit(c) { this.editing = true; this.form = { ...c }; this.showModal = true; },
    async save() {
        this.saving = true; if (!this.form.name) { this.toastMsg('Name is required', 'error'); this.saving = false; return; }
        try {
            const method = this.editing ? 'PUT' : 'POST';
            const url = this.editing ? '/api/customers/' + this.form.id : '/api/customers';
            const res = await window.POS.api(url, { method, body: JSON.stringify(this.form) });
            if (res) { this.showModal = false; this.toastMsg(this.editing ? 'Customer updated' : 'Customer created', 'success'); this.fetchCustomers(); }
        } catch (e) { this.toastMsg(e.message || 'Save failed', 'error'); } finally { this.saving = false; }
    },
    async deleteCustomer(id) { if (!confirm('Delete this customer?')) return; this.customers = this.customers.filter(c => c.id !== id); try { await window.POS.api('/api/customers/' + id, { method: 'DELETE' }); this.toastMsg('Customer deleted', 'success'); } catch (e) { this.toastMsg('Delete failed. Refreshing...', 'error'); this.fetchCustomers(); } },
    async toggleStatus(customer) { const was = customer.is_enabled; customer.is_enabled = !customer.is_enabled; try { await window.POS.api('/api/customers/' + customer.id, { method: 'PUT', body: JSON.stringify({ is_enabled: customer.is_enabled }) }); } catch (e) { customer.is_enabled = was; this.toastMsg('Toggle failed', 'error'); } },
    toastMsg(message, type = 'success') { this.toast = { show: true, message, type }; clearTimeout(this._t); this._t = setTimeout(() => { this.toast.show = false; }, 2500); },
    formatMoney(amount) { return window.POS.formatCurrency(amount); },
    openPayment(customer) {
        this.paymentCustomer = customer;
        this.paymentForm = { amount: '', payment_method: 'cash' };
        this.showPaymentModal = true;
    },
    async savePayment() {
        const amount = parseFloat(this.paymentForm.amount);
        if (!amount || amount <= 0) { alert('Enter a valid amount.'); return; }
        this.paymentSaving = true;
        try {
            const r = await window.POS.api('/api/customers/' + this.paymentCustomer.id + '/payment', {
                method: 'POST',
                body: JSON.stringify({ amount: amount, payment_method: this.paymentForm.payment_method }),
            });
            this.showPaymentModal = false;
            await this.fetchCustomers();
            if (r?.data?.excess > 0) {
                alert('Payment recorded. ' + r.data.allocated + ' applied, ' + r.data.excess + ' overpaid (excess ignored).');
            }
            this.toastMsg('Payment recorded', 'success');
        } catch (e) { alert(e.message || 'Failed to record payment'); }
        finally { this.paymentSaving = false; }
    },
    async openStatement(customer) {
        this.showStatementModal = true;
        this.statementLoading = true;
        this.statement = null;
        try {
            const r = await window.POS.api('/api/customers/' + customer.id + '/statement');
            this.statement = r?.data || null;
        } catch (e) { this.statement = null; }
        finally { this.statementLoading = false; }
    },
}));

// --- Orders List Component ---
Alpine.data('ordersList', () => ({
    orders: [], loading: true, statusFilter: 'all', dateFilter: '', dateFrom: '', dateTo: '', searchQuery: '',
    currentPage: 1, totalPages: 1, totalOrders: 0, tableManagementEnabled: false,
    get gridStyle() {
        const w = this.$store.screen.width;
        const cols = this.posSettings.grid_columns || 4;
        if (w < 640) return `grid-template-columns: repeat(2, minmax(0, 1fr))`;
        if (w < 768) return `grid-template-columns: repeat(3, minmax(0, 1fr))`;
        return `grid-template-columns: repeat(${cols}, minmax(0, 1fr))`;
    },

    async init() { await Promise.all([this.fetchOrders(), this.fetchSettings()]); },
    async fetchSettings() {
        try {
            const data = await window.POS.api('/api/settings');
            const s = data?.data || {};
            this.tableManagementEnabled = s.table_management_enabled === 'true' || s.table_management_enabled === true;
        } catch(e) {}
    },
    setStatus(status) { this.statusFilter = status; this.currentPage = 1; this.fetchOrders(); },
    toggleToday() { this.dateFilter = this.dateFilter === 'today' ? '' : 'today'; this.currentPage = 1; this.fetchOrders(); },
    setDateRange() { this.dateFilter = ''; this.currentPage = 1; this.fetchOrders(); },
    async fetchOrders(page = 1) {
        this.loading = true;
        try {
            let url = '/api/orders?page=' + page;
            if (this.statusFilter !== 'all') url += '&status=' + this.statusFilter;
            if (this.dateFilter === 'today') {
                const now = new Date();
                const today = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-${String(now.getDate()).padStart(2, '0')}`;
                url += `&date_from=${today}&date_to=${today}`;
            } else if (this.dateFrom && this.dateTo) {
                url += `&date_from=${this.dateFrom}&date_to=${this.dateTo}`;
            }
            if (this.searchQuery) url += '&q=' + this.searchQuery;
            const data = await window.POS.api(url);
            this.orders = data.data?.data || data.data || [];
            this.currentPage = data.data?.current_page || data.meta?.current_page || 1;
            this.totalPages = data.data?.last_page || data.meta?.last_page || 1;
            this.totalOrders = data.data?.total || data.meta?.total || this.orders.length;
        } catch (e) { this.orders = []; } finally { this.loading = false; }
    },
    viewOrder(order) {
        window.location.href = '/pos?order=' + order.id;
    },
    async closeOrder(order) {
        if (!confirm('Close order #' + (order.number || order.id) + '?')) return;
        try {
            await window.POS.api('/api/orders/' + order.id + '/close', { method: 'POST' });
            await this.fetchOrders();
        } catch (e) { alert(e.message); }
    },
    async refundOrder(order) {
        if (!confirm('Refund order #' + (order.number || order.id) + '? This will restore stock.')) return;
        try {
            await window.POS.api('/api/orders/' + order.id + '/refund', { method: 'POST' });
            await this.fetchOrders();
            alert('Order refunded successfully.');
        } catch (e) { alert(e.message); }
    },
    changePage(page) {
        if (page < 1 || page > (this.totalPages || 1)) return;
        this.fetchOrders(page);
    },
    async downloadOrderReceipt(order) {
        try {
            const data = await window.POS.api('/api/receipts/' + order.id);
            const receipt = data?.data;
            const html = receipt?.pdf_html || receipt?.receipt_html;
            if (!html) { alert('Receipt not available'); return; }
            const w = window.open('', '_blank');
            if (!w) return;
            w.document.write(html);
            w.document.close();
            setTimeout(() => { w.print(); }, 600);
        } catch (e) { alert('Failed to load receipt'); }
    },
    formatMoney(amount) {
        return window.POS.formatCurrency(amount);
    },
}));

// --- Reports Component ---
Alpine.data('reportsManager', () => ({
    activeTab: 'sales', tabData: {}, loading: false, dateFrom: '', dateTo: '',
    customerId: '', customers: [], employeeId: '', employees: [], branchId: '', branches: [], pagination: {}, custPage: 1, statusFilter: 'closed',
    customerDue: { total_due: 0, customers: 0 },
    formatMoney(amount) {
        const sym = window.POS?.currency?.symbol || (Alpine.store('currency')?.symbol ?? '$');
        return sym + Number(amount || 0).toFixed(Alpine.store('currency')?.decimalPlaces ?? 2);
    },
    tabs: [
        { key: 'sales', label: 'Sales Summary' },
        { key: 'payments', label: 'Payment Methods' },
        { key: 'bestselling', label: 'Best Selling' },
        { key: 'profit-loss', label: 'Profit & Loss' },
        { key: 'customers', label: 'Customer Analytics' },
        { key: 'customer-due', label: 'Customer Due' },
        { key: 'customer-detail', label: 'Customer Detail' },
        { key: 'employee-detail', label: 'Employee Detail' },
        { key: 'tax', label: 'Tax Report' },
    ],
    get chartMax() {
        if (!this.tabData?.chart_data?.length) return 1;
        return Math.max(...this.tabData.chart_data.map(d => d.value), 1);
    },
    get gridStyle() {
        const w = this.$store.screen.width;
        const cols = this.posSettings.grid_columns || 4;
        if (w < 640) return `grid-template-columns: repeat(2, minmax(0, 1fr))`;
        if (w < 768) return `grid-template-columns: repeat(3, minmax(0, 1fr))`;
        return `grid-template-columns: repeat(${cols}, minmax(0, 1fr))`;
    },

    async init() { await Promise.all([this.fetchTabData(), this.fetchCustomers(), this.fetchEmployees(), this.fetchBranches(), this.fetchCustomerDue()]); },
    async fetchCustomerDue() {
        try {
            const r = await window.POS.api('/api/reports/customer-due');
            const d = r.data || r || {};
            this.customerDue = { total_due: Number(d.total_due || 0), customers: (d.records || []).length };
        } catch (e) { this.customerDue = { total_due: 0, customers: 0 }; }
    },
    async fetchCustomers() {
        try { const r = await window.POS.api('/api/customers?per_page=500'); this.customers = r.data?.data || r.data || []; } catch(e) { this.customers = []; }
    },
    async fetchEmployees() {
        try { const r = await window.POS.api('/api/users'); this.employees = r.data?.data || r.data || []; } catch(e) { this.employees = []; }
    },
    async fetchBranches() {
        try { const r = await window.POS.api('/api/branches'); this.branches = r.data?.data || r.data || []; } catch(e) { this.branches = []; }
    },
    async fetchTabData(page = 1) {
        if (this.activeTab === 'employee-detail' && !this.employeeId) { this.loading = false; return; }
        if (this.activeTab === 'customer-detail' && !this.customerId) { this.loading = false; return; }
        if (this.branchId && this.branchId !== 'all') { localStorage.setItem('active_branch_id', this.branchId); } else { localStorage.removeItem('active_branch_id'); }
        this.loading = true;
        try {
            const apiMap = { sales: 'sales-summary', payments: 'payments', bestselling: 'best-selling', 'profit-loss': 'profit-loss', customers: 'customers', 'customer-due': 'customer-due', tax: 'taxes', 'customer-detail': 'customer-sales', 'employee-detail': 'employee-sales' };
            let url = '/api/reports/' + (apiMap[this.activeTab] || 'sales-summary');
            let params = [];
            if (this.dateFrom) params.push('start_date=' + this.dateFrom + '&end_date=' + this.dateTo);
            params.push('page=' + page + '&per_page=25');
            if (this.statusFilter && this.statusFilter !== 'all') params.push('status=' + this.statusFilter);
            if (this.activeTab === 'employee-detail' && this.employeeId) params.push('user_id=' + this.employeeId + '&per_page=10');
            if (this.activeTab === 'customer-detail' && this.customerId) params.push('customer_id=' + this.customerId + '&per_page=10');
            
            if (params.length) url += '?' + params.join('&');
            const data = await window.POS.api(url);
            this.tabData = data?.data || data || {};
            this.pagination = data?.data?.pagination || null;
        } catch (e) { this.tabData = {}; this.pagination = null; } finally { this.loading = false; }
    },
    async downloadCustReceipt(orderId) {
        try {
            const data = await window.POS.api('/api/receipts/' + orderId);
            const receipt = data?.data;
            const html = receipt?.pdf_html || receipt?.receipt_html;
            if (!html) { alert('Receipt not available'); return; }
            const w = window.open('', '_blank');
            if (!w) return;
            w.document.write(html);
            w.document.close();
            setTimeout(() => { w.print(); }, 600);
        } catch (e) { alert('Failed to load receipt'); }
    },
}));

// --- Users Manager Component ---
Alpine.data('usersManager', () => ({
    users: [], loading: true, pagination: {}, roles: [], branches: [], currentUserId: null,
    showModal: false, editing: false, saving: false, editId: null, showPwd: false, uploadingStock: false,
    error: '',
    form: { first_name: '', last_name: '', username: '', employee_number: '', email: '', password: '', pin_code: '', access_level: 0, is_enabled: true, can_edit_price: false, branch_id: '', branch_ids: [] },
    get gridStyle() {
        const w = this.$store.screen.width;
        const cols = this.posSettings.grid_columns || 4;
        if (w < 640) return `grid-template-columns: repeat(2, minmax(0, 1fr))`;
        if (w < 768) return `grid-template-columns: repeat(3, minmax(0, 1fr))`;
        return `grid-template-columns: repeat(${cols}, minmax(0, 1fr))`;
    },

    async init() {
        try {
            const me = await window.POS.api('/api/auth/me');
            this.currentUserId = me?.data?.id || null;
        } catch(e) {}
        await Promise.all([this.fetchUsers(), this.fetchRoles(), this.fetchBranches()]);
    },
    async fetchBranches() { try { const r = await window.POS.api('/api/branches'); this.branches = r?.data || []; } catch(e) { this.branches = []; } },
    toggleBranch(id) { const i = this.form.branch_ids.indexOf(id); if (i >= 0) this.form.branch_ids.splice(i, 1); else this.form.branch_ids.push(id); },
    async fetchRoles() { try { const r = await window.POS.api('/api/roles'); this.roles = r?.data || []; } catch(e) { this.roles = []; } },
    getRoleName(level) { const r = this.roles.find(r => r.access_level == level); return r ? r.name : 'Lv' + level; },
    async fetchUsers(page = 1) {
        this.loading = true;
        try { const r = await window.POS.api('/api/users?page=' + page + '&per_page=15'); this.users = r.data?.data || r.data || []; this.pagination = r.meta || r.data?.meta || { current_page: 1, last_page: 1, total: 0, per_page: 15 }; } catch (e) { this.users = []; } finally { this.loading = false; }
    },
    openAdd() { this.editing = false; this.editId = null; this.error = ''; this.showPwd = false; this.form = { first_name: '', last_name: '', username: '', employee_number: '', email: '', password: '', pin_code: '', access_level: 0, is_enabled: true, branch_id: '', branch_ids: [] }; this.showModal = true; },
    openEdit(u) { this.editing = true; this.editId = u.id; this.error = ''; this.showPwd = false; this.form = { first_name: u.first_name || '', last_name: u.last_name || '', username: u.username || '', employee_number: u.employee_number || '', email: u.email || '', password: '', pin_code: '', access_level: u.access_level ?? 0, is_enabled: u.is_enabled ?? true, can_edit_price: !!u.can_edit_price, branch_id: u.branch_id || '', branch_ids: u.branches ? u.branches.map(b => b.id) : [] }; this.showModal = true; },
    async save() {
        this.saving = true; this.error = '';
        try {
            const method = this.editing ? 'PUT' : 'POST', url = this.editing ? '/api/users/' + this.editId : '/api/users';
            const payload = { first_name: this.form.first_name, last_name: this.form.last_name, username: this.form.username, email: this.form.email, access_level: parseInt(this.form.access_level), is_enabled: this.form.is_enabled, can_edit_price: !!this.form.can_edit_price, branch_id: this.form.branch_id || null, branch_ids: this.form.branch_ids };
            if (this.form.employee_number) payload.employee_number = parseInt(this.form.employee_number);
            if (this.form.password) payload.password = this.form.password;
            if (this.form.pin_code && this.form.pin_code.length === 4) payload.pin_code = this.form.pin_code;
            await window.POS.api(url, { method, body: JSON.stringify(payload) });
            this.showModal = false; this.fetchUsers();
        } catch (e) { this.error = e.message; } finally { this.saving = false; }
    },
    async deleteUser(id) { if (!confirm('Are you sure you want to delete this user? This action cannot be undone.')) return; try { await window.POS.api('/api/users/' + id, { method: 'DELETE' }); this.fetchUsers(); } catch (e) { alert(e.message); } },
    async toggleStatus(user) {
        const newStatus = !user.is_enabled;
        try { await window.POS.api('/api/users/' + user.id, { method: 'PATCH', body: JSON.stringify({ is_enabled: newStatus }) }); user.is_enabled = newStatus; } catch (e) { alert(e.message); return; }
        if (!newStatus && user.id === this.currentUserId) {
            localStorage.removeItem('auth_token');
            window.location.href = '/login';
        }
    },
}));

// --- Taxes Manager Component ---
Alpine.data('taxesManager', () => ({
    taxes: [], loading: true, pagination: {},
    showModal: false, editing: false, saving: false, error: '',
    form: { name: '', rate: 10, code: '', is_fixed: false, is_enabled: true },
    get gridStyle() {
        const w = this.$store.screen.width;
        const cols = this.posSettings.grid_columns || 4;
        if (w < 640) return `grid-template-columns: repeat(2, minmax(0, 1fr))`;
        if (w < 768) return `grid-template-columns: repeat(3, minmax(0, 1fr))`;
        return `grid-template-columns: repeat(${cols}, minmax(0, 1fr))`;
    },

    async init() { await this.fetchTaxes(); },
    async fetchTaxes(page = 1) {
        this.loading = true;
        try { const r = await window.POS.api('/api/taxes?page=' + page); this.taxes = r.data?.data || r.data || []; this.pagination = r.meta || r.data?.meta || { current_page: 1, last_page: 1, total: 0 }; } catch (e) { this.taxes = []; } finally { this.loading = false; }
    },
    openAdd() { this.editing = false; this.form = { name: '', rate: 10, code: '', is_fixed: false, is_enabled: true }; this.showModal = true; },
    openEdit(t) { this.editing = true; this.form = { ...t }; this.showModal = true; },
    async save() {
        this.saving = true;
        this.error = '';
        try {
            const method = this.editing ? 'PUT' : 'POST', url = this.editing ? '/api/taxes/' + this.form.id : '/api/taxes';
            await window.POS.api(url, { method, body: JSON.stringify(this.form) });
            this.showModal = false; this.fetchTaxes();
        } catch (e) { this.error = e.message || 'Failed to save tax.'; } finally { this.saving = false; }
    },
    async deleteTax(id) { if (!confirm('Delete this tax?')) return; try { await window.POS.api('/api/taxes/' + id, { method: 'DELETE' }); this.fetchTaxes(); } catch (e) { alert(e.message); } },
    async toggleStatus(tax) { try { await window.POS.api('/api/taxes/' + tax.id, { method: 'PUT', body: JSON.stringify({ is_enabled: !tax.is_enabled }) }); tax.is_enabled = !tax.is_enabled; } catch (e) { alert(e.message); } },
}));

// --- Promotions Manager Component ---
Alpine.data('promotionsManager', () => ({
    promotions: [], loading: true, pagination: {},
    showModal: false, editing: false, saving: false,
    form: { name: '', start_date: '', end_date: '', days_of_week: 127, is_enabled: true },
    get gridStyle() {
        const w = this.$store.screen.width;
        const cols = this.posSettings.grid_columns || 4;
        if (w < 640) return `grid-template-columns: repeat(2, minmax(0, 1fr))`;
        if (w < 768) return `grid-template-columns: repeat(3, minmax(0, 1fr))`;
        return `grid-template-columns: repeat(${cols}, minmax(0, 1fr))`;
    },

    async init() { await this.fetchPromotions(); },
    async fetchPromotions(page = 1) {
        this.loading = true;
        try { const r = await window.POS.api('/api/promotions?page=' + page); this.promotions = r.data?.data || r.data || []; this.pagination = r.meta || r.data?.meta || { current_page: 1, last_page: 1, total: 0 }; } catch (e) { this.promotions = []; } finally { this.loading = false; }
    },
    openAdd() { this.editing = false; this.form = { name: '', start_date: '', end_date: '', days_of_week: 127, is_enabled: true }; this.showModal = true; },
    openEdit(p) { this.editing = true; this.form = { ...p }; this.showModal = true; },
    async save() {
        this.saving = true;
        try {
            const method = this.editing ? 'PUT' : 'POST', url = this.editing ? '/api/promotions/' + this.form.id : '/api/promotions';
            await window.POS.api(url, { method, body: JSON.stringify(this.form) });
            this.showModal = false; this.fetchPromotions();
        } catch (e) { alert(e.message); } finally { this.saving = false; }
    },
    async deletePromotion(id) { if (!confirm('Delete this promotion?')) return; try { await window.POS.api('/api/promotions/' + id, { method: 'DELETE' }); this.fetchPromotions(); } catch (e) { alert(e.message); } },
    async togglePromotion(id) { try { await window.POS.api('/api/promotions/' + id + '/toggle', { method: 'POST' }); this.fetchPromotions(); } catch (e) { alert(e.message); } },
}));

// --- Loyalty Manager Component ---
Alpine.data('loyaltyManager', () => ({
    cards: [], loading: true, pagination: {}, customers: [],
    showModal: false, points: 0, selectedCard: null, transactionType: 'earn',
    showAddCard: false, newCard: { customer_id: '', card_number: '' },
    get gridStyle() {
        const w = this.$store.screen.width;
        const cols = this.posSettings.grid_columns || 4;
        if (w < 640) return `grid-template-columns: repeat(2, minmax(0, 1fr))`;
        if (w < 768) return `grid-template-columns: repeat(3, minmax(0, 1fr))`;
        return `grid-template-columns: repeat(${cols}, minmax(0, 1fr))`;
    },

    async init() { await Promise.all([this.fetchCards(), this.fetchCustomers()]); },
    async fetchCustomers() {
        try { const r = await window.POS.api('/api/customers'); this.customers = r?.data?.data || r?.data || []; } catch (e) { this.customers = []; }
    },
    async fetchCards(page = 1) {
        this.loading = true;
        try { const r = await window.POS.api('/api/loyalty?page=' + page); this.cards = r?.data?.data || r?.data || []; this.pagination = r.meta || r.data?.meta || { current_page: 1, last_page: 1, total: 0 }; } catch (e) { this.cards = []; } finally { this.loading = false; }
    },
    openPointsModal(card, type) { this.selectedCard = card; this.transactionType = type; this.points = 0; this.showModal = true; },
    async processPoints() {
        try {
            const url = '/api/loyalty/' + this.selectedCard.id + '/' + this.transactionType;
            await window.POS.api(url, { method: 'POST', body: JSON.stringify({ points: this.points }) });
            if (this.transactionType === 'earn') { this.selectedCard.points_balance += parseInt(this.points); this.selectedCard.total_points_earned += parseInt(this.points); }
            else { this.selectedCard.points_balance -= parseInt(this.points); }
            this.showModal = false;
        } catch (e) { alert(e.message); }
    },
    async createCard() {
        if (!this.newCard.customer_id) { alert('Please select a customer'); return; }
        try {
            await window.POS.api('/api/loyalty', { method: 'POST', body: JSON.stringify(this.newCard) });
            this.showAddCard = false; this.newCard = { customer_id: '', card_number: '' }; this.fetchCards();
        } catch (e) { alert(e.message); }
    },
    async deleteCard(id) { if (!confirm('Delete this loyalty card?')) return; this.cards = this.cards.filter(c => c.id !== id); try { await window.POS.api('/api/loyalty/' + id, { method: 'DELETE' }); } catch (e) { this.fetchCards(); } },
}));

// --- Printers Manager Component ---
Alpine.data('printersManager', () => ({
    printers: [], loading: true, pagination: {}, error: '',
    showModal: false, editing: false, saving: false,
    form: { printer_name: '', paper_width: 32, header: '', footer: '', feed_lines: 0, cut_paper: true, open_cash_drawer: true, printer_type: 0, number_of_copies: 1 },
    get gridStyle() {
        const w = this.$store.screen.width;
        const cols = this.posSettings.grid_columns || 4;
        if (w < 640) return `grid-template-columns: repeat(2, minmax(0, 1fr))`;
        if (w < 768) return `grid-template-columns: repeat(3, minmax(0, 1fr))`;
        return `grid-template-columns: repeat(${cols}, minmax(0, 1fr))`;
    },

    async init() { await this.fetchPrinters(); },
    async fetchPrinters(page = 1) {
        this.loading = true;
        try { const r = await window.POS.api('/api/printers?page=' + page); this.printers = r.data?.data || r.data || []; this.pagination = r.meta || r.data?.meta || { current_page: 1, last_page: 1, total: 0 }; } catch (e) { this.printers = []; } finally { this.loading = false; }
    },
    openAdd() { this.editing = false; this.form = { printer_name: '', paper_width: 32, header: '', footer: '', feed_lines: 0, cut_paper: true, open_cash_drawer: true, printer_type: 0, number_of_copies: 1 }; this.showModal = true; },
    openEdit(p) { this.editing = true; this.form = { ...p }; this.showModal = true; },
    async save() {
        this.saving = true;
        try {
            const method = this.editing ? 'PUT' : 'POST', url = this.editing ? '/api/printers/' + this.form.id : '/api/printers';
            await window.POS.api(url, { method, body: JSON.stringify(this.form) });
            this.showModal = false; this.fetchPrinters();
        } catch (e) { alert(e.message); } finally { this.saving = false; }
    },
    async deletePrinter(id) { if (!confirm('Delete this printer?')) return; try { await window.POS.api('/api/printers/' + id, { method: 'DELETE' }); this.fetchPrinters(); } catch (e) { alert(e.message); } },
    async testPrint(id) {
        this.error = '';
        try { await window.POS.api('/api/printers/' + id + '/test', { method: 'POST' }); alert('Test print sent'); }
        catch (e) { this.error = e.message || 'Test print failed'; }
    },
}));

// Branches Manager
Alpine.data('branchesManager', () => ({
    branches: [], loading: true, pagination: {},
    showModal: false, editing: false, saving: false,
    uniqueBusinessTypes: [],
    form: { name: '', branch_code: '', business_type: 'Retail', address: '', phone: '', is_headquarters: false },
    get gridStyle() {
        const w = this.$store.screen.width;
        const cols = this.posSettings.grid_columns || 4;
        if (w < 640) return `grid-template-columns: repeat(2, minmax(0, 1fr))`;
        if (w < 768) return `grid-template-columns: repeat(3, minmax(0, 1fr))`;
        return `grid-template-columns: repeat(${cols}, minmax(0, 1fr))`;
    },

    async init() { await this.fetchBranches(); },
    async fetchBranches() {
        this.loading = true;
        try { const r = await window.POS.api('/api/branches'); this.branches = Array.isArray(r?.data) ? r.data : (r?.data?.data || []); this.uniqueBusinessTypes = [...new Set(this.branches.map(b => b.business_type).filter(Boolean))]; } catch (e) { this.branches = []; } finally { this.loading = false; }
    },
    openAdd() { this.editing = false; this.form = { name: '', branch_code: '', business_type: 'Retail', address: '', phone: '', is_headquarters: false }; this.showModal = true; },
    openEdit(b) { this.editing = true; this.form = { ...b }; this.showModal = true; },
    async save() {
        this.saving = true;
        try {
            const method = this.editing ? 'PUT' : 'POST';
            const url = this.editing ? '/api/branches/' + this.form.id : '/api/branches';
            await window.POS.api(url, { method, body: JSON.stringify(this.form) });
            this.showModal = false; this.fetchBranches();
        } catch (e) { alert(e.message); } finally { this.saving = false; }
    },
    async deleteBranch(id) { if (!confirm('Delete this branch?')) return; try { await window.POS.api('/api/branches/' + id, { method: 'DELETE' }); this.showModal = false; this.fetchBranches(); } catch (e) { alert(e.message); } },
}));

// --- Inventory Manager Component ---
Alpine.data('inventoryManager', () => ({
    warehouses: [], loading: true, selectedWarehouse: null, selectedWarehouseName: '',
    warehouseStocks: [], stockLoading: false, adjustQty: {}, uploadingStock: false,
    stockPage: 1, stockLastPage: 1, stockTotal: 0,
    showWarehouseModal: false, warehouseSaving: false, editingWarehouseId: null,
    warehouseForm: { name: '', is_default: false },
    allProducts: [], addProductId: '', addProductQty: 1,
    get gridStyle() {
        const w = this.$store.screen.width;
        const cols = this.posSettings.grid_columns || 4;
        if (w < 640) return `grid-template-columns: repeat(2, minmax(0, 1fr))`;
        if (w < 768) return `grid-template-columns: repeat(3, minmax(0, 1fr))`;
        return `grid-template-columns: repeat(${cols}, minmax(0, 1fr))`;
    },

    async init() { await Promise.all([this.fetchWarehouses(), this.fetchAllProducts()]); },
    async fetchWarehouses() {
        this.loading = true;
        try { const r = await window.POS.api('/api/warehouses'); this.warehouses = r?.data?.data || r?.data || []; } catch (e) { this.warehouses = []; }
        finally { this.loading = false; }
    },
    async fetchAllProducts() {
        try { const r = await window.POS.api('/api/products?per_page=200'); this.allProducts = r?.data?.data || r?.data || []; } catch(e) { this.allProducts = []; }
    },
    async addStockToWarehouse() {
        if (!this.addProductId || !this.addProductQty || !this.selectedWarehouse) return;
        try {
            await window.POS.api('/api/stock/adjust', {
                method: 'POST',
                body: JSON.stringify({ product_id: this.addProductId, warehouse_id: this.selectedWarehouse, new_quantity: parseFloat(this.addProductQty), note: 'Initial stock setup' })
            });
            this.addProductId = ''; this.addProductQty = 1;
            await this.selectWarehouse(this.selectedWarehouse);
        } catch(e) { alert('Failed to add stock'); }
    },
    async selectWarehouse(id) {
        this.selectedWarehouse = id;
        const w = this.warehouses.find(x => x.id === id);
        this.selectedWarehouseName = w?.name || '';
        await this.loadStockPage(1);
    },
    async loadStockPage(page) {
        this.stockLoading = true;
        try {
            const r = await window.POS.api('/api/stock?warehouse_id=' + this.selectedWarehouse + '&per_page=50&page=' + page);
            const body = r?.data;
            const items = Array.isArray(body) ? body : (body?.data || []);
            this.warehouseStocks = items.map(s => ({
                product_id: s.product_id,
                product_name: s.product ? s.product.name : '\u2014',
                product_code: s.product ? s.product.code : '',
                quantity: parseFloat(s.quantity) || 0,
                branch_summary: '',
            }));
            this.stockPage = body && body.current_page ? body.current_page : page;
            this.stockLastPage = body && body.last_page ? body.last_page : 1;
            this.stockTotal = body && body.total ? body.total : this.warehouseStocks.length;
            for (const s of this.warehouseStocks) {
                try {
                    const br = await window.POS.api('/api/stock/pos-summary?product_ids=' + s.product_id);
                    const list = Array.isArray(br?.data) ? br.data : [];
                    s.branch_summary = list.map(b => (b.branch_name || '?').substring(0,3) + ':' + b.current_stock).join(', ');
                } catch(e) {}
            }
        } catch(e) { this.warehouseStocks = []; }
        finally { this.stockLoading = false; }
    },
    async quickAdjust(productId) {
        const qty = parseFloat(this.adjustQty[productId]);
        if (isNaN(qty) || qty < 0) return;
        try {
            const product = this.warehouseStocks.find(s => s.product_id === productId);
            await window.POS.api('/api/stock/adjust', {
                method: 'POST',
                body: JSON.stringify({ product_id: productId, warehouse_id: this.selectedWarehouse, new_quantity: qty, note: 'Manual adjustment from Inventory page' })
            });
            if (product) product.quantity = qty;
            this.adjustQty[productId] = null;
        } catch(e) { alert('Adjustment failed'); }
    },
    openAddWarehouse() { this.editingWarehouseId = null; this.warehouseForm = { name: '', is_default: false }; this.showWarehouseModal = true; },
    openEditWarehouse(w) { this.editingWarehouseId = w.id; this.warehouseForm = { name: w.name, is_default: w.is_default }; this.showWarehouseModal = true; },
    async saveWarehouse() {
        if (!this.warehouseForm.name) return;
        this.warehouseSaving = true;
        try {
            const method = this.editingWarehouseId ? 'PUT' : 'POST';
            const url = '/api/warehouses' + (this.editingWarehouseId ? '/' + this.editingWarehouseId : '');
            await window.POS.api(url, { method, body: JSON.stringify(this.warehouseForm) });
            this.showWarehouseModal = false;
            this.editingWarehouseId = null;
            await this.fetchWarehouses();
        } catch(e) { alert('Failed to save warehouse'); }
        finally { this.warehouseSaving = false; }
    },
    async deleteWarehouse(id) {
        if (!confirm('Delete this warehouse?')) return;
        try {
            await window.POS.api('/api/warehouses/' + id, { method: 'DELETE' });
            if (this.selectedWarehouse === id) this.selectedWarehouse = null;
            await this.fetchWarehouses();
        } catch(e) {
            if (e.message && e.message.includes('stock records')) {
                if (!confirm(e.message + '\n\nForce delete? ALL stock and documents will be removed.')) return;
                try {
                    await window.POS.api('/api/warehouses/' + id + '?force=1', { method: 'DELETE' });
                    if (this.selectedWarehouse === id) this.selectedWarehouse = null;
                    await this.fetchWarehouses();
                } catch(e2) { alert(e2.message || 'Cannot delete this warehouse'); }
            } else {
                alert(e.message || 'Cannot delete this warehouse');
            }
        }
    },
    async handleStockUpload(event) {
        const file = event.target.files[0]; if (!file) return;
        this.uploadingStock = true;
        try {
            const text = await file.text();
            const lines = text.split('\n').filter(l => l.trim()); if (lines.length < 2) return;
            const h = lines[0].split(',').map(c => c.trim().toLowerCase().replace(/"/g, ''));
            const ci = h.indexOf('code') >= 0 ? h.indexOf('code') : h.indexOf('sku'), qi = h.indexOf('stock') >= 0 ? h.indexOf('stock') : h.indexOf('quantity');
            if (ci < 0 || qi < 0) return;
            const items = lines.slice(1).map(l => { const c = l.split(',').map(v => v.trim().replace(/"/g, '')); return ci >= 0 && c[ci] ? { product_code: c[ci], quantity: parseFloat(c[qi]) || 0 } : null; }).filter(Boolean);
            if (!items.length) return;
            await window.POS.api('/api/stock/bulk-update', { method: 'POST', body: JSON.stringify({ items, warehouse_id: this.selectedWarehouse }) });
            if (this.selectedWarehouse) await this.selectWarehouse(this.selectedWarehouse);
        } catch(e) {}
        finally { this.uploadingStock = false; event.target.value = ''; }
    },
}));

// --- Activity Log Manager Component ---
Alpine.data('activityManager', () => ({
    logs: [], loading: true, pagination: {},
    activeTab: 'all',
    filterModule: '', filterEvent: '', filterUser: '', filterBranch: '',
    filterDateFrom: '', filterDateTo: '', filterSearch: '',
    selectedLog: null, showDetail: false,
    users: [], branches: [],
    tabs: [
        { key: 'all', label: 'All Activities', modules: '' },
        { key: 'security', label: 'Login & Security', modules: 'Security' },
        { key: 'sales', label: 'Sales Activities', modules: 'POS,Orders' },
        { key: 'purchase', label: 'Purchase Activities', modules: 'Purchases' },
        { key: 'inventory', label: 'Inventory Activities', modules: 'Inventory,Stock,Warehouses' },
        { key: 'customer', label: 'Customer Activities', modules: 'Customers' },
        { key: 'product', label: 'Product Activities', modules: 'Products,Barcodes' },
        { key: 'system', label: 'System Activities', modules: 'Users,Roles,Settings,Branches,Taxes,Printers,Fiscal,Shifts,Payment Types,Cash Register' },
    ],
    get gridStyle() {
        const w = this.$store.screen.width;
        const cols = this.posSettings.grid_columns || 4;
        if (w < 640) return `grid-template-columns: repeat(2, minmax(0, 1fr))`;
        if (w < 768) return `grid-template-columns: repeat(3, minmax(0, 1fr))`;
        return `grid-template-columns: repeat(${cols}, minmax(0, 1fr))`;
    },

    async init() {
        await Promise.all([this.fetchLogs(), this.fetchUsers(), this.fetchBranches()]);
    },
    async fetchUsers() {
        try { const r = await window.POS.api('/api/users'); this.users = r?.data?.data || r?.data || []; } catch(e) { this.users = []; }
    },
    async fetchBranches() {
        try { const r = await window.POS.api('/api/branches'); this.branches = r?.data?.data || r?.data || []; } catch(e) { this.branches = []; }
    },
    switchTab(key) { this.activeTab = key; this.fetchLogs(); },
    async fetchLogs(page = 1) {
        this.loading = true;
        try {
            const params = new URLSearchParams({ page: String(page), per_page: '50' });
            const tab = this.tabs.find(t => t.key === this.activeTab);
            if (tab && tab.modules) params.set('modules', tab.modules);
            if (this.filterModule) params.set('module', this.filterModule);
            if (this.filterEvent) params.set('event', this.filterEvent);
            if (this.filterUser) params.set('user_id', this.filterUser);
            if (this.filterBranch) params.set('branch_id', this.filterBranch);
            if (this.filterDateFrom) params.set('date_from', this.filterDateFrom);
            if (this.filterDateTo) params.set('date_to', this.filterDateTo);
            if (this.filterSearch) params.set('search', this.filterSearch);
            const r = await window.POS.api('/api/activity?' + params.toString());
            this.logs = r?.data?.data || r?.data || [];
            this.pagination = { current_page: r?.data?.current_page || 1, last_page: r?.data?.last_page || 1 };
        } catch(e) { this.logs = []; }
        finally { this.loading = false; }
    },
    viewDetail(log) { this.selectedLog = log; this.showDetail = true; },
    eventLabel(event) {
        if (!event) return '—';
        return event.split('_').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ');
    },
}));

// --- Purchases Manager ---
Alpine.data('purchasesManager', () => ({
    activeTab: 'report-dashboard',
    // Suppliers
    suppliers: [], suppliersLoading: false, supplierSearch: '', supplierForm: {name:'',code:'',phone_number:'',email:'',tax_number:'',city:'',address:'',is_enabled:true},
    showSupplierModal: false, supplierEditing: false, supplierEditId: null, supplierError: '', supplierSaving: false,
    // Purchases
    purchases: [], purchasesLoading: false, purchasesPage: {}, purchaseSearch: '', purchaseStatusFilter: '',
    showPurchaseModal: false, purchaseSaving: false, purchaseError: '',
    showPaymentModal: false, showSupplierStatementModal: false,
    paymentForm: { amount: '', payment_method: 'cash', note: '' }, paymentSaving: false, paymentPurchase: null,
    supplierStatement: null, supplierStatementLoading: false,
    purchaseForm: {supplier_id:'',warehouse_id:'',purchase_date:new Date().toISOString().split('T')[0],reference_number:'',discount:0,discount_type:0,shipping_cost:0,status:'pending',items:[{product_id:'',quantity:1,unit_cost:0,tax_id:null,discount:0,discount_type:0}]},
    supplierList: [], productList: [], warehouseList: [],
    // Returns
    returns: [], returnsLoading: false, showReturnModal: false, returnSaving: false,
    returnError: '', returnForm: {purchase_id:'',return_date:new Date().toISOString().split('T')[0],reason:''},
    returnablePurchases: [], returnItems: [],
    // Receive
    showReceiveModal: false, receiveItems: [], receivePurchaseId: null, receiveSaving: false, receiveError: '',
    // Reports
    reportData: {}, reportSuppliers: [], reportSuppliersLoading: false,
    reportProducts: [], reportProductsLoading: false,
    reportMonthly: [], reportMonthlyLoading: false,
    reportOutstanding: [], reportOutstandingLoading: false,

    get purchaseSubtotal() {
        return this.purchaseForm.items.reduce((s,i) => s + ((i.quantity||0) * (i.unit_cost||0)), 0);
    },
    get purchaseGrandTotal() {
        let sub = this.purchaseSubtotal;
        let disc =         this.purchaseForm.discount || 0;
        if (this.purchaseForm.discount_type == 0) disc = sub * disc / 100;
        return Math.max(0, sub - disc + (this.purchaseForm.shipping_cost||0));
    },
    formatMoney(amount) {
        return window.POS.formatCurrency(amount);
    },

    async init() {
        await Promise.all([this.fetchSuppliers(), this.fetchSupplierList(), this.fetchProductList(), this.fetchWarehouseList()]);
        this.fetchReportSummary();
    },

    switchTab(tab) {
        this.activeTab = tab;
        if (tab === 'purchases') { this.fetchPurchases(); this.fetchSupplierList(); this.fetchProductList(); }
        if (tab === 'returns') this.fetchReturns();
        if (tab === 'report-dashboard') this.fetchReportSummary();
        if (tab === 'report-suppliers') this.fetchReportBySupplier();
        if (tab === 'report-products') this.fetchReportByProduct();
        if (tab === 'report-monthly') this.fetchReportMonthly();
        if (tab === 'report-outstanding') this.fetchReportOutstanding();
    },

    // --- SUPPLIERS ---
    async fetchSuppliers() {
        this.suppliersLoading = true;
        try { const params = new URLSearchParams(); if (this.supplierSearch) params.set('search', this.supplierSearch); const res = await fetch('/api/suppliers?'+params.toString(),{headers:{'Authorization':`Bearer ${localStorage.getItem('auth_token')}`,'Accept':'application/json'}}); const d = await res.json(); this.suppliers = d.data?.data || d.data || []; } catch(e) { this.suppliers = []; } finally { this.suppliersLoading = false; }
    },
    openSupplierForm(s) {
        this.supplierError = ''; this.showSupplierModal = true;
        if (s) { this.supplierEditing = true; this.supplierEditId = s.id; this.supplierForm = {name: s.name||'', code: s.code||'', phone_number: s.phone_number||'', email: s.email||'', tax_number: s.tax_number||'', city: s.city||'', address: s.address||'', is_enabled: s.is_enabled!==false}; }
        else { this.supplierEditing = false; this.supplierEditId = null; this.supplierForm = {name:'', code:'', phone_number:'', email:'', tax_number:'', city:'', address:'', is_enabled:true}; }
    },
    editSupplier(s) { this.openSupplierForm(s); },
    async saveSupplier() {
        this.supplierSaving = true; this.supplierError = '';
        try {
            const m = this.supplierEditing ? 'PUT' : 'POST';
            const u = this.supplierEditing ? '/api/suppliers/' + this.supplierEditId : '/api/suppliers';
            const payload = {...this.supplierForm};
            if (!this.supplierEditing) delete payload.code;
            const res = await fetch(u,{method:m,headers:{'Content-Type':'application/json','Accept':'application/json','Authorization':`Bearer ${localStorage.getItem('auth_token')}`, 'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').getAttribute('content')},body:JSON.stringify(payload)});
            if (!res.ok) { const e = await res.json(); throw new Error(e.message||'Failed'); }
            this.showSupplierModal = false; this.fetchSuppliers(); this.fetchSupplierList();
        } catch(e) { this.supplierError = e.message; } finally { this.supplierSaving = false; }
    },
    async deleteSupplier(id) { if(!confirm('Delete?'))return; try { const res = await fetch('/api/suppliers/'+id,{method:'DELETE',headers:{'Authorization':`Bearer ${localStorage.getItem('auth_token')}`,'Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').getAttribute('content')}}); if(!res.ok){const e=await res.json();throw new Error(e.message||'Failed');} this.fetchSuppliers(); this.fetchSupplierList(); } catch(e) { alert(e.message); } },
    async fetchSupplierList() {
        try { const res = await fetch('/api/suppliers/quick-list',{headers:{'Authorization':`Bearer ${localStorage.getItem('auth_token')}`,'Accept':'application/json'}}); const d = await res.json(); this.supplierList = d.data || []; } catch(e) { this.supplierList = []; }
    },
    async fetchProductList() {
        try { const res = await fetch('/api/products?per_page=200',{headers:{'Authorization':`Bearer ${localStorage.getItem('auth_token')}`,'Accept':'application/json'}}); const d = await res.json(); this.productList = d.data?.data || d.data || []; } catch(e) { this.productList = []; }
    },
    async fetchWarehouseList() {
        try { const res = await fetch('/api/warehouses',{headers:{'Authorization':`Bearer ${localStorage.getItem('auth_token')}`,'Accept':'application/json'}}); const d = await res.json(); this.warehouseList = d.data?.data || d.data || []; } catch(e) { this.warehouseList = []; }
    },

    // --- PURCHASES ---
    async fetchPurchases(page = 1) {
        this.purchasesLoading = true;
        try {
            const params = new URLSearchParams({page, per_page: 20});
            if (this.purchaseSearch) params.set('search', this.purchaseSearch);
            if (this.purchaseStatusFilter) params.set('status', this.purchaseStatusFilter);
            const res = await fetch('/api/purchases?'+params.toString(),{headers:{'Authorization':`Bearer ${localStorage.getItem('auth_token')}`,'Accept':'application/json'}});
            const d = await res.json();
            this.purchases = d.data || [];
            this.purchasesPage = { current_page: d.current_page || 1, last_page: d.last_page || 1 };
        } catch(e) { this.purchases = []; } finally { this.purchasesLoading = false; }
    },
    openPurchaseForm() {
        this.purchaseError = '';
        this.purchaseForm = {supplier_id:'',warehouse_id:'',purchase_date:new Date().toISOString().split('T')[0],reference_number:'',discount:0,discount_type:0,shipping_cost:0,status:'pending',items:[{product_id:'',quantity:1,unit_cost:0,tax_id:null,discount:0,discount_type:0}]};
        this.showPurchaseModal = true;
    },
    onPurchaseItemProductChange(i) {
        const item = this.purchaseForm.items[i];
        const product = this.productList.find(p => p.id === item.product_id);
        if (product && product.cost) item.unit_cost = parseFloat(product.cost);
        this.calcPurchaseTotals();
    },
    calcPurchaseTotals() { /* getters recalculate automatically */ },
    async savePurchase() {
        this.purchaseSaving = true; this.purchaseError = '';
        try {
            const validItems = this.purchaseForm.items.filter(i => i.product_id && i.quantity > 0);
            if (validItems.length === 0) { this.purchaseError = 'Add at least one item'; this.purchaseSaving = false; return; }
            const payload = {...this.purchaseForm, items: validItems};
            const res = await fetch('/api/purchases',{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json','Authorization':`Bearer ${localStorage.getItem('auth_token')}`,'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').getAttribute('content')},body:JSON.stringify(payload)});
            if (!res.ok) { const e = await res.json(); throw new Error(e.message||'Failed'); }
            this.showPurchaseModal = false; this.fetchPurchases();
        } catch(e) { this.purchaseError = e.message; } finally { this.purchaseSaving = false; }
    },
    viewPurchase(po) { alert('Purchase #' + po.purchase_number + '\nSupplier: ' + (po.supplier?.name||'N/A') + '\nTotal: ' + (Alpine.store('currency')?.symbol || '$') + Number(po.grand_total).toFixed(2)); },
    async receivePurchase(po) {
        this.receiveItems = [];
        try {
            const res = await fetch('/api/purchases/'+po.id,{headers:{'Authorization':`Bearer ${localStorage.getItem('auth_token')}`,'Accept':'application/json'}});
            const d = await res.json();
            const purchase = d.data || d;
            const items = purchase.items || [];
            this.receiveItems = items.map(i => ({...i, receive_qty: Math.max(0, Number(i.quantity || 0) - Number(i.received_quantity || 0))}));
            this.receivePurchaseId = po.id; this.receiveError = ''; this.showReceiveModal = true;
        } catch(e) { alert(e.message || 'Failed to load purchase details'); }
    },
    async confirmReceive() {
        this.receiveSaving = true; this.receiveError = '';
        try {
            const items = this.receiveItems.filter(i => i.receive_qty > 0).map(i => ({item_id: i.id, quantity: i.receive_qty}));
            if (items.length === 0) { this.receiveError = 'Enter quantities to receive'; this.receiveSaving = false; return; }
            const res = await fetch('/api/purchases/'+this.receivePurchaseId+'/receive',{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json','Authorization':`Bearer ${localStorage.getItem('auth_token')}`,'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').getAttribute('content')},body:JSON.stringify({items})});
            if (!res.ok) { const e = await res.json(); throw new Error(e.message||'Failed'); }
            this.showReceiveModal = false; this.fetchPurchases();
        } catch(e) { this.receiveError = e.message; } finally { this.receiveSaving = false; }
    },
    async cancelPurchase(id) { if(!confirm('Cancel this purchase?'))return; try { const res = await fetch('/api/purchases/'+id,{method:'DELETE',headers:{'Authorization':`Bearer ${localStorage.getItem('auth_token')}`,'Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').getAttribute('content')}}); if(!res.ok){const e=await res.json();throw new Error(e.message||'Failed');} this.fetchPurchases(); } catch(e) { alert(e.message); } },
    async markPaid(id) { if(!confirm('Mark this purchase as fully paid?'))return; try { const res = await fetch('/api/purchases/'+id+'/mark-paid',{method:'POST',headers:{'Authorization':`Bearer ${localStorage.getItem('auth_token')}`,'Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').getAttribute('content')}}); if(!res.ok){const e=await res.json();throw new Error(e.message||'Failed');} this.fetchPurchases(); } catch(e) { alert(e.message); } },
    openPayment(purchase) {
        this.paymentPurchase = purchase;
        this.paymentForm = { amount: purchase.due_amount || '', payment_method: 'cash', note: '' };
        this.showPaymentModal = true;
    },
    async savePayment() {
        const amount = parseFloat(this.paymentForm.amount);
        if (!amount || amount <= 0) { alert('Enter a valid amount.'); return; }
        this.paymentSaving = true;
        try {
            await window.POS.api('/api/purchases/' + this.paymentPurchase.id + '/payment', {
                method: 'POST',
                body: JSON.stringify({ amount: amount, payment_method: this.paymentForm.payment_method, note: this.paymentForm.note }),
            });
            this.showPaymentModal = false;
            this.fetchPurchases();
            this.fetchReportSummary();
            this.toastMsg('Payment recorded', 'success');
        } catch (e) { alert(e.message || 'Failed to record payment'); }
        finally { this.paymentSaving = false; }
    },
    async openSupplierStatement(supplierId) {
        this.showSupplierStatementModal = true;
        this.supplierStatementLoading = true;
        this.supplierStatement = null;
        try {
            const r = await window.POS.api('/api/suppliers/' + supplierId + '/statement');
            this.supplierStatement = r?.data || null;
        } catch (e) { this.supplierStatement = null; }
        finally { this.supplierStatementLoading = false; }
    },
    // --- RETURNS ---
    async openReturnForm() {
        this.showPurchaseModal = false; this.showReceiveModal = false;
        this.returnError = ''; this.returnForm = {purchase_id:'',return_date:new Date().toISOString().split('T')[0],reason:''};
        this.returnItems = [];
        try {
            const res = await fetch('/api/purchases?per_page=50&status=received',{headers:{'Authorization':`Bearer ${localStorage.getItem('auth_token')}`,'Accept':'application/json'}});
            const d = await res.json();
            const all = d.data || [];
            const res2 = await fetch('/api/purchases?per_page=50&status=partially_received',{headers:{'Authorization':`Bearer ${localStorage.getItem('auth_token')}`,'Accept':'application/json'}});
            const d2 = await res2.json();
            this.returnablePurchases = [...all, ...(d2.data || [])];
        } catch(e) { this.returnablePurchases = []; }
        this.showReturnModal = true;
    },
    async loadReturnItems() {
        if (!this.returnForm.purchase_id) { this.returnItems = []; return; }
        try {
            const res = await fetch('/api/purchases/'+this.returnForm.purchase_id,{headers:{'Authorization':`Bearer ${localStorage.getItem('auth_token')}`,'Accept':'application/json'}});
            const d = await res.json();
            const p = d.data || d;
            this.returnItems = (p.items || []).filter(i => Number(i.received_quantity) > 0).map(i => ({...i, return_qty: 0}));
        } catch(e) { this.returnItems = []; }
    },
    async saveReturn() {
        if (!this.returnForm.purchase_id) { this.returnError = 'Select a purchase'; return; }
        const itemsToReturn = this.returnItems.filter(i => i.return_qty > 0).map(i => ({purchase_item_id:i.id,product_id:i.product_id,quantity:i.return_qty,unit_cost:i.unit_cost}));
        if (itemsToReturn.length === 0) { this.returnError = 'Enter return quantities'; return; }
        this.returnSaving = true; this.returnError = '';
        try {
            const res = await fetch('/api/purchase-returns',{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json','Authorization':`Bearer ${localStorage.getItem('auth_token')}`,'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').getAttribute('content')},body:JSON.stringify({purchase_id:this.returnForm.purchase_id,return_date:this.returnForm.return_date,reason:this.returnForm.reason,items:itemsToReturn})});
            if (!res.ok) { const e = await res.json(); throw new Error(e.message||'Failed'); }
            this.showReturnModal = false; this.fetchPurchases(); this.fetchReturns();
        } catch(e) { this.returnError = e.message; } finally { this.returnSaving = false; }
    },
    async fetchReturns() { this.returnsLoading = true; try { const res = await fetch('/api/purchase-returns',{headers:{'Authorization':`Bearer ${localStorage.getItem('auth_token')}`,'Accept':'application/json'}}); const d = await res.json(); this.returns = d.data?.data || d.data || []; } catch(e) { this.returns = []; } finally { this.returnsLoading = false; } },

    // --- REPORTS ---
    async fetchReportSummary() { try { const res = await fetch('/api/reports/purchases/summary',{headers:{'Authorization':`Bearer ${localStorage.getItem('auth_token')}`,'Accept':'application/json'}}); const d = await res.json(); this.reportData = d.data || d; } catch(e) { this.reportData = {}; } },
    async fetchReportBySupplier() { this.reportSuppliersLoading = true; try { const res = await fetch('/api/reports/purchases/by-supplier',{headers:{'Authorization':`Bearer ${localStorage.getItem('auth_token')}`,'Accept':'application/json'}}); const d = await res.json(); this.reportSuppliers = d.data || []; } catch(e) { this.reportSuppliers = []; } finally { this.reportSuppliersLoading = false; } },
    async fetchReportByProduct() { this.reportProductsLoading = true; try { const res = await fetch('/api/reports/purchases/by-product',{headers:{'Authorization':`Bearer ${localStorage.getItem('auth_token')}`,'Accept':'application/json'}}); const d = await res.json(); this.reportProducts = d.data || []; } catch(e) { this.reportProducts = []; } finally { this.reportProductsLoading = false; } },
    async fetchReportMonthly() { this.reportMonthlyLoading = true; try { const res = await fetch('/api/reports/purchases/monthly',{headers:{'Authorization':`Bearer ${localStorage.getItem('auth_token')}`,'Accept':'application/json'}}); const d = await res.json(); this.reportMonthly = d.data || []; } catch(e) { this.reportMonthly = []; } finally { this.reportMonthlyLoading = false; } },
    async fetchReportOutstanding() { this.reportOutstandingLoading = true; try { const res = await fetch('/api/reports/purchases/outstanding-payments',{headers:{'Authorization':`Bearer ${localStorage.getItem('auth_token')}`,'Accept':'application/json'}}); const d = await res.json(); this.reportOutstanding = d.data || []; } catch(e) { this.reportOutstanding = []; } finally { this.reportOutstandingLoading = false; } },
}));

// --- Income & Expenses Manager ---
Alpine.data('incomeExpensesManager', () => ({
    activeTab: 'dashboard',
    showEntryModal: false, showCategoryModal: false,
    editing: false, editId: null, entrySaving: false, entryError: '',
    categoryEditing: false, categoryEditId: null, categorySaving: false, categoryError: '',
    syncing: false,

    // Sync modal
    showSyncModal: false, syncDateFrom: '', syncDateTo: '', syncError: '',

    // Dashboard
    dashboard: { total_income: 0, total_expense: 0, total_count: 0, top_income_categories: [], top_expense_categories: [], recent_entries: [] },
    monthlyData: [], chartLoading: false, chartMax: 1,

    // Entries
    entries: [], entriesLoading: false, entriesPage: { current_page: 1, last_page: 1 },
    filterSearch: '', filterCategoryId: '', filterDateFrom: '', filterDateTo: '',

    // Categories
    categories: { income: [], expense: [] },

    // Payment methods
    paymentMethods: [],

    // Reports
    reportDateFrom: '', reportDateTo: '',
    reportData: { summary: {}, top_income_categories: [], top_expense_categories: [] },

    // Forms
    entryForm: { type: 'expense', category_id: '', amount: '', description: '', payment_method: '', date: new Date().toISOString().split('T')[0] },
    categoryForm: { type: 'income', name: '', color: '#10b981' },

    async init() {
        await this.fetchCategories();
        this.fetchPaymentTypes();
        this.fetchDashboard();
        this.fetchMonthlyChart();
    },

    switchTab(tab) {
        this.activeTab = tab;
        if (tab === 'dashboard') { this.fetchDashboard(); this.fetchMonthlyChart(); }
        if (tab === 'income') { this.filterDateFrom = ''; this.filterDateTo = ''; this.filterCategoryId = ''; this.fetchEntries('income'); }
        if (tab === 'expenses') { this.filterDateFrom = ''; this.filterDateTo = ''; this.filterCategoryId = ''; this.fetchEntries('expense'); }
        if (tab === 'categories') { this.fetchCategories(); }
        if (tab === 'reports') { this.fetchReports(); }
    },

    async fetchDashboard() {
        try {
            const d = await window.POS.api('/api/reports/income-expenses/summary?' + new URLSearchParams({
                date_from: this.reportDateFrom || new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().split('T')[0],
                date_to: this.reportDateTo || new Date().toISOString().split('T')[0],
            }));
            this.dashboard = d.data || d;
        } catch(e) { /* ignore */ }
    },

    async fetchMonthlyChart() {
        this.chartLoading = true;
        try {
            const d = await window.POS.api('/api/reports/income-expenses/monthly?year=' + new Date().getFullYear());
            const data = d.data || [];
            const months = [];
            for (let m = 1; m <= 12; m++) {
                const found = data.find(x => x.month === new Date().getFullYear() + '-' + String(m).padStart(2,'0'));
                months.push(found || { month: new Date().getFullYear() + '-' + String(m).padStart(2,'0'), income_total: 0, expense_total: 0 });
            }
        this.monthlyData = months;
        this.chartMax = Math.max(1, ...months.map(m => Number(m.income_total || 0) + Number(m.expense_total || 0)));
        } catch(e) { this.monthlyData = []; }
        finally { this.chartLoading = false; }
    },

    async fetchEntries(tab) {
        const type = (typeof tab === 'string' && (tab === 'income' || tab === 'expense')) ? tab : (this.activeTab === 'income' ? 'income' : 'expense');
        const page = (typeof tab === 'number') ? tab : this.entriesPage.current_page;
        this.entriesLoading = true;
        try {
            const params = new URLSearchParams();
            params.set('type', type);
            params.set('per_page', '20');
            params.set('page', String(page));
            if (this.filterSearch) params.set('search', this.filterSearch);
            if (this.filterCategoryId) params.set('category_id', this.filterCategoryId);
            if (this.filterDateFrom) params.set('date_from', this.filterDateFrom);
            if (this.filterDateTo) params.set('date_to', this.filterDateTo);
            const d = await window.POS.api('/api/income-expenses?' + params);
            this.entries = d.data || [];
            this.entriesPage = { current_page: d.current_page || 1, last_page: d.last_page || 1 };
        } catch(e) { this.entries = []; }
        finally { this.entriesLoading = false; }
    },

    async fetchCategories() {
        try {
            const inc = await window.POS.api('/api/income-expense-categories?type=income');
            const exp = await window.POS.api('/api/income-expense-categories?type=expense');
            this.categories.income = inc.data || [];
            this.categories.expense = exp.data || [];
        } catch(e) { this.categories = { income: [], expense: [] }; }
    },

    async fetchPaymentTypes() {
        try {
            const d = await window.POS.api('/api/payment-types/all');
            this.paymentMethods = d.data || [];
        } catch(e) { this.paymentMethods = []; }
    },

    async fetchReports() {
        try {
            const params = new URLSearchParams();
            if (this.reportDateFrom) params.set('date_from', this.reportDateFrom);
            if (this.reportDateTo) params.set('date_to', this.reportDateTo);
            const d = await window.POS.api('/api/reports/income-expenses/summary?' + params);
            this.reportData = d.data || d;
        } catch(e) { this.reportData = {}; }
    },

    // Entry CRUD
    openForm(type) {
        this.editing = false;
        this.editId = null;
        this.entryError = '';
        this.entryForm = { type: type, category_id: '', amount: '', description: '', payment_method: '', date: new Date().toISOString().split('T')[0] };
        this.showEntryModal = true;
    },

    editEntry(e) {
        this.editing = true;
        this.editId = e.id;
        this.entryError = '';
        this.entryForm = {
            type: e.type,
            category_id: e.category_id || '',
            amount: e.amount || '',
            description: e.description || '',
            payment_method: e.payment_method || '',
            date: e.date || new Date().toISOString().split('T')[0],
        };
        this.showEntryModal = true;
    },

    async saveEntry() {
        this.entrySaving = true; this.entryError = '';
        try {
            const method = this.editing ? 'PUT' : 'POST';
            const url = this.editing ? '/api/income-expenses/' + this.editId : '/api/income-expenses';
            await window.POS.api(url, {
                method,
                body: JSON.stringify(this.entryForm),
            });
            this.showEntryModal = false;
            this.fetchEntries(this.entryForm.type);
            if (this.activeTab === 'dashboard') { this.fetchDashboard(); this.fetchMonthlyChart(); }
        } catch(e) { this.entryError = e.message; }
        finally { this.entrySaving = false; }
    },

    async deleteEntry(id) {
        if(!confirm('Delete this entry?')) return;
        try {
            await window.POS.api('/api/income-expenses/' + id, { method: 'DELETE' });
            this.fetchEntries();
            if (this.activeTab === 'dashboard') { this.fetchDashboard(); this.fetchMonthlyChart(); }
        } catch(e) { alert(e.message); }
    },

    // Category CRUD
    openCategoryForm(type) {
        this.categoryEditing = false;
        this.categoryEditId = null;
        this.categoryError = '';
        this.categoryForm = { type: type, name: '', color: type === 'income' ? '#10b981' : '#ef4444' };
        this.showCategoryModal = true;
    },

    editCategory(c) {
        this.categoryEditing = true;
        this.categoryEditId = c.id;
        this.categoryError = '';
        this.categoryForm = { type: c.type, name: c.name, color: c.color || '#6b7280' };
        this.showCategoryModal = true;
    },

    async saveCategory() {
        this.categorySaving = true; this.categoryError = '';
        try {
            const method = this.categoryEditing ? 'PUT' : 'POST';
            const url = this.categoryEditing ? '/api/income-expense-categories/' + this.categoryEditId : '/api/income-expense-categories';
            await window.POS.api(url, {
                method,
                body: JSON.stringify(this.categoryForm),
            });
            this.showCategoryModal = false;
            this.fetchCategories();
        } catch(e) { this.categoryError = e.message; }
        finally { this.categorySaving = false; }
    },

    async deleteCategory(id) {
        if(!confirm('Delete this category?')) return;
        try {
            await window.POS.api('/api/income-expense-categories/' + id, { method: 'DELETE' });
            this.fetchCategories();
        } catch(e) { alert(e.message); }
    },

    // POS Sync
    openSyncModal() {
        const now = new Date();
        const today = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-${String(now.getDate()).padStart(2, '0')}`;
        this.syncDateFrom = today;
        this.syncDateTo = today;
        this.syncError = '';
        this.showSyncModal = true;
    },
    async syncPosSales() {
        this.syncing = true; this.syncError = '';
        try {
            const d = await window.POS.api('/api/income-expenses/sync-pos-sales', {
                method: 'POST',
                body: JSON.stringify({
                    date_from: this.syncDateFrom || undefined,
                    date_to: this.syncDateTo || undefined,
                }),
            });
            alert(d.message || 'Sync completed');
            this.showSyncModal = false;
            this.fetchEntries('income');
            this.fetchDashboard();
            this.fetchMonthlyChart();
        } catch(e) { alert(e.message); }
        finally { this.syncing = false; }
    },
}));

// --- Barcode Manager ---
Alpine.data('barcodeManager', () => ({
    barcodes: [], loading: false, page: { current_page: 1, last_page: 1 },
    search: '', filterType: '', filterStatus: 'all',
    products: [],

    showGenerateModal: false, showManualModal: false,
    editing: false, editId: null, saving: false, genSaving: false,
    printLoading: false,
    selectedIds: new Set(),
    get allSelected() { return this.barcodes.length > 0 && this.barcodes.every(b => this.selectedIds.has(b.id)); },
    genResult: null, genError: '', formError: '',

    genForm: { product_id: '', value: '', barcode_type: 'CODE_128', is_primary: true },
    genExistingBarcode: '',
    form: { product_id: '', value: '', barcode_type: 'CODE_128', is_primary: true },
    existingBarcode: '',

    async init() {
        await this.fetchBarcodes();
        await this.fetchProducts();
        this.fetchSettings();
    },

    async fetchBarcodes(p) {
        this.loading = true;
        try {
            const params = new URLSearchParams();
            params.set('per_page', '25');
            params.set('page', p || this.page.current_page);
            if (this.search) params.set('search', this.search);
            if (this.filterType) params.set('barcode_type', this.filterType);
            if (this.filterStatus !== 'all') params.set('is_enabled', this.filterStatus);
            const d = await window.POS.api('/api/barcodes?' + params);
            this.barcodes = d.data || [];
            this.page = { current_page: d.current_page || 1, last_page: d.last_page || 1 };
        } catch(e) { this.barcodes = []; }
        finally { this.loading = false; }
    },

    async fetchProducts() {
        try {
            const d = await window.POS.api('/api/products?per_page=200&include_barcodes=1');
            this.products = d.data?.data || d.data || [];
        } catch(e) { this.products = []; }
    },

    async fetchSettings() {
        try {
            const d = await window.POS.api('/api/settings');
            const s = d?.data || {};
            this.printSettings = {
                show_product_name: s.show_product_name === 'true' || s.show_product_name === true,
                show_price: s.show_price === 'true' || s.show_price === true,
                show_sku: s.show_sku === 'true' || s.show_sku === true,
                show_company_name: s.show_company_name === 'true' || s.show_company_name === true,
            };
            this.companyName = s.company_name || '';
        } catch(e) { /* ignore */ }
    },

    async openBulkModal() {
        this.showBulkModal = true;
        this.bulkSelected = new Set();
        this.bulkSelectAll = false;
        this.bulkProducts = [];
        try {
            const d = await window.POS.api('/api/barcodes/products-without?per_page=500');
            this.bulkProducts = d.data?.data || d.data || [];
        } catch(e) { this.bulkProducts = []; }
    },

    toggleBulkProduct(id) {
        if (this.bulkSelected.has(id)) this.bulkSelected.delete(id);
        else this.bulkSelected.add(id);
        this.bulkSelectAll = this.bulkProducts.length > 0 && this.bulkProducts.every(p => this.bulkSelected.has(p.id));
    },

    toggleBulkAll() {
        if (this.bulkSelectAll) { this.bulkSelected.clear(); }
        else { this.bulkProducts.forEach(p => this.bulkSelected.add(p.id)); }
        this.bulkSelectAll = !this.bulkSelectAll;
    },

    async generateAllBarcodes() {
        const productIds = Array.from(this.bulkSelected);
        if (!productIds.length) return;
        this.bulkGenerating = true;
        try {
            const d = await window.POS.api('/api/barcodes/bulk-generate', {
                method: 'POST',
                body: JSON.stringify({ product_ids: productIds, barcode_type: this.bulkType }),
            });
            const generated = d?.data || [];
            this.showBulkModal = false;
            this._bulkPrintItems = generated;
            await this.fetchBarcodes();
            if (generated.length > 0) {
                this.showPrintModal = true;
            }
        } catch(e) { alert(e.message); }
        finally { this.bulkGenerating = false; }
    },

    async fetchAllSelectedForPrint() {
        this.openPrintModal();
    },

    get printItems() {
        if (this._bulkPrintItems && this._bulkPrintItems.length > 0) {
            return this._bulkPrintItems;
        }
        return this.barcodes.filter(b => this.selectedIds.has(b.id));
    },

    openGenerateModal() { this.showGenerateModal = true; this.genError = ''; this.genForm = { product_id: '', value: '', barcode_type: 'CODE_128', is_primary: true }; this.genExistingBarcode = ''; },

    onGenProductSelect() {
        if (!this.genForm.product_id) { this.genExistingBarcode = ''; this.genForm.value = ''; return; }
        const product = this.products.find(p => p.id === this.genForm.product_id);
        if (product) {
            const primary = product.barcodes?.find(b => b.is_primary) || product.barcodes?.[0];
            if (primary?.value) {
                this.genForm.value = primary.value;
                this.genForm.barcode_type = primary.barcode_type || 'CODE_128';
                this.genExistingBarcode = primary.value;
            } else {
                this.genForm.value = '';
                this.genExistingBarcode = '';
            }
        }
    },

    async generateBarcodeValue() {
        this.genSaving = true; this.genError = '';
        try {
            const d = await window.POS.api('/api/barcodes/generate', {
                method: 'POST',
                body: JSON.stringify({ barcode_type: this.genForm.barcode_type || 'CODE_128' }),
            });
            if (d?.data?.value) {
                this.genForm.value = d.data.value;
                this.genExistingBarcode = '';
            }
        } catch(e) { this.genError = e.message; }
        finally { this.genSaving = false; }
    },

    async saveGenBarcode() {
        if (!this.genForm.product_id) { this.genError = 'Please select a product.'; return; }
        if (!this.genForm.value.trim()) { this.genError = 'Please generate or enter a barcode value.'; return; }
        this.genSaving = true; this.genError = '';
        try {
            await window.POS.api('/api/barcodes', {
                method: 'POST',
                body: JSON.stringify({
                    product_id: this.genForm.product_id,
                    value: this.genForm.value,
                    barcode_type: this.genForm.barcode_type,
                    is_primary: this.genForm.is_primary,
                }),
            });
            this.showGenerateModal = false;
            this.fetchBarcodes();
        } catch(e) { this.genError = e.message; }
        finally { this.genSaving = false; }
    },

    openManualModal() {
        this.editing = false; this.editId = null; this.formError = '';
        this.form = { product_id: '', value: '', barcode_type: 'CODE_128', is_primary: true };
        this.existingBarcode = '';
        this.showManualModal = true;
    },

    onProductSelect() {
        if (!this.form.product_id || this.editing) return;
        const product = this.products.find(p => p.id === this.form.product_id);
        if (product) {
            this.populateProductBarcode(product);
            return;
        }
        this.fetchProductBarcode(this.form.product_id);
    },

    populateProductBarcode(product) {
        if (!product) return;
        const primary = product.barcodes?.find(b => b.is_primary) || product.barcodes?.[0];
        if (primary?.value) {
            this.form.value = primary.value;
            this.form.barcode_type = primary.barcode_type || 'CODE_128';
            this.existingBarcode = primary.value;
        } else {
            this.form.value = '';
            this.existingBarcode = '';
        }
    },

    _fetchCounter: 0,
    async fetchProductBarcode(id) {
        if (!id) return;
        this._fetchCounter++;
        const reqId = this._fetchCounter;
        this.existingBarcode = '';
        this.form.value = '';
        try {
            const d = await window.POS.api('/api/products/' + id);
            if (reqId === this._fetchCounter && this.form.product_id === id) {
                const product = d?.data;
                const primary = product?.barcodes?.find(b => b.is_primary) || product?.barcodes?.[0];
                if (primary?.value) {
                    this.form.value = primary.value;
                    this.form.barcode_type = primary.barcode_type || 'CODE_128';
                    this.existingBarcode = primary.value;
                } else {
                    this.existingBarcode = '';
                    this.form.value = '';
                }
            }
        } catch(e) { this.existingBarcode = ''; }
    },

    async generateNewBarcode() {
        if (!this.form.product_id) return;
        this.genSaving = true;
        try {
            const d = await window.POS.api('/api/barcodes/generate', {
                method: 'POST',
                body: JSON.stringify({ barcode_type: this.form.barcode_type }),
            });
            if (d?.data?.value) {
                this.form.value = d.data.value;
                this.existingBarcode = '';
            }
        } catch(e) { this.formError = e.message; }
        finally { this.genSaving = false; }
    },

    editBarcode(b) {
        this.editing = true; this.editId = b.id; this.formError = '';
        this.form = { product_id: b.product_id || '', value: b.value || '', barcode_type: b.barcode_type || 'CODE_128', is_primary: b.is_primary || false };
        this.showManualModal = true;
    },

    async saveBarcode() {
        if (!this.form.product_id || !this.form.value.trim()) { this.formError = 'Product and barcode value are required.'; return; }
        this.saving = true; this.formError = '';
        try {
            const method = this.editing ? 'PUT' : 'POST';
            const url = this.editing ? '/api/barcodes/' + this.editId : '/api/barcodes';
            await window.POS.api(url, { method, body: JSON.stringify(this.form) });
            this.showManualModal = false;
            this.fetchBarcodes();
        } catch(e) { this.formError = e.message; }
        finally { this.saving = false; }
    },

    async toggleStatus(id, currentEnabled) {
        try {
            await window.POS.api('/api/barcodes/' + id, {
                method: 'PUT',
                body: JSON.stringify({ is_enabled: !currentEnabled }),
            });
            this.fetchBarcodes();
        } catch(e) { alert(e.message); }
    },

    async deleteBarcode(id) {
        this.toggleStatus(id, true);
    },

    copyBarcode(value, btn) {
        try {
            const input = document.createElement('textarea');
            input.value = value;
            input.style.position = 'fixed';
            input.style.opacity = '0';
            document.body.appendChild(input);
            input.select();
            input.setSelectionRange(0, 99999);
            document.execCommand('copy');
            document.body.removeChild(input);
            if (btn) {
                const orig = btn.textContent;
                btn.textContent = 'Copied!';
                btn.classList.add('text-green-600');
                setTimeout(() => { btn.textContent = orig; btn.classList.remove('text-green-600'); }, 1500);
            }
        } catch(e) {
            navigator.clipboard?.writeText(value).catch(() => {});
        }
    },

    toggleSelect(id) {
        if (this.selectedIds.has(id)) this.selectedIds.delete(id);
        else this.selectedIds.add(id);
    },

    toggleAll() {
        if (this.allSelected) { this.selectedIds.clear(); }
        else { this.barcodes.forEach(b => this.selectedIds.add(b.id)); }
    },

    showPrintModal: false, printLabelSize: 'medium', printSending: false,
    printSettings: { show_product_name: true, show_price: true, show_sku: false, show_company_name: true },
    companyName: '',

    showBulkModal: false, bulkProducts: [], bulkSelected: new Set(), bulkType: 'CODE_128', bulkGenerating: false, bulkSelectAll: false,
    _bulkPrintItems: [],

    openPrintModal() {
        if (this.printItems.length === 0) return;
        this.showPrintModal = true;
        this.printSending = false;
    },

    async sendPrintJob() {
        const ids = this.printItems.map(b => b.id);
        if (!ids.length) return;
        this.printSending = true;
        try {
            const d = await window.POS.api('/api/barcodes/print', {
                method: 'POST',
                body: JSON.stringify({ ids, label_size: this.printLabelSize }),
            });
            alert('Print job sent: ' + (d?.data?.label_count || 0) + ' labels completed.');
            this.showPrintModal = false;
            this._bulkPrintItems = [];
            this.selectedIds.clear();
        } catch(e) { alert(e.message); }
        finally { this.printSending = false; }
    },

    async printBarcodes() {
        this.openPrintModal();
    },
}));

window.Alpine = Alpine;
Alpine.start();



