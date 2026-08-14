# D Star Company -- Point of Sale (POS) System

A modern, responsive, multi-branch Point of Sale application built with Laravel, Tailwind CSS, and Alpine.js. Designed for restaurants, cafes, retail stores, and multi-location businesses.

---

## Features

### Point of Sale (POS)
- **Browse / Build Modes** -- segmented toggle: **Browse** (full product grid) or **Build Order** (large editable cart in the middle with search-to-add; calculation/payment stays in the right sidebar)
- **Product Grid** -- customizable grid layout (2-6 columns) with category filtering
- **Cart Management** -- add/remove items, adjust quantities, apply discounts (flat/percentage/promo)
- **Cart Price Edit** -- inline line-item price override (pencil icon) for users with "Can Edit POS Price" permission; enforces 5× product-price cap + 2-decimal rounding
- **Order Types** -- Dine-in, Takeaway, and Table Management (all toggleable from Settings)
- **Customer Selection** -- search existing customers or create walk-in customers inline
- **Payment Processing** -- dynamic payment methods (Cash, Card, Check, bKash, Nagad, Rocket, Bank Transfer, Customer Due) with reorderable shortcuts
- **Barcode Scanning** -- scan barcode + Enter auto-adds product to cart (fast checkout flow)
- **Automated Receipt Printing** -- plain-text ESC/POS receipts auto-printed to thermal printers on checkout (runs in background after response — never blocks the receipt display)
- **Fast Receipt Display** -- checkout response returns in ~0.3s; "Processing..." overlay shown, receipt appears instantly
- **Keyboard Shortcuts** -- `F1`-`F5` payment methods, `F8` Print, `Esc` Close, `Shift+?` Shortcut help
- **Receipt Generation** -- printable HTML receipts + thermal text receipts with centered headers/company name and configurable header/footer
- **Order Loading** -- load and edit existing orders
- **Virtual Keyboard** -- on-screen keyboard for touch devices

### Orders
- Full order history with search (order number / customer name, case-insensitive) and status filtering
- Order status management (Open, Closed, Cancelled, Refunded)
- View, Complete, and Refund orders
- Table / Name column shown only when Table Management is enabled in Settings
- Download receipts as PDF
- Responsive: card-based layout on mobile, table on desktop

### Purchases
- Supplier management with full CRUD (name, contact, email, address, status)
- Purchase orders with multi-item support, payments (partial/full), status tracking
- Purchase returns management
- Report dashboard: by supplier, by product, monthly breakdown, outstanding payments
- Supplier/category stock tracking integration

### Income & Expenses
- Dashboard with Income vs Expenses summary cards, monthly bar chart, recent entries
- Income & Expenses CRUD with search, category/date filters, pagination
- Category management with colored badges (12 default categories, 4 income + 8 expense)
- POS Sales auto-sync: imports completed orders as income entries grouped by actual payment method
- Reports: date-range filtered summary with by-category breakdown

### Cash Register (Opening / Closing Cash)
- **Open Register** -- select shift (dynamic), enter opening cash
- **Close Register** -- summary (Opening Cash + Cash Sales + Cash In − Cash Refund − Cash Out = Expected Cash), actual cash count, variance
- **Cash In / Cash Out** -- recorded with dynamic reasons (managed in Settings)
- **Per-cashier separation** -- each cashier has their own register (scoped by user + branch)
- **Register History** -- past shifts with opening/expected/actual/variance + cashier session timeline
- **Unattended detection** -- idle register warning (last activity tracking)
- **Logout protection** -- warns if register is open, register stays open on logout (auth ≠ register state)
- **Cart persistence** -- cart survives page refresh, restored on re-login

### Open / Held Orders
- Hold (park) orders mid-transaction, resume or cancel later
- Open/Held orders panel with resume/cancel actions
- Order state tracking (open/held/closed/refunded/cancelled)

### Barcode Management
- Barcode generator (CODE-128 / EAN-13 / UPC-A) with check-digit validation
- Barcode list with SVG barcode image rendering (JsBarcode), search, type/status filters
- Product integration: barcode field + auto-generate button in product create/edit modal
- Print Labels: checkbox selection → preview modal with label size → ESC/POS print
- POS scanning: hardware barcode scanner → auto-detect product → add to cart (50ms debounce)
- Deactivate/Activate toggle preserves history; Copy-to-clipboard with visual feedback
- Barcode Settings: default type, auto-generate, label content toggles

### Products
- Product CRUD with Code/SKU, PLU, pricing, and categories
- Stock management with branch-level inventory
- Branch-to-branch stock transfer
- Bulk stock upload
- Configurable tax rates and rounding rules

### Warehouses / Inventory
- Multi-warehouse management
- Per-branch stock tracking
- Stock upload and management

### Customers
- Customer database with Code, Phone, Email, Address
- Loyalty points tracking per customer
- Enable/disable toggles for customer accounts
- Status badges and quick actions
- **Outstanding balance** -- red balance column + Add Payment button
- **Customer statement** -- invoices + payments + running balance
- **Partial payments** -- auto-allocated across multiple invoices

### Customer & Supplier Due
- **Customer Due** -- credit sales (Customer Due payment method) tracked with paid/due amounts
- **Supplier Due** -- purchase totals with paid/due amounts
- **Payment recording** -- partial payments, overpayment protection (capped at due)
- **Statements** -- customer statement (invoices + payments) and supplier statement (purchases + payments)
- **Due reports** -- Customer Due and Outstanding Purchases

### Reports & Analytics
- **Sales Summary** -- revenue totals, order counts, avg order, tax collected, revenue trend chart (net of refunds)
- **Payment Methods** -- amount + transaction count per payment method
- **Best Selling** -- products ranked by revenue with gold/silver/bronze badges
- **Profit & Loss** -- Gross Sales → Net Sales → COGS → Gross Profit → Other Income → Operating Expenses → Net Profit
- **Customer Analytics** -- top customers by spending, total orders
- **Customer Due** -- outstanding balance per customer
- **Customer Detail** -- per-customer drill-down: orders, top products, receipts
- **Employee Detail** -- per-employee sales breakdown: orders, items, top products
- **Tax Report** -- tax collected by date with totals
- Date range filtering across all reports
- Status filter (Closed / Open / Refunded / All)
- Branch filtering in multi-branch mode
- Pagination support

### Settings
- **General** — Company name, application logo, currency, contact info
- **POS** — Grid columns/rows, tax rate, rounding rules, sound effects, payment confirmation, Dine-in/Takeaway/Table toggles
- **Receipt** — Auto-print settings, header/footer customization, **dual logo upload** (Application Logo + Receipt/PDF Logo)
- **Notifications** — Duration and position configuration
- **System** — Single Company vs Multi-Branch mode
- **Payment Methods** — Full CRUD for payment types (Cash, Card, Check, bKash, Nagad, Rocket, Bank, Customer Due) with Quick Pay toggle, shortcut keys, enable/disable, reorderable (drag up/down)
- **Cash Register** — Shift management (CRUD), Cash In/Out reasons management
- **Barcode** — Default barcode type, auto-generate toggle, label content toggles (name, price, SKU, company)

### Multi-Branch Support
- **Single Company Mode** -- all data across one location
- **Multi-Branch Mode** -- branch selector, branch-specific inventory, orders filtered by branch
- Role-based branch assignment (Admin sees all, Managers/Staff see assigned branches only)
- Branch-to-branch stock transfer
- Branch settings per location

### Admin Modules
- **Users** -- user management with role assignment
- **Roles & Permissions** -- role-based access control
- **Branches** -- multi-branch configuration
- **Taxes** -- tax rate management
- **Printers / Slips** -- thermal printer configuration
- **Fiscal Items** -- legal/compliance document management
- **Promotions** -- discount promotion management
- **Loyalty Cards** -- loyalty program with points tracking
- **Activity Log** -- audit trail of all system actions with category tabs, filters, and detail view

### UI/UX
- **Fully Responsive** -- works on desktop, tablet, and mobile
- **Dark Mode** -- full dark mode support across all modules
- **Mobile-Optimized** -- card-based mobile layouts, collapsible sidebar, slide-in cart panel
- **Touch-Friendly** -- 44px minimum touch targets, iOS input zoom prevention
- **RTL Support** -- right-to-left layout for Arabic/Hebrew languages
- **Toast Notifications** -- success/error feedback with configurable position

---

## Recent Updates & Bug Fixes

### Latest Release

1. **Cart Line-Item Price Edit (Admin-Controlled)** — super/admin can enable "Can Edit POS Price" per user (`/users` → Edit). Cashiers with permission can click the pencil icon on any cart line and edit the price inline (Browse + Build modes). Enforces max 5× product price, 2-decimal rounding, green custom-price indicator, Esc to cancel.
2. **POS Browse/Build Mode Toggle** — redesigned POS layout with a mode toggle: **Browse** (full product grid) and **Build Order** (large editable cart in the middle + calculation/payment in the right sidebar). Search dropdown in Build mode adds products directly to the cart.
3. **Orders Search** — search orders by order number or customer name (case-insensitive) directly from the Orders page.
4. **Checkout Item Sync Fix** — resumed/held orders now sync items exactly to the cart before checkout (prevents duplicate items appearing on the receipt).
5. **Discount Calculation Fix** — discount (% or flat) is applied to the subtotal BEFORE tax (Subtotal → Discount → Taxable Amount → Tax → Grand Total). Verified against your example.
6. **Discount Reset** — discount clears automatically after checkout (robust against exceptions).
7. **Hold Orders Resume Fix** — resume works for both held and open orders; cart loads reliably.
8. **Receipt Large Totals** — 5-6+ digit totals fit on one line (HTML + ESC/POS text + PDF).
9. **Receipt Centering (Thermal)** — ESC/POS text receipt now centers company name/headers with full-width balanced padding + left-align command; logo, QR, and text center on the paper.
10. **Fast Receipt Display** — checkout auto-print moved to a background (`terminating()`) callback + "Processing..." overlay. Receipt now appears in ~0.3s instead of ~2.5s.
11. **Sidebar Width Increased** — right sidebar now 400px on desktop (was 320px) for comfortable totals/payment display.
12. **Table / Name Column (Conditional)** — the Orders page hides the "Table / Name" column automatically when Table Management is disabled in Settings (desktop + mobile).
13. **Table Number Display Fix** — orders without a table no longer show "Table: --"; loading an order puts the correct table number (not the order number) in the POS table field.
14. **User Permission System** — per-user `can_edit_price` permission flag managed from the Users page.
15. **Dual Logo System (Application + Receipt/PDF)** — Settings → Receipt → Receipt Settings now has **two** logo uploads: an **Application Logo** (colorful, shown across the app: sidebar, login, etc.) and a **Receipt / PDF Logo** (black/white, used only on printed receipts and PDF exports). Receipts/PDFs prefer the receipt logo and fall back to the application logo when it's empty.
16. **Logo Auto-Optimization** — both logos are resized and compressed on upload (client-side canvas): app logo max 512px, receipt logo max 320px; PNG keeps transparency, other formats saved as JPEG @ 85%. A 2000×2000 upload becomes ~21KB (512px) / ~2.3KB (320px).
17. **POS Cart — One-Row Mobile Layout** — browse-mode and build-mode cart items render as a single row on mobile (name + price + qty stepper + line total + remove), so the quantity increment/decrement controls no longer push the product name to a second line, and the pencil (price edit), "× qty", and "−" icons never overlap.
18. **Stepper Column Alignment** — the qty increment/decrement column is aligned across all cart items (fixed-width line-total column) regardless of product name length or price width.
19. **Full-Width Mobile Cart Drawer** — the cart drawer uses the full phone width on mobile (`w-full sm:w-80 lg:w-100`) for comfortable one-row items; tablets and desktop keep the 320px/400px panel.

### Major Features (this release)

1. **Cash Register (Opening/Closing Cash)** — full shift-based cash drawer management with open/close, cash in/out, dynamic shifts and reasons, per-cashier separation, register history with cashier session timeline, unattended detection, and logout protection.
2. **Dynamic Payment Methods** — expanded to 8 methods (Cash, Card, Check, bKash, Nagad, Rocket, Bank Transfer, Customer Due) with reorderable buttons and shortcuts.
3. **COGS + Profit & Loss** — cost snapshot captured at sale time, full P&L report (Gross Sales → Net Sales → COGS → Gross Profit → Other Income → Expenses → Net Profit).
4. **Customer & Supplier Due** — credit sales tracking, partial payments with auto-allocation, customer/supplier statements, overpayment protection.
5. **Open/Held Orders** — hold, resume, and cancel orders mid-transaction.
6. **Enhanced Activity Log** — category tabs, user/action/branch/search filters, detail modal with device/IP/changes, login/logout + cash register tracking.
7. **Cart Persistence** — cart survives page refresh, restored on re-login.
8. **Logo Optimization** — company logo resized/compressed (96KB → 14KB) for faster page loads.

### 1. RTL/LTR Toggle — Side Panel Positioning (Fixed)
- **Problem:** When toggling to RTL mode, the left/right side panels swapped incorrectly and the right panel slid off-screen and vanished.
- **Fix:**
  - Removed the incorrect `[dir="rtl"] .flex-row { flex-direction: row-reverse }` rule — `flex-direction: row` already respects the RTL writing mode naturally.
  - Added responsive translate resets (`lg:translate-x-0`, etc.) so the cart panel stays visible on desktop in RTL.
  - Scoped `translate-x-full` / `-translate-x-full` panel slide overrides to mobile only (`@media max-width: 1023px`).
  - Added comprehensive RTL overrides for padding (`pl-0..12`, `pr-0..12`), margins (`ml-0..8`, `mr-0..8`), and positions (`left-*`, `right-*`, negative variants).
  - Added `left-3.5`, `right-4`, `-left-*`, `-right-*`, `left-1/2`, `right-1/2`, and rounded-corner overrides.

### 2. Sidebar Blink / Shake on Navigation (Fixed)
- **Problem:** The left sidebar blinked, shrank, and shook when clicking menu items.
- **Fix:**
  - Moved the `transition-[width,transform]` class into the Alpine `:class` array binding so it only applies after Alpine initializes (no pre-Alpine animation).
  - Added static `w-56` so the sidebar renders at full width immediately.
  - Used static responsive classes (`-translate-x-full lg:translate-x-0`) for the correct pre-Alpine state on mobile vs desktop.
  - Added `x-cloak` with a desktop CSS override (`@media min-width: 1024px { aside[x-cloak] { display: flex !important } }`).
  - Removed the duplicate `layoutData` definition from `app.js` that had conflicting resize handlers.

### 3. Full Application Shake / Blink on Page Load (Fixed)
- **Problem:** The entire app blinked when navigating between pages because `dir="rtl"` and the `dark` class were applied by Alpine *after* the first paint.
- **Fix:** Added a blocking `<script>` in the `<head>` that reads `localStorage` and applies `dir` and `dark` class synchronously **before** the browser renders anything.

### 4. Header Shake on Page Load (Fixed)
- **Problem:** The header clock and user-info elements rendered empty then filled in, causing layout reflow.
- **Fix:** Added `x-cloak` to the clock span and user info div so they stay hidden until Alpine initializes with the correct content.

### 5. Modal / Button Flash on Page Load (Fixed)
- **Problem:** The "Transfer Stock" modal, "Add Warehouse" modal, Add/Edit Product modal, and Upload Stock spinner/button flashed visibly for a split second before Alpine initialized.
- **Fix:** Added `x-cloak` to all modals and upload button spans (`Uploading...`) that use `x-show`.

### 6. Browser Compatibility (Fixed)
- **Problem:** Tailwind CSS v4 uses `oklch()` colors which break on older browsers (Chrome < 111, Safari < 15.4, Firefox < 113). Also `appearance: textfield` lacked the `-webkit-` prefix for Safari.
- **Fix:**
  - Added `resources/css/browser-fallbacks.css` with an `@supports not (color: oklch(0 0 0))` block providing hex-based fallbacks for all 11 used Tailwind color families.
  - Added `-webkit-appearance: textfield` for Safari number input spinners.

### 7. Demo Products (Added)
- **Problem:** Only a handful of demo products existed for testing.
- **Fix:** Added a `DemoProductsSeeder` with 48 products across 9 product groups (5 new groups: Pastries, Tea, Snacks, Ice Cream, Bakery), each with full pricing, cost, MRP, units, colors, descriptions, and randomized stock quantities.

### 8. Product Pagination & POS Product Loading (Fixed)
- **Problem:** The Products page hardcoded 25 products/page and pagination never worked; the POS page only loaded 12 products.
- **Fix:**
  - `ProductController::index()` now respects the `per_page` query parameter (default 25).
  - Fixed the Products page to read pagination metadata from the correct response path (`r?.data` instead of the non-existent `r?.meta`).
  - POS page requests `per_page=200` and uses `current_page < last_page` for the Load More button.

### 9. Product Card Color / Contrast (Fixed)
- **Problem:** Product cards used arbitrary background colors with white text, causing unreadable cards on 40+ products.
- **Fix:** Redesigned cards to use standard white/dark backgrounds with a thin product-color accent strip at the top. Text uses standard high-contrast colors, and stock status uses proper green/red indicators. Zero contrast issues remain.

### 10. Stock Visibility in Single Company Mode (Fixed)
- **Problem:** In single-company mode, demo products showed as out-of-stock / unsaleable because `posSummary` filtered warehouses and stocks by `tenant_id`, but the user's tenant didn't match the default warehouse's tenant.
- **Fix:**
  - `posSummary()` now uses `Warehouse::where('is_default', true)` without tenant filtering and queries stocks by `warehouse_id` only.
  - Added a server-side `SystemModeService::isSingleMode()` guard so a wrong `X-Active-Branch` header can't accidentally switch the read path in single mode.
  - `bulkUpdate()` and `findOrCreateStock()` no longer filter by `tenant_id` on lookup, preventing failed writes and duplicate stock records.

### 11. Checkout / Refund Stock Atomicity (Fixed)
- **Problem:** Checkout wrote to `stocks` and `branch_inventories` without a transaction (risk of inconsistency), used raw `save()` (race condition), and refund could skip branch-inventory restoration if the refund service threw.
- **Fix:**
  - Wrapped checkout stock writes in `DB::transaction()`.
  - Replaced `$bi->stock -= $qty; $bi->save()` with version-locked `$bi->updateStock(-$qty)`.
  - Restructured the refund flow: an `$alreadyRefunded` flag prevents re-refunding, branch inventory restoration uses `updateStock()` inside its own `DB::transaction()`, and order status is always updated to `refunded`.

### 12. Refunded Order Receipt Button & Status (Fixed)
- **Problem:** After an order was refunded, the Download Receipt button disappeared, and the receipt showed no refund indicator.
- **Fix:**
  - Download Receipt button now shows for both `closed` and `refunded` orders (desktop & mobile).
  - Added a blue `refunded` status badge.
  - The receipt now renders a **REFUNDED** dashed banner when the order status is `refunded`.

### 13. Receipt Service Type (Dine-in / Takeaway) Conditional Display (Fixed)
- **Problem:** The receipt always showed `Type: Dine-in` / `Type: Takeaway` even when those order types were disabled in Settings.
- **Fix:** `ReceiptBuilder` now reads `dine_in_enabled`, `takeaway_enabled`, and `table_management_enabled` settings and hides the service-type line (and table number) when disabled.

### 14. Order Creation 500 Error — Missing `table_number` Column (Fixed)
- **Problem:** Creating any order returned `500 Internal Server Error` with `SQLSTATE[42703]: Undefined column: column "table_number" of relation "pos_orders" does not exist`. The frontend sent `table_number`, but the database column never existed.
- **Fix:** Added a migration `add_table_number_to_pos_orders_table` that adds a nullable `table_number` column, and added it to the `PosOrder` model `$fillable`.

### 15. Dine-in / Takeaway / Table Management Strict Visibility (Fixed)
- **Problem:** Even when Admin/Superadmin disabled these features in Settings, they still appeared on the POS page.
- **Fix:**
  - Changed POS visibility conditions from `posSettings.xxx_enabled !== false` to `posSettings.xxx_enabled === true` — a missing/failed settings load now hides the feature instead of showing it (safe default).
  - Added `x-cloak` to the order-type section to prevent a pre-Alpine flash.
  - The settings API is read-only for all authenticated users, so the POS page can always load these toggles.

### 16. Mobile Sidebar Menu Items Not Clickable (Fixed)
- **Problem:** On mobile, the left sidebar menu items didn't respond to clicks — the sidebar had `pointer-events-none` in static classes and Tailwind's `pointer-events-none` rule won the CSS cascade over the open-state `pointer-events-auto`.
- **Fix:** Changed the sidebar open state to use `!pointer-events-auto` (Tailwind `!important` variant) so it reliably overrides the static `pointer-events-none` when the menu is open. Works in both LTR and RTL.

### 17. Category Pills Scroll Arrows — RTL Support (Fixed)
- **Problem:** On mobile, the product-group pills scroll arrows didn't work in RTL mode (and the left arrow never appeared). The scroll logic was LTR-only: in RTL, `scrollLeft` goes `0 → negative`, so positive `scrollBy` was clamped and `canScrollLeft = scrollLeft > 4` was never true.
- **Fix:** Made `pillScroller` RTL-aware:
  - `isRtl()` helper reads `document.documentElement.dir`.
  - `checkOverflow()` uses inverted thresholds in RTL.
  - `scrollLeft()` / `scrollRight()` reverse the scroll direction in RTL.
  - Added a `MutationObserver` on the `dir` attribute so arrow visibility re-evaluates instantly when RTL/LTR is toggled.

### 18. Company Logo Not Visible for Cashier Role (Fixed)
- **Problem:** Cashier (and other low-access-level) users didn't see the company logo in the sidebar; only managers/admins did.
- **Fix:** `GET /api/settings` and `GET /api/settings/{key}` were moved out of the `access.level:5` middleware group into the shared authenticated group, so all logged-in users can read settings (logo, company name, POS config). `POST /api/settings` (update) remains restricted to manager+.

### 19. Header Shows Full User Name, Responsive (Fixed)
- **Problem:** The header showed only the first name and hid the name entirely on mobile.
- **Fix:** The header now renders the full name (`first_name + last_name`), is visible on all screen sizes, and truncates gracefully with responsive max-widths (`max-w-[100px] sm:max-w-[140px] md:max-w-[180px] lg:max-w-[220px]`).

### 20. POS Performance & UX Optimizations (Implemented)
- **Parallel init loading** — `posCart.init()` now loads products, categories, fiscal items, tax rate, default customer, and stock summary in parallel via `Promise.all` (after settings resolve). Cut initial load from ~7 sequential API calls to ~2.
- **Keyboard shortcuts** — `F1` (Cash), `F2` (Card), `F3` (Check), `F4` (New Sale), `F8` (Print Receipt), `Esc` (Close Payment), `Shift+?` (Shortcuts Help). A `⌨` icon button and `Shift+?` open a help modal listing all shortcuts. Shortcuts are ignored while typing in any input field.
- **Fixed `screenWidth` bug** — `this.screenWidth` was undefined in all 14 components, breaking responsive grid breakpoints. Replaced with a global `Alpine.store('screen')` + single resize listener.
- **Removed redundant API call** — barcode scan no longer reloads the full product list after adding an item.
- **Promo discount cache** — promo discounts are cached for 10 seconds instead of re-fetching on every cart add.
- **Scanning race guard** — a `_scanning` flag prevents overlapping barcode-search requests during rapid scans.

### 21. Automated Receipt Printing — Thermal Printer Integration (Implemented)
- **Problem:** `PrintJobDispatcher` (ESC/POS thermal printing via local print proxy) existed but was only wired to the test-print UI, not to checkout. Receipts were only printable via the browser dialog.
- **Fix:**
  - Added `ReceiptBuilder::buildText()` — generates a plain-text ESC/POS-ready receipt (42/32 column layout for 80mm/58mm paper) alongside the existing HTML receipt.
  - Added `dispatchAutoPrint()` in `PosController::checkout()` — after an order is completed, the receipt is sent to the configured thermal printer via `PrintJobDispatcher`.
  - Printing is fully fault-tolerant: if no printer is configured, the proxy is offline, or printing throws, the order still completes and the receipt still shows on screen (failures are logged as warnings only).
  - Added `F8` keyboard shortcut to print the on-screen receipt.

### 22. Purchases Module (Implemented)
- Complete supplier management with CRUD operations
- Purchase orders with item tracking, payments (partial/full), and status workflow
- Purchase returns management
- Report dashboard: summary by supplier, by product, monthly trends, outstanding payments
- Supplier filter, date range search, and status badges
- Full currency support via dynamic Alpine store

### 23. Income & Expenses Module (Implemented)
- **Dashboard** — summary cards (Total Income, Total Expenses, Net Profit/Loss), monthly bar chart, top categories, recent entries
- **Income / Expenses tabs** — full CRUD with search, date-range/category filters, pagination, dynamic payment methods
- **Categories tab** — income & expense categories with color picker, inline add/edit/delete
- **Reports tab** — date-range filtered summary, by-category breakdown
- **POS Sync** — date-range selection modal to auto-sync completed POS sales as income entries grouped by actual payment method (Cash, Card, Check)
- **Default categories**: 4 income (Sales, Service, Interest, Other) + 8 expense (Rent, Salaries, Supplies, Marketing, Maintenance, Travel, Taxes, Other)

### 24. Barcode Module (Implemented)
- **Barcode List** — searchable/filterable table with barcode image (JsBarcode SVG rendering), product, SKU, type, primary/active status
- **Generate Barcode** — select product → choose type (CODE-128/EAN-13/UPC-A) → auto-generate with duplicate prevention
- **Add/Edit Barcode** — manual entry or scanner input, auto-populates existing barcode on product selection
- **Deactivate/Activate** — toggle barcode status without deleting (preserves history)
- **Copy** — clipboard copy with "Copied!" visual feedback
- **Print Labels** — select barcodes via checkboxes → preview modal with label size selector → print via ESC/POS proxy
- **Barcode Settings tab** — default type, auto-generate toggle, label content toggles (name/price/SKU/company)
- **Product Integration** — barcode field + Generate button in product create/edit modal; saved automatically
- **POS Integration** — hardware barcode scanner → keyboard input → Enter → product found → added to cart (smart 50ms debounce for barcodes, 300ms for text search)
- **Fixed bugs:** ProductResource `pluck('barcode')` → `map(fn($b) => ...)`, AroniumImporter column name `'barcode'` → `'value'`

### 25. Payment Methods Management (Implemented)
- **Settings → Payment Methods tab** — full CRUD table with Name, Code, Shortcut Key, Quick Pay toggle, Enabled toggle
- Dynamic dropdowns: Income/Expenses payment methods fetched from `payment_types` table (tenant-scoped + global)
- Deduplication: shared (tenant_id=NULL) and tenant-specific records merged by unique name
- POS quick-pay buttons (Cash/Card/Check) tied to `is_quick_payment` flag in settings

### 26. Currency Display — Dynamic & LocalStorage Caching (Fixed)
- **Problem:** Currency symbol was hardcoded as `$` in purchase blade template (14+ places), `viewPurchase()` alert, and various tables
- **Fix:** All currency displays now use `Alpine.store('currency')?.symbol` with `localStorage` caching for instant correct symbol on page load without waiting for API

### 27. Sidebar — Global Thin Scrollbar (Implemented)
- **Problem:** Browser-default thick scrollbar appeared on sidebar nav when content overflowed
- **Fix:** Global CSS (*4px thin scrollbar* with transparent track and subtle thumb) applied via `app.css` — works on every scrollable element across the entire app in both light and dark modes. Chrome, Firefox, and Safari supported.

### 28. POS Checkout — Instant Payment Modal (Fixed)
- **Problem:** Clicking a payment method button called `loadDefaultStocks()` before opening the payment modal, causing a noticeable delay (stock validation already happens server-side)
- **Fix:** Removed `loadDefaultStocks()` from `openPayment()` — payment modal opens instantly. Promotion calculation cached for 10 seconds even with items in cart.

### 29. POS Stock — Instant Decrement After Sale (Fixed)
- **Problem:** Stock count on product cards didn't update after a sale until page refresh
- **Fix:** After successful payment, sold item quantities are subtracted from `stockMap` (Alpine-reactive), so product cards update immediately without refresh.

### 30. Refund Stock Correction — Double Adjustment (Fixed)
- **Problem:** `CheckoutService::processRefund()` already increments warehouse stock, but `PosController::refund()` was doing a second decrement (wrong direction). Missing stock records also threw errors.
- **Fix:** Removed duplicate warehouse stock adjustment from PosController. Stock record auto-creation on missing records in `CheckoutService`. Refund now handles missing stock gracefully.

### 31. Order View — Customer Name (Fixed)
- **Problem:** Viewing an order always showed "Walk-in Customer" regardless of actual customer
- **Fix:** `PosController::show()` now eager-loads `->with('customer')` so the API response includes the customer object

### 32. Global Thin Scrollbar (Implemented)
- All scrollable elements across the entire app now use a 4px subtle scrollbar (Chrome/Edge via `::-webkit-scrollbar`, Firefox via `scrollbar-width: thin`, Safari compatible). Dark mode support with appropriate color tints. Applied globally via `app.css` — no per-element classes needed.

---

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | Laravel 12 (PHP 8.2+) |
| Frontend | Blade templates, Alpine.js 3, Tailwind CSS v4 |
| Database | PostgreSQL |
| Build Tool | Vite 7 |
| Authentication | Laravel Sanctum (API tokens) |
| Icons | SVG inline (no icon library dependency) |

---

## Requirements

- PHP 8.2 or higher
- PostgreSQL 14 or higher
- Composer 2.x
- Node.js 18+ and npm 9+
- PHP extensions: `pdo_pgsql`, `bcmath`, `curl`, `fileinfo`, `mbstring`, `xml`

---

## Installation

```bash
# Clone the repository
git clone https://github.com/your-org/dstar-pos.git
cd dstar-pos

# Install PHP dependencies
composer install

# Install JavaScript dependencies
npm install

# Copy environment file
cp .env.example .env

# Configure your database in .env
# DB_CONNECTION=pgsql
# DB_HOST=127.0.0.1
# DB_PORT=5432
# DB_DATABASE=dstar_pos
# DB_USERNAME=postgres
# DB_PASSWORD=your_password

# Generate application key
php artisan key:generate

# Run database migrations and seed
php artisan migrate --seed

# Build frontend assets
npm run build

# Start the development server
php artisan serve --port=8000
```

---

## Project Structure

```
.
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Api/          # REST API controllers
│   │   └── Middleware/        # SetActiveBranch, SetTenant, RBAC
│   ├── Models/                # Eloquent models
│   ├── Services/              # Business logic services
│   └── Providers/             # Service providers
├── resources/
│   ├── views/                 # Blade templates
│   │   ├── layouts/           # Main layout (sidebar, header)
│   │   ├── pos/               # POS page + Orders
│   │   ├── components/        # Reusable UI components
│   │   └── {module}/          # Module pages
│   ├── css/                   # Tailwind CSS + RTL styles
│   └── js/                    # Alpine.js components
├── routes/
│   └── api.php                # API route definitions
├── database/
│   └── migrations/            # Database migrations
└── public/
    └── build/                 # Compiled Vite assets
```

---

## API Endpoints

### Authentication
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/auth/login` | User login |
| POST | `/api/auth/logout` | User logout |
| GET | `/api/auth/me` | Current user info |

### POS
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/products` | List products |
| GET | `/api/product-groups` | List categories |
| GET | `/api/customers` | List/search customers |
| POST | `/api/customers` | Create customer |
| POST | `/api/orders` | Create order |
| PUT | `/api/orders/{id}` | Update order |
| GET | `/api/orders/{id}` | Get order details |
| POST | `/api/orders/{id}/checkout` | Checkout/complete order |
| POST | `/api/orders/{id}/refund` | Refund order |
| GET | `/api/stock/pos-summary` | POS stock summary |

### Reports
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/reports/sales-summary` | Sales report |
| GET | `/api/reports/best-selling` | Best selling products |
| GET | `/api/reports/customer-analytics` | Customer report |
| GET | `/api/reports/customer-sales` | Customer detail |
| GET | `/api/reports/employee-sales` | Employee detail |
| GET | `/api/reports/tax-report` | Tax report |

### Settings
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/settings` | Get all settings |
| POST | `/api/settings` | Save settings |

### Admin
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET/POST | `/api/users` | User management |
| GET/POST | `/api/roles` | Role management |
| GET/POST | `/api/branches` | Branch management |
| GET/POST | `/api/taxes` | Tax management |
| GET/POST | `/api/printers` | Printer management |
| GET/POST | `/api/promotions` | Promotion management |
| GET/POST | `/api/loyalty` | Loyalty card management |
| GET/POST | `/api/fiscal-items` | Fiscal item management |
| GET | `/api/activity` | Activity log |

### Purchases
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET/POST | `/api/suppliers` | Supplier CRUD |
| GET/POST | `/api/purchases` | Purchase CRUD |
| POST | `/api/purchases/{id}/return` | Purchase return |
| GET | `/api/reports/purchases/summary` | Purchases dashboard |
| GET | `/api/reports/purchases/by-supplier` | By supplier report |
| GET | `/api/reports/purchases/by-product` | By product report |
| GET | `/api/reports/purchases/monthly` | Monthly report |
| GET | `/api/reports/purchases/outstanding` | Outstanding payments |

### Income & Expenses
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET/POST | `/api/income-expenses` | Entry CRUD |
| GET/POST | `/api/income-expense-categories` | Category CRUD |
| POST | `/api/income-expenses/sync-pos-sales` | Sync POS sales as income |
| GET | `/api/reports/income-expenses/summary` | Dashboard summary |
| GET | `/api/reports/income-expenses/by-category` | By category report |
| GET | `/api/reports/income-expenses/monthly` | Monthly breakdown |

### Barcodes
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET/POST | `/api/barcodes` | List, create barcodes |
| PUT/DELETE | `/api/barcodes/{id}` | Update, deactivate barcode |
| POST | `/api/barcodes/generate` | Auto-generate barcode |
| GET | `/api/barcodes/scan` | Scan/lookup product by barcode |
| POST | `/api/barcodes/print` | Print barcode labels |
| GET | `/api/barcodes/products-without` | Products without barcodes |

### Payment Types
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/payment-types/quick-list` | Enabled quick-payment types |
| GET | `/api/payment-types/all` | All payment types |
| GET/POST | `/api/payment-types` | Payment type CRUD |

---

## Multi-Branch Architecture

The system supports two operating modes:

**Single Company Mode**: All orders, products, and inventory belong to a single location. The branch selector is hidden.

**Multi-Branch Mode**: Each branch has its own inventory, orders, and settings.
- Admin users can switch between branches via the header dropdown
- Branch selection filters all data (orders, stock, reports)
- Non-admin users see only their assigned branch(es)
- Branch-level stock tracking via `BranchInventory`

The `X-Active-Branch` HTTP header is sent with every API request to maintain branch context across stateless API calls. The `SetActiveBranch` middleware processes this header and stores the active branch in the session.

---

## Responsive Design

| Viewport | Behavior |
|----------|----------|
| Mobile (under 640px) | Card layouts, slide-in sidebar/cart, 2-column product grid |
| Tablet (640-1024px) | 3-column product grid, wrapped tabs |
| Desktop (over 1024px) | Full sidebar, table layouts, multi-column grids |

---

## License

Proprietary. All rights reserved. (C) D Star Company.

---

## Support

For issues, feature requests, or questions:
- Open an issue on GitHub
- Contact: admin@dstar.com
