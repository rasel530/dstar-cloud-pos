import './bootstrap';
import Alpine from 'alpinejs';
import '../css/app.css';
import '../css/rtl.css';
import POS_SOUNDS from './sounds.js';

// ─── Global Theme Store ─── accessible from any Alpine scope via $store.theme
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

// ─── Global Currency Store ───
Alpine.store('currency', {
    code: 'USD',
    symbol: '$',
    decimalPlaces: 2,
});


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
        if (branchId && localStorage.getItem('system_mode') !== 'single') headers['X-Branch-Id'] = branchId;
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
            }
        } catch (e) { /* use defaults */ }
    },
};

// --- Layout Component ---
Alpine.data('layoutData', () => ({
    sidebarOpen: false,
    sidebarExpanded: true,
    sidebarLocked: false,
    currentTime: '',
    user: null,
    init() {
        this.updateClock();
        setInterval(() => this.updateClock(), 1000);
        if (window.innerWidth >= 1024) { this.sidebarExpanded = true; this.sidebarLocked = true; }
        window.addEventListener('resize', () => {
            if (window.innerWidth >= 1024) { this.sidebarExpanded = true; this.sidebarLocked = true; this.sidebarOpen = false; }
            else { this.sidebarLocked = false; }
        });
        this.fetchUser();
    },
    updateClock() {
        const now = new Date();
        this.currentTime = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true })
            + ' \u00B7 ' + now.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    },
    async fetchUser() {
        try {
            const t = localStorage.getItem('auth_token');
            if (!t) return;
            const r = await fetch('/api/auth/me', { headers: { Authorization: 'Bearer ' + t, Accept: 'application/json' } });
            if (r.ok) { const d = await r.json(); this.user = d.data; }
        } catch (e) { /* ignore */ }
    },
}));

// --- POS Cart Component ---
  Alpine.data('posCart', () => ({
    items: [], searchTerm: '', activeCategory: null, products: [],
    categories: [], selectedCustomer: null,
    serviceType: 0, tableNumber: '',
    showCustomerSearch: false, customerSearch: '', searchedCustomers: [],
    discountType: 'percent', discountValue: 0,
    showPayment: false, paymentType: 'cash', tenderAmount: null, changeAmount: 0,
    processingPayment: false, toast: { show: false, message: '', type: 'success' },
    showReceipt: false, receiptData: null, receiptApiUrl: null,
    showAuthRedirect: false, uploading: false, existingOrderId: null, promoDiscount: 0, orderTotal: null,
    showQuickCustomerForm: false, quickCustomerPhone: '', quickCustomerName: '', quickCustomerSaving: false,
    stockMap: {}, allowNegativeStock: false,
    get hasBranch() { return !!(localStorage.getItem('active_branch_id')) || (localStorage.getItem('system_mode') === 'single'); },
    posSettings: {
        grid_columns: 4, grid_rows: 4, default_tax_rate: 10, rounding_rule: 'none',
        sound_effects: true, payment_confirmation: true,
        notification_duration: 3, notification_position: 'bottom-center',
        receipt_auto_print: false,
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
                const vatStr = this.fiscalItems[product.plu];
                const rate = parseFloat(vatStr.replace(/[^0-9.]/g, ''));
                if (rate > 0) total += taxable * (rate / 100);
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
    get filteredProducts() {
        if (!this.searchTerm) return this.products;
        const q = this.searchTerm.toLowerCase();
        return this.products.filter(p =>
            p.name.toLowerCase().includes(q) || (p.code && p.code.toLowerCase().includes(q)));
    },

    async init() {
        const token = window.POS.token();
        if (!token) { this.showAuthRedirect = true; return; }
        await this.loadPosSettings();
        this.loadCategories();
        await this.loadProducts();
        await this.loadFiscalItems();
        await this.loadTaxRate();
        await this.loadDefaultCustomer();
        await this.loadStockSummary();
        window.addEventListener('branch-changed', () => {
            this.items = [];
            this.discountValue = 0;
            this.promoDiscount = 0;
            this.existingOrderId = null;
            this.orderTotal = null;
            this.showPayment = false;
            this.loadStockSummary();
        });
        const params = new URLSearchParams(window.location.search);
        const orderId = params.get('order');
        if (orderId) { await this.loadOrder(orderId); }
    },
    async loadPosSettings() {
        try {
            const data = await window.POS.api('/api/settings');
            if (data?.data) {
                const s = data.data;
                if (s.grid_columns) this.posSettings.grid_columns = parseInt(s.grid_columns) || 4;
                if (s.grid_rows) this.posSettings.grid_rows = parseInt(s.grid_rows) || 4;
                if (s.default_tax_rate) this.posSettings.default_tax_rate = parseFloat(s.default_tax_rate) || 10;
                if (s.rounding_rule) this.posSettings.rounding_rule = s.rounding_rule;
                if (s.sound_effects !== undefined) this.posSettings.sound_effects = s.sound_effects === 'true' || s.sound_effects === true;
                if (s.payment_confirmation !== undefined) this.posSettings.payment_confirmation = s.payment_confirmation === 'true' || s.payment_confirmation === true;
                if (s.notification_duration) this.posSettings.notification_duration = parseInt(s.notification_duration) || 3;
                if (s.notification_position) this.posSettings.notification_position = s.notification_position;
                if (s.receipt_auto_print !== undefined) this.posSettings.receipt_auto_print = s.receipt_auto_print === 'true' || s.receipt_auto_print === true;
            }
        } catch (e) { /* use defaults */ }
    },
    async loadDefaultCustomer() {
        try {
            const data = await window.POS.api('/api/customers?search=Walk-in');
            const customers = Array.isArray(data?.data) ? data.data : (data?.data?.data || []);
            if (customers.length > 0) {
                this.selectedCustomer = customers[0];
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
    async loadDefaultStocks() {
        if (this.items.length === 0) return;
        try {
            const ids = [...new Set(this.items.map(i => i.product_id))];
            const data = await window.POS.api('/api/stock/pos-summary?product_ids=' + ids.join(','));
            const list = Array.isArray(data?.data) ? data.data : (data?.data?.data || []);
            list.forEach(s => { this.stockMap[s.product_id] = s; });
        } catch (e) {}
    },
    async loadOrder(orderId) {
        try {
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
                this.discountType = 'flat';
                this.discountValue = storedDiscount;
                this.tableNumber = order.number || '';
                this.selectedCustomer = order.customer || null;
                this.serviceType = order.service_type || 0;
        } catch (e) { this.toastMsg('Failed to load order', 'error'); }
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
                this.taxRate = this.posSettings.default_tax_rate || 0;
                this.taxIsFixed = false;
            }
        } catch (e) { this.taxRate = this.posSettings.default_tax_rate || 0; this.taxIsFixed = false; }
    },
    async loadProducts() {
        try {
            let url = '/api/products';
            if (this.activeCategory) url += '?product_group_id=' + this.activeCategory;
            const res = await window.POS.api(url);
            this.products = Array.isArray(res?.data) ? res.data : (res?.data?.data || []);
        } catch (e) { this.toastMsg('Failed to load products', 'error'); this.products = []; }
    },
    async searchProducts() {
        if (!this.searchTerm || this.searchTerm.length < 2) { await this.loadProducts(); return; }
        try {
            let url = '/api/products?search=' + encodeURIComponent(this.searchTerm);
            if (this.activeCategory) url += '&product_group_id=' + this.activeCategory;
            const data = await window.POS.api(url);
            this.products = Array.isArray(data?.data) ? data.data : (data?.data?.data || []);
        } catch (e) {}
    },
    async handleBarcodeSearch() {
        const term = this.searchTerm.trim(); if (!term) return;
        try { const data = await window.POS.api('/api/products?search=' + encodeURIComponent(term)); const p = (data.data || [])[0]; if (p) { this.addToCart(p); this.searchTerm = ''; this.loadProducts(); } else this.toastMsg('Product not found', 'error'); } catch (e) { this.toastMsg('Product not found', 'error'); }
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
        if (existing) { existing.qty++; } else { this.items.push({ id: product.id, name: product.name, price: parseFloat(product.price), qty: 1 });
            if (this.posSettings.sound_effects) POS_SOUNDS.addItem(); }
        this.showToast('Added: ' + product.name);
        this.refreshPromoDiscounts();
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
            if (this.posSettings.sound_effects) POS_SOUNDS.removeItem(); this.refreshPromoDiscounts(); },
    updateQty(idx, qty) { if (qty < 1) return; this.items[idx].qty = qty; this.refreshPromoDiscounts(); },
    newSale() { if (this.items.length && !confirm('Clear order?')) return; this.items = []; this.discountType = 'percent'; this.discountValue = 0; this.promoDiscount = 0; this.selectedCustomer = null; this.orderTotal = null; this.existingOrderId = null; },
    openPayment(type) { if (!this.items.length) return; this.loadDefaultStocks().then(() => { this.paymentType = type; this.tenderAmount = this.grandTotal; this.showPayment = true; }); },
    async calcPromotionDiscounts() {
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
        return totalPromoDiscount;
    },
    async refreshPromoDiscounts() {
        if (!this.items.length) { this.promoDiscount = 0; return; }
        this.promoDiscount = await this.calcPromotionDiscounts();
    },
    async processPayment() {
        if (this.processingPayment || !this.items.length) return;
        if (this.posSettings.payment_confirmation && !confirm('Confirm payment of ' + window.POS.formatCurrency(this.grandTotal) + '?')) return;
        this.processingPayment = true;
        try {

            const promoDiscounts = await this.calcPromotionDiscounts();

            const payload = {
                items: this.items.map(i => ({ product_id: i.product_id || i.id, quantity: i.qty, price: i.price })),
                discount: this.discount + promoDiscounts, total: this.grandTotal, payment_type: this.paymentType,
                paid_amount: this.tenderAmount || this.grandTotal,
                customer_id: this.selectedCustomer?.id || null,
                table_number: this.tableNumber || null,
                service_type: this.serviceType,
            };
            let checkoutRes = null;
            if (this.existingOrderId) {
                const addItems = payload.items.map(item =>
                    window.POS.api('/api/orders/' + this.existingOrderId + '/items', { method: 'POST', body: JSON.stringify({ product_id: item.product_id, quantity: item.quantity, price: item.price }) })
                );
                await Promise.all(addItems);
                this.receiptApiUrl = '/api/receipts/' + this.existingOrderId;
                checkoutRes = await window.POS.api('/api/orders/' + this.existingOrderId + '/checkout', { method: 'POST', body: JSON.stringify({ payment_type: this.paymentType, paid_amount: payload.paid_amount, discount: this.discount, discount_type: this.discountType === 'percent' ? 0 : 1, total: this.grandTotal, customer_id: payload.customer_id, table_number: payload.table_number }) });
            } else {
                const orderRes = await window.POS.api('/api/orders', { method: 'POST', body: JSON.stringify(payload) });
                const orderId = orderRes?.data?.id;
                if (orderId) {
                    this.receiptApiUrl = '/api/receipts/' + orderId;
                    checkoutRes = await window.POS.api('/api/orders/' + orderId + '/checkout', { method: 'POST', body: JSON.stringify({ payment_type: this.paymentType, paid_amount: payload.paid_amount, discount: this.discount, discount_type: this.discountType === 'percent' ? 0 : 1, total: this.grandTotal, customer_id: payload.customer_id, table_number: payload.table_number }) });
                }
            }
            const receipt = checkoutRes?.data?.receipt;
            if (receipt) {
                this.receiptData = receipt;
                this.showReceipt = true;
                if (this.posSettings.receipt_auto_print) {
                    setTimeout(() => { this.$refs.receiptFrame?.contentWindow?.print(); }, 800);
                }
            } else {
                this.toastMsg('Sale completed!', 'success');
            }
            if (this.posSettings.sound_effects) POS_SOUNDS.paymentComplete(); this.showPayment = false; this.items = []; this.discountType = 'percent'; this.discountValue = 0; this.promoDiscount = 0; this.existingOrderId = null; this.orderTotal = null;
        } catch (e) { this.toastMsg('Payment failed', 'error');
            if (this.posSettings.sound_effects) POS_SOUNDS.error(); } finally { this.processingPayment = false; }
    },
    downloadReceipt() {
        if (!this.receiptApiUrl) return;
        const w = window.open('', '_blank');
        if (!w) return;
        window.POS.api(this.receiptApiUrl).then(r => {
            const receipt = r?.data;
            if (receipt?.receipt_html) {
                w.document.write(receipt.receipt_html);
                w.document.close();
                setTimeout(() => { w.print(); }, 600);
            } else { w.close(); }
        }).catch(() => { w.close(); });
    },
    formatMoney(amount) { return window.POS.formatCurrency(amount); },
    truncate(str, len) { if (!str) return ''; return str.length > len ? str.substring(0, len) + '\u2026' : str; },
    colorForProduct(product) { const colors = ['#3b82f6','#8b5cf6','#06b6d4','#f59e0b','#10b981','#ef4444','#ec4899','#6366f1','#14b8a6','#f97316']; const c = product?.color; if (c && c.startsWith('#')) return c; let hash = 0; const str = product?.name || ''; for (let i = 0; i < str.length; i++) hash = str.charCodeAt(i) + ((hash << 5) - hash); return colors[Math.abs(hash) % colors.length]; },
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

// --- Dashboard Component ---
Alpine.data('dashboard', () => ({
    data: null, loading: true,
    get maxRevenue() { if (!this.data?.revenue_chart?.length) return 1; return Math.max(...this.data.revenue_chart.map(d => d.value), 1); },
    async init() {
        try { this.data = await window.POS.api('/api/dashboard'); } catch (e) { this.data = { todays_sales: 0, orders_count: 0, products_count: 0, low_stock_count: 0, revenue_chart: [], recent_orders: [], avg_order_value: 0, customers_count: 0, pending_orders: 0, completed_today: 0 }; } finally { this.loading = false; }
    },
}));

// --- Products Manager Component ---
Alpine.data('productsManager', () => ({
    products: [], loading: true, search: '', pagination: null,
    showModal: false, editing: false, saving: false, productGroups: [], branches: [],
    showNewGroup: false, newGroupName: '', uploadingStock: false, toast: { show: false, message: '', type: 'success' },
    measurementUnits: [], showNewUnit: false, newUnitName: '', newUnitKey: '',
    showTransferModal: false, transferring: false, transferForm: { product_code: '', quantity: 1, from_branch: '', to_branch: '' }, transferMessage: '', transferError: false,
    form: { name: '', code: '', plu: '', price: 0, cost: 0, product_group_id: null, measurement_unit: '', is_enabled: true, track_inventory: true, is_global: true, stock_qty: 0, branch_stocks: {} },
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
            } else {
                await this.fetchGroups();
            }
            this.newGroupName = '';
            this.showNewGroup = false;
        } catch (e) { alert('Failed to create group: ' + (e.message || 'Unknown error')); }
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
    async fetchProducts(page = 1) {
        this.loading = true;
        try { const r = await window.POS.api('/api/products?page=' + page + (this.search ? '&search=' + this.search : '')); this.products = r?.data?.data || r?.data || []; this.pagination = r?.meta || r?.data?.meta || { current_page: 1, last_page: 1, total: this.products.length }; } catch (e) { this.products = []; this.pagination = { current_page: 1, last_page: 1, total: 0 }; } finally { this.loading = false; }
    },
    openAdd() { this.editing = false; this.form = { name: '', code: '', price: 0, cost: 0, product_group_id: null, measurement_unit: '', is_enabled: true, track_inventory: true, is_global: true, stock_qty: 0, branch_stocks: {} }; this.showModal = true; },
    openEdit(p) { this.editing = true; this.form = { ...p, stock_qty: p.stock || 0, branch_stocks: {}, is_global: p.is_global !== false }; if (p.branch_stocks) { p.branch_stocks.forEach(b => { this.form.branch_stocks[b.branch_id] = b.stock; }); } this.showModal = true; },
    async save() {
        this.saving = true;
        try {
            const method = this.editing ? 'PUT' : 'POST';
            const url = this.editing ? '/api/products/' + this.form.id : '/api/products';
            const payload = { name: this.form.name, code: this.form.code, price: this.form.price, cost: this.form.cost, product_group_id: this.form.product_group_id, plu: this.form.plu, measurement_unit: this.form.measurement_unit, is_enabled: this.form.is_enabled, track_inventory: this.form.track_inventory, is_global: this.form.is_global };
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
    customers: [], loading: true, search: '', pagination: null,
    showModal: false, editing: false, saving: false,
    toast: { show: false, message: '', type: 'success' },
    form: { name: '', email: '', phone_number: '', code: '', is_enabled: true },
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
    async toggleStatus(customer) { try { await window.POS.api('/api/customers/' + customer.id, { method: 'PUT', body: JSON.stringify({ is_enabled: !customer.is_enabled }) }); customer.is_enabled = !customer.is_enabled; this.toastMsg(customer.is_enabled ? 'Customer enabled' : 'Customer disabled', 'success'); } catch (e) { this.toastMsg('Toggle failed', 'error'); } },
    toastMsg(message, type = 'success') { this.toast = { show: true, message, type }; clearTimeout(this._t); this._t = setTimeout(() => { this.toast.show = false; }, 2500); },
}));

// --- Orders List Component ---
Alpine.data('ordersList', () => ({
    orders: [], loading: true, statusFilter: 'all', searchQuery: '',
    currentPage: 1, totalPages: 1, totalOrders: 0,
    async init() { await this.fetchOrders(); },
    async fetchOrders(page = 1) {
        this.loading = true;
        try {
            let url = '/api/orders?page=' + page;
            if (this.statusFilter !== 'all') url += '&status=' + this.statusFilter;
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
            if (!receipt?.receipt_html) { alert('Receipt not available'); return; }
            const w = window.open('', '_blank');
            if (!w) return;
            w.document.write(receipt.receipt_html);
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
    customerId: '', customers: [], pagination: null, custPage: 1,
    tabs: [
        { key: 'sales', label: 'Sales Summary' },
        { key: 'bestselling', label: 'Best Selling' },
        { key: 'customers', label: 'Customer Analytics' },
        { key: 'customer-detail', label: 'Customer Detail' },
        { key: 'tax', label: 'Tax Report' },
    ],
    get chartMax() {
        if (!this.tabData?.chart_data?.length) return 1;
        return Math.max(...this.tabData.chart_data.map(d => d.value), 1);
    },
    async init() { await Promise.all([this.fetchTabData(), this.fetchCustomers()]); },
    async fetchCustomers() {
        try { const r = await window.POS.api('/api/customers?per_page=500'); this.customers = r.data?.data || r.data || []; } catch(e) { this.customers = []; }
    },
    async fetchTabData(page = 1) {
        this.loading = true;
        try {
            const apiMap = { sales: 'sales-summary', bestselling: 'best-selling', customers: 'customers', tax: 'taxes', 'customer-detail': 'customer-sales' };
            let url = '/api/reports/' + (apiMap[this.activeTab] || 'sales-summary');
            let params = [];
            if (this.dateFrom) params.push('start_date=' + this.dateFrom + '&end_date=' + this.dateTo);
            params.push('page=' + page + '&per_page=25');
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
            if (!receipt?.receipt_html) { alert('Receipt not available'); return; }
            const w = window.open('', '_blank');
            if (!w) return;
            w.document.write(receipt.receipt_html);
            w.document.close();
            setTimeout(() => { w.print(); }, 600);
        } catch (e) { alert('Failed to load receipt'); }
    },
}));

// --- Users Manager Component ---
Alpine.data('usersManager', () => ({
    users: [], loading: true, pagination: null, roles: [], branches: [], currentUserId: null,
    showModal: false, editing: false, saving: false, editId: null, showPwd: false, uploadingStock: false,
    error: '',
    form: { first_name: '', last_name: '', username: '', email: '', password: '', access_level: 0, is_enabled: true, branch_id: '', branch_ids: [] },
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
    openAdd() { this.editing = false; this.editId = null; this.error = ''; this.showPwd = false; this.form = { first_name: '', last_name: '', username: '', email: '', password: '', access_level: 0, is_enabled: true, branch_id: '', branch_ids: [] }; this.showModal = true; },
    openEdit(u) { this.editing = true; this.editId = u.id; this.error = ''; this.showPwd = false; this.form = { first_name: u.first_name || '', last_name: u.last_name || '', username: u.username || '', email: u.email || '', password: '', access_level: u.access_level ?? 0, is_enabled: u.is_enabled ?? true, branch_id: u.branch_id || '', branch_ids: u.branches ? u.branches.map(b => b.id) : [] }; this.showModal = true; },
    async save() {
        this.saving = true; this.error = '';
        try {
            const method = this.editing ? 'PUT' : 'POST', url = this.editing ? '/api/users/' + this.editId : '/api/users';
            const payload = { first_name: this.form.first_name, last_name: this.form.last_name, username: this.form.username, email: this.form.email, access_level: parseInt(this.form.access_level), is_enabled: this.form.is_enabled, branch_id: this.form.branch_id || null, branch_ids: this.form.branch_ids };
            if (this.form.password) payload.password = this.form.password;
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
    taxes: [], loading: true, pagination: null,
    showModal: false, editing: false, saving: false,
    form: { name: '', rate: 10, code: '', is_fixed: false, is_enabled: true },
    async init() { await this.fetchTaxes(); },
    async fetchTaxes(page = 1) {
        this.loading = true;
        try { const r = await window.POS.api('/api/taxes?page=' + page); this.taxes = r.data?.data || r.data || []; this.pagination = r.meta || r.data?.meta || { current_page: 1, last_page: 1, total: 0 }; } catch (e) { this.taxes = []; } finally { this.loading = false; }
    },
    openAdd() { this.editing = false; this.form = { name: '', rate: 10, code: '', is_fixed: false, is_enabled: true }; this.showModal = true; },
    openEdit(t) { this.editing = true; this.form = { ...t }; this.showModal = true; },
    async save() {
        this.saving = true;
        try {
            const method = this.editing ? 'PUT' : 'POST', url = this.editing ? '/api/taxes/' + this.form.id : '/api/taxes';
            await window.POS.api(url, { method, body: JSON.stringify(this.form) });
            this.showModal = false; this.fetchTaxes();
        } catch (e) { alert(e.message); } finally { this.saving = false; }
    },
    async deleteTax(id) { if (!confirm('Delete this tax?')) return; try { await window.POS.api('/api/taxes/' + id, { method: 'DELETE' }); this.fetchTaxes(); } catch (e) { alert(e.message); } },
    async toggleStatus(tax) { try { await window.POS.api('/api/taxes/' + tax.id, { method: 'PUT', body: JSON.stringify({ is_enabled: !tax.is_enabled }) }); tax.is_enabled = !tax.is_enabled; } catch (e) { alert(e.message); } },
}));

// --- Promotions Manager Component ---
Alpine.data('promotionsManager', () => ({
    promotions: [], loading: true, pagination: null,
    showModal: false, editing: false, saving: false,
    form: { name: '', start_date: '', end_date: '', days_of_week: 127, is_enabled: true },
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
    cards: [], loading: true, pagination: null, customers: [],
    showModal: false, points: 0, selectedCard: null, transactionType: 'earn',
    showAddCard: false, newCard: { customer_id: '', card_number: '' },
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
    printers: [], loading: true, pagination: null,
    showModal: false, editing: false, saving: false,
    form: { printer_name: '', paper_width: 32, header: '', footer: '', feed_lines: 0, cut_paper: true, open_cash_drawer: true, printer_type: 0, number_of_copies: 1 },
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
    async testPrint(id) { try { await window.POS.api('/api/printers/' + id + '/test', { method: 'POST' }); alert('Test print sent'); } catch (e) { alert(e.message); } },
}));

// Branches Manager
Alpine.data('branchesManager', () => ({
    branches: [], loading: true, pagination: null,
    showModal: false, editing: false, saving: false,
    uniqueBusinessTypes: [],
    form: { name: '', branch_code: '', business_type: 'Retail', address: '', phone: '', is_headquarters: false },
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
    showWarehouseModal: false, warehouseSaving: false, editingWarehouseId: null,
    warehouseForm: { name: '', is_default: false },
    allProducts: [], addProductId: '', addProductQty: 1,
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
        this.stockLoading = true;
        try {
            const r = await window.POS.api('/api/stock?warehouse_id=' + id + '&per_page=100');
            const items = r?.data?.data || r?.data || [];
            this.warehouseStocks = items.map(s => ({
                product_id: s.product_id,
                product_name: s.product ? s.product.name : '—',
                product_code: s.product ? s.product.code : '',
                quantity: parseFloat(s.quantity) || 0,
                branch_summary: '',
            }));
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
    logs: [], loading: true, pagination: null,
    filterModule: '', filterDateFrom: '', filterDateTo: '',
    async init() { await this.fetchLogs(); },
    async fetchLogs(page = 1) {
        this.loading = true;
        try {
            let url = '/api/activity?page=' + page;
            if (this.filterModule) url += '&module=' + this.filterModule;
            if (this.filterDateFrom) url += '&date_from=' + this.filterDateFrom;
            if (this.filterDateTo) url += '&date_to=' + this.filterDateTo;
            const r = await window.POS.api(url);
            this.logs = r?.data?.data || r?.data || [];
            this.pagination = r?.data?.meta || r?.meta || { current_page: 1, last_page: 1 };
        } catch(e) { this.logs = []; }
        finally { this.loading = false; }
    },
}));

window.Alpine = Alpine;
Alpine.start();



