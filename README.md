# D Star Company -- Point of Sale (POS) System

A modern, responsive, multi-branch Point of Sale application built with Laravel, Tailwind CSS, and Alpine.js. Designed for restaurants, cafes, retail stores, and multi-location businesses.

---

## Features

### Point of Sale (POS)
- **Product Grid** -- customizable grid layout (2-6 columns) with category filtering
- **Cart Management** -- add/remove items, adjust quantities, apply discounts (flat/percentage/promo)
- **Order Types** -- Dine-in, Takeaway, and Table Management (all toggleable from Settings)
- **Customer Selection** -- search existing customers or create walk-in customers inline
- **Payment Processing** -- Cash, Card, and Check payment methods
- **Receipt Generation** -- printable receipts with configurable header/footer
- **Order Loading** -- load and edit existing orders
- **Virtual Keyboard** -- on-screen keyboard for touch devices

### Orders
- Full order history with search and status filtering
- Order status management (Open, Closed, Cancelled)
- View, Complete, and Refund orders
- Download receipts as PDF
- Responsive: card-based layout on mobile, table on desktop

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

### Reports & Analytics
- **Sales Summary** -- revenue totals, order counts, avg order, tax collected, revenue trend chart
- **Best Selling** -- products ranked by revenue with gold/silver/bronze badges
- **Customer Analytics** -- top customers by spending, total orders
- **Customer Detail** -- per-customer drill-down: orders, top products, receipts
- **Employee Detail** -- per-employee sales breakdown: orders, items, top products
- **Tax Report** -- tax collected by date with totals
- Date range filtering across all reports
- Branch filtering in multi-branch mode
- Pagination support

### Settings
- **General** -- Company name, logo, currency, contact info
- **POS** -- Grid columns/rows, tax rate, rounding rules, sound effects, payment confirmation, Dine-in/Takeaway/Table toggles
- **Receipt** -- Auto-print settings, header/footer customization
- **Notifications** -- Duration and position configuration
- **System** -- Single Company vs Multi-Branch mode

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
- **Activity Log** -- audit trail of all system actions

### UI/UX
- **Fully Responsive** -- works on desktop, tablet, and mobile
- **Dark Mode** -- full dark mode support across all modules
- **Mobile-Optimized** -- card-based mobile layouts, collapsible sidebar, slide-in cart panel
- **Touch-Friendly** -- 44px minimum touch targets, iOS input zoom prevention
- **RTL Support** -- right-to-left layout for Arabic/Hebrew languages
- **Toast Notifications** -- success/error feedback with configurable position

---

## Recent Updates & Bug Fixes

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
