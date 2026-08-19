<?php

use App\Http\Controllers\Api\{
    AuthController,
    ProductController,
    ProductGroupController,
    CustomerController,
    TaxController,
    PosController,
    DocumentController,
    PaymentController,
    StockController,
    SupplierController,
    PurchaseController,
    PurchaseReturnController,
    PurchaseReportController,
    IncomeExpenseController,
    IncomeExpenseCategoryController,
    IncomeExpenseReportController,
    BarcodeController,
    PaymentTypeController,
    WarehouseController,
    UserController,
    PriceListController,
    PromotionController,
    PromotionItemController,
    CompanyController,
    LoyaltyController,
    ReportController,
    DashboardController,
    PrinterController,
    SettingsController,
    FloorPlanController,
    FiscalItemController,
    CashRegisterController,
    ShiftController,
};

// ---- Public Routes ----
Route::post('auth/login', [AuthController::class, 'login'])->middleware('throttle:login');
Route::post('auth/pin-login', [AuthController::class, 'pinLogin'])->middleware('throttle:pin');
Route::post('auth/employee-pin-login', [AuthController::class, 'employeePinLogin'])->middleware('throttle:pin');

// ---- Authenticated Routes ----
Route::middleware(['auth:sanctum', 'user.enabled', 'throttle:api', 'track.activity'])->group(function () {

    // Auth
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::get('auth/me', [AuthController::class, 'me']);
    Route::post('auth/setup-pin', [AuthController::class, 'setupPin']);
    Route::put('auth/change-pin', [AuthController::class, 'changePin']);
    Route::post('auth/users/{userId}/reset-pin', [AuthController::class, 'resetPin'])->middleware('access.level:5');

    // Settings (read-only for all authenticated users — needed for logo/company/POS settings)
    Route::get('settings', [SettingsController::class, 'index']);
    Route::get('settings/{key}', [SettingsController::class, 'getByKey']);

    // Dashboard
    Route::get('dashboard', [DashboardController::class, 'index']);

        // Products
        Route::get('products/next-code', [ProductController::class, 'nextCode']);
        Route::get('products', [ProductController::class, 'index']);
        Route::get('products/{product}', [ProductController::class, 'show']);
        Route::post('products', [ProductController::class, 'store'])->middleware('access.level:5');
        Route::put('products/{product}', [ProductController::class, 'update'])->middleware('access.level:5');
        Route::delete('products/{product}', [ProductController::class, 'destroy'])->middleware('access.level:9');
        Route::get('product-groups', [ProductGroupController::class, 'index']);
        Route::get('product-groups/{productGroup}', [ProductGroupController::class, 'show']);
        Route::post('product-groups', [ProductGroupController::class, 'store'])->middleware('access.level:5');
        Route::put('product-groups/{productGroup}', [ProductGroupController::class, 'update'])->middleware('access.level:5');
        Route::delete('product-groups/{productGroup}', [ProductGroupController::class, 'destroy'])->middleware('access.level:9');

// Customers
Route::post('customers/{customer}/payment', [CustomerController::class, 'addPayment']);
Route::get('customers/{customer}/statement', [CustomerController::class, 'statement']);
Route::get('customers/{customer}/payments', [CustomerController::class, 'payments']);
Route::get('customers', [CustomerController::class, 'index']);
Route::get('customers/{customer}', [CustomerController::class, 'show']);
Route::post('customers', [CustomerController::class, 'store'])->middleware('access.level:5');
Route::put('customers/{customer}', [CustomerController::class, 'update'])->middleware('access.level:5');
Route::delete('customers/{customer}', [CustomerController::class, 'destroy'])->middleware('access.level:9');
    Route::post('customers/quick', [CustomerController::class, 'quickStore']);

    // POS
    Route::get('orders', [PosController::class, 'index']);
    Route::get('orders/hold-list', [PosController::class, 'holdOrders']);
    Route::post('orders', [PosController::class, 'store']);
    Route::get('orders/{order}', [PosController::class, 'show']);
    Route::post('orders/{order}/items', [PosController::class, 'addItem']);
    Route::delete('orders/{order}/items/{item}', [PosController::class, 'removeItem']);
    Route::post('orders/{order}/items/{item}/void', [PosController::class, 'voidItem']);
    Route::post('orders/{order}/refund', [PosController::class, 'refund'])->middleware('throttle:refund');
    Route::post('orders/{order}/checkout', [PosController::class, 'checkout'])->middleware('throttle:checkout');
    Route::post('orders/{order}/close', [PosController::class, 'closeOrder']);
    Route::post('orders/{order}/hold', [PosController::class, 'holdOrder']);
    Route::post('orders/{order}/resume', [PosController::class, 'resumeOrder']);
    Route::post('orders/{order}/cancel', [PosController::class, 'cancelOrder']);
    Route::post('orders/{order}/transfer', [PosController::class, 'transferItems']);
    Route::get('receipts/{order}', [PosController::class, 'receipt']);

    // Cash Register
    Route::get('cash-register/status', [CashRegisterController::class, 'status']);
    Route::post('cash-register/open', [CashRegisterController::class, 'open']);
    Route::post('cash-register/close', [CashRegisterController::class, 'close']);
    Route::post('cash-register/cash-in-out', [CashRegisterController::class, 'cashInOut']);
    Route::get('cash-register/history', [CashRegisterController::class, 'history']);
    Route::get('cash-register/{id}', [CashRegisterController::class, 'show']);

    // Shifts (write = admin)
    Route::get('shifts', [ShiftController::class, 'index']);
    Route::get('shifts/{shift}', [ShiftController::class, 'show']);
    Route::post('shifts', [ShiftController::class, 'store'])->middleware('access.level:9');
    Route::put('shifts/{shift}', [ShiftController::class, 'update'])->middleware('access.level:9');
    Route::delete('shifts/{shift}', [ShiftController::class, 'destroy'])->middleware('access.level:9');

    // Documents
    Route::get('documents', [DocumentController::class, 'index']);
    Route::post('documents', [DocumentController::class, 'store'])->middleware('access.level:5');
    Route::get('documents/{document}', [DocumentController::class, 'show']);
    Route::put('documents/{document}', [DocumentController::class, 'update'])->middleware('access.level:5');
    Route::get('documents/by-date', [DocumentController::class, 'getByDate']);
    Route::get('documents/by-customer/{customer}', [DocumentController::class, 'getByCustomer']);
    Route::get('documents/by-type/{documentType}', [DocumentController::class, 'getByType']);

    // Payments
    Route::get('payments', [PaymentController::class, 'index']);
    Route::post('payments', [PaymentController::class, 'store'])->middleware('access.level:5');
    Route::get('payments/{payment}', [PaymentController::class, 'show']);

    // Stock
    Route::post('stock/transfer', [StockController::class, 'transfer'])->middleware('access.level:5');
    Route::post('stock/bulk-update', [StockController::class, 'bulkUpdate'])->middleware('access.level:5');
    Route::get('stock/pos-summary', [StockController::class, 'posSummary']);
    Route::get('stock', [StockController::class, 'index']);
    Route::get('stock/{stock}', [StockController::class, 'show']);
    Route::post('stock/adjust', [StockController::class, 'adjust'])->middleware('access.level:5');
    Route::get('stock/movements', [StockController::class, 'movementHistory']);
    Route::post('stock/inventory-count', [StockController::class, 'inventoryCount'])->middleware('access.level:5');

    // Warehouses
    Route::get('warehouses', [WarehouseController::class, 'index']);
    Route::get('warehouses/{warehouse}', [WarehouseController::class, 'show']);
    Route::post('warehouses', [WarehouseController::class, 'store'])->middleware('access.level:5');
    Route::put('warehouses/{warehouse}', [WarehouseController::class, 'update'])->middleware('access.level:5');
    Route::delete('warehouses/{warehouse}', [WarehouseController::class, 'destroy'])->middleware('access.level:9');

        // Purchases
        Route::get('purchases/next-number', [PurchaseController::class, 'nextNumber']);
Route::post('purchases/{purchase}/receive', [PurchaseController::class, 'receive'])->middleware('access.level:5');
Route::post('purchases/{purchase}/mark-paid', [PurchaseController::class, 'markPaid'])->middleware('access.level:5');
Route::post('purchases/{purchase}/payment', [PurchaseController::class, 'addPayment'])->middleware('access.level:5');
Route::get('purchases', [PurchaseController::class, 'index']);
Route::get('purchases/{purchase}', [PurchaseController::class, 'show']);
Route::post('purchases', [PurchaseController::class, 'store'])->middleware('access.level:5');
Route::put('purchases/{purchase}', [PurchaseController::class, 'update'])->middleware('access.level:5');
Route::delete('purchases/{purchase}', [PurchaseController::class, 'destroy'])->middleware('access.level:9');
Route::get('purchase-returns', [PurchaseReturnController::class, 'index']);
Route::post('purchase-returns', [PurchaseReturnController::class, 'store'])->middleware('access.level:5');
Route::get('purchase-returns/{purchaseReturn}', [PurchaseReturnController::class, 'show']);
Route::get('suppliers/quick-list', [SupplierController::class, 'quickList']);
Route::get('suppliers/{supplier}/statement', [SupplierController::class, 'statement']);
Route::get('suppliers/{supplier}/payments', [SupplierController::class, 'payments']);
Route::get('suppliers', [SupplierController::class, 'index']);
Route::get('suppliers/{supplier}', [SupplierController::class, 'show']);
Route::post('suppliers', [SupplierController::class, 'store'])->middleware('access.level:5');
Route::put('suppliers/{supplier}', [SupplierController::class, 'update'])->middleware('access.level:5');
Route::delete('suppliers/{supplier}', [SupplierController::class, 'destroy'])->middleware('access.level:9');

        // Purchase Reports
        Route::prefix('reports/purchases')->group(function () {
            Route::get('summary', [PurchaseReportController::class, 'summary']);
            Route::get('by-supplier', [PurchaseReportController::class, 'bySupplier']);
            Route::get('by-product', [PurchaseReportController::class, 'byProduct']);
            Route::get('monthly', [PurchaseReportController::class, 'monthly']);
            Route::get('outstanding-payments', [PurchaseReportController::class, 'outstandingPayments']);
        });

        // Income & Expenses
        Route::get('payment-types/quick-list', [PaymentTypeController::class, 'index']);
        Route::get('payment-types/all', [PaymentTypeController::class, 'all']);
        Route::post('payment-types/reorder', [PaymentTypeController::class, 'reorder'])->middleware('access.level:9');
        Route::get('payment-types', [PaymentTypeController::class, 'index']);
        Route::get('payment-types/{paymentType}', [PaymentTypeController::class, 'show']);
        Route::post('payment-types', [PaymentTypeController::class, 'store'])->middleware('access.level:9');
        Route::put('payment-types/{paymentType}', [PaymentTypeController::class, 'update'])->middleware('access.level:9');
        Route::delete('payment-types/{paymentType}', [PaymentTypeController::class, 'destroy'])->middleware('access.level:9');
        Route::post('income-expenses/sync-pos-sales', [IncomeExpenseController::class, 'syncPosSales'])->middleware('access.level:5');
        Route::get('income-expenses', [IncomeExpenseController::class, 'index']);
        Route::get('income-expenses/{incomeExpense}', [IncomeExpenseController::class, 'show']);
        Route::post('income-expenses', [IncomeExpenseController::class, 'store'])->middleware('access.level:5');
        Route::put('income-expenses/{incomeExpense}', [IncomeExpenseController::class, 'update'])->middleware('access.level:5');
        Route::delete('income-expenses/{incomeExpense}', [IncomeExpenseController::class, 'destroy'])->middleware('access.level:5');
        Route::get('income-expense-categories', [IncomeExpenseCategoryController::class, 'index']);
        Route::get('income-expense-categories/{category}', [IncomeExpenseCategoryController::class, 'show']);
        Route::post('income-expense-categories', [IncomeExpenseCategoryController::class, 'store'])->middleware('access.level:5');
        Route::put('income-expense-categories/{category}', [IncomeExpenseCategoryController::class, 'update'])->middleware('access.level:5');
        Route::delete('income-expense-categories/{category}', [IncomeExpenseCategoryController::class, 'destroy'])->middleware('access.level:5');

        // Barcodes
        Route::get('barcodes/products-without', [BarcodeController::class, 'productsWithoutBarcode']);
        Route::post('barcodes/generate', [BarcodeController::class, 'generate'])->middleware('access.level:5');
        Route::post('barcodes/bulk-generate', [BarcodeController::class, 'bulkGenerate'])->middleware('access.level:5');
        Route::get('barcodes/scan', [BarcodeController::class, 'scan']);
        Route::post('barcodes/print', [BarcodeController::class, 'print'])->middleware('access.level:5');
        Route::get('barcodes', [BarcodeController::class, 'index']);
        Route::get('barcodes/{barcode}', [BarcodeController::class, 'show']);
        Route::post('barcodes', [BarcodeController::class, 'store'])->middleware('access.level:5');
        Route::put('barcodes/{barcode}', [BarcodeController::class, 'update'])->middleware('access.level:5');
        Route::delete('barcodes/{barcode}', [BarcodeController::class, 'destroy'])->middleware('access.level:5');

        // Income & Expense Reports
        Route::prefix('reports/income-expenses')->group(function () {
            Route::get('summary', [IncomeExpenseReportController::class, 'summary']);
            Route::get('by-category', [IncomeExpenseReportController::class, 'byCategory']);
            Route::get('monthly', [IncomeExpenseReportController::class, 'monthly']);
        });

        // Taxes
        Route::get('taxes', [TaxController::class, 'index']);
        Route::post('taxes', [TaxController::class, 'store'])->middleware('access.level:9');
        Route::put('taxes/{tax}', [TaxController::class, 'update'])->middleware('access.level:9');
        Route::delete('taxes/{tax}', [TaxController::class, 'destroy'])->middleware('access.level:9');

    // Pricing
    Route::get('price-lists', [PriceListController::class, 'index']);
    Route::get('price-lists/{priceList}', [PriceListController::class, 'show']);
    Route::post('price-lists', [PriceListController::class, 'store'])->middleware('access.level:5');
    Route::put('price-lists/{priceList}', [PriceListController::class, 'update'])->middleware('access.level:5');
    Route::delete('price-lists/{priceList}', [PriceListController::class, 'destroy'])->middleware('access.level:5');
    Route::post('price-lists/{priceList}/items', [PriceListController::class, 'addItem'])->middleware('access.level:5');
    Route::delete('price-lists/{priceList}/items/{item}', [PriceListController::class, 'removeItem'])->middleware('access.level:5');

    // Promotions
    Route::get('promotions', [PromotionController::class, 'index']);
    Route::get('promotions/{promotion}', [PromotionController::class, 'show']);
    Route::post('promotions', [PromotionController::class, 'store'])->middleware('access.level:9');
    Route::put('promotions/{promotion}', [PromotionController::class, 'update'])->middleware('access.level:9');
    Route::delete('promotions/{promotion}', [PromotionController::class, 'destroy'])->middleware('access.level:9');
    Route::post('promotions/{promotion}/toggle', [PromotionController::class, 'toggleEnabled'])->middleware('access.level:9');
    Route::apiResource('promotions.items', PromotionItemController::class)->middleware('access.level:9');

    // Company
    Route::get('company', [CompanyController::class, 'index']);
    Route::get('company/{company}', [CompanyController::class, 'show']);
    Route::post('company', [CompanyController::class, 'store'])->middleware('access.level:5');
    Route::put('company/{company}', [CompanyController::class, 'update'])->middleware('access.level:5');

    // Loyalty
    Route::get('loyalty', [LoyaltyController::class, 'index']);
    Route::get('loyalty/{card}', [LoyaltyController::class, 'show']);
    Route::post('loyalty', [LoyaltyController::class, 'store'])->middleware('access.level:9');
    Route::delete('loyalty/{card}', [LoyaltyController::class, 'destroy'])->middleware('access.level:9');
    Route::post('loyalty/{card}/earn', [LoyaltyController::class, 'earnPoints'])->middleware('access.level:5');
    Route::post('loyalty/{card}/redeem', [LoyaltyController::class, 'redeemPoints'])->middleware('access.level:5');
    Route::get('loyalty/{card}/transactions', [LoyaltyController::class, 'transactionHistory']);

    // Floor Plans
    Route::get('floor-plans', [FloorPlanController::class, 'index']);
    Route::get('floor-plans/{floorPlan}', [FloorPlanController::class, 'show']);
    Route::post('floor-plans', [FloorPlanController::class, 'store'])->middleware('access.level:5');
    Route::put('floor-plans/{floorPlan}', [FloorPlanController::class, 'update'])->middleware('access.level:5');
    Route::delete('floor-plans/{floorPlan}', [FloorPlanController::class, 'destroy'])->middleware('access.level:5');
    Route::post('floor-plans/{floorPlan}/tables', [FloorPlanController::class, 'addTable'])->middleware('access.level:5');
    Route::put('floor-plans/{floorPlan}/tables/{table}', [FloorPlanController::class, 'updateTable'])->middleware('access.level:5');
    Route::delete('floor-plans/{floorPlan}/tables/{table}', [FloorPlanController::class, 'removeTable'])->middleware('access.level:5');

    // Fiscal Items
    Route::apiResource('fiscal-items', FiscalItemController::class)->middleware('access.level:9');

    // Modules list (public to authenticated)
    Route::get('modules', [\App\Http\Controllers\Api\RoleController::class, 'modules']);

    // ---- Manager+ Routes (access_level >= 5) ----
    Route::middleware('access.level:5')->group(function () {
        // Users
        Route::apiResource('users', UserController::class);
        Route::put('users/{user}/access-level', [UserController::class, 'updateAccessLevel'])
            ->middleware('access.level:9');

        // Reports
        Route::get('reports/sales-summary', [ReportController::class, 'salesSummary']);
        Route::get('reports/best-selling', [ReportController::class, 'bestSellingItems']);
        Route::get('reports/customers', [ReportController::class, 'customerAnalytics']);
        Route::get('reports/customer-sales', [ReportController::class, 'customerSalesDetail']);
        Route::get('reports/discounts', [ReportController::class, 'discountReport']);
        Route::get('reports/taxes', [ReportController::class, 'taxReport']);
        Route::get('reports/payments', [ReportController::class, 'paymentTypeBreakdown']);
        Route::get('reports/employees', [ReportController::class, 'employeeSalesReport']);
        Route::get('reports/employee-sales', [ReportController::class, 'employeeSalesDetail']);
Route::get('reports/profit-margin', [ReportController::class, 'profitMarginReport']);
Route::get('reports/inventory-valuation', [ReportController::class, 'inventoryValuation']);
Route::get('reports/profit-loss', [ReportController::class, 'profitLoss']);
Route::get('reports/customer-due', [ReportController::class, 'customerDue']);

        // Printers
        Route::apiResource('printers', PrinterController::class);
        Route::post('printers/{printer}/test', [PrinterController::class, 'testPrint']);

        // Settings (update only — manager+)
        Route::post('settings', [SettingsController::class, 'update']);

        // Activity Logs
        Route::get('activity', [\App\Http\Controllers\Api\ActivityController::class, 'index'])
            ->middleware('access.level:5');
        Route::get('activity/summary', [\App\Http\Controllers\Api\ActivityController::class, 'summary'])
            ->middleware('access.level:5');
        Route::get('activity/user/{userId}', [\App\Http\Controllers\Api\ActivityController::class, 'userActivity'])
            ->middleware('access.level:5');

        // Roles & Permissions (Admin only)
        Route::apiResource('roles', \App\Http\Controllers\Api\RoleController::class)
            ->middleware('access.level:9');
    });

    // Branches (index + switch available to all for branch selection; management = admin)
    Route::get('branches', [\App\Http\Controllers\Api\TenantController::class, 'index']);
    Route::post('branches/{branch}/switch', [\App\Http\Controllers\Api\TenantController::class, 'switch']);
    Route::get('branches/{branch}', [\App\Http\Controllers\Api\TenantController::class, 'show'])->middleware('access.level:9');
    Route::post('branches', [\App\Http\Controllers\Api\TenantController::class, 'store'])->middleware('access.level:9');
    Route::put('branches/{branch}', [\App\Http\Controllers\Api\TenantController::class, 'update'])->middleware('access.level:9');
    Route::delete('branches/{branch}', [\App\Http\Controllers\Api\TenantController::class, 'destroy'])->middleware('access.level:9');
});
