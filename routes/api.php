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
};

// ---- Public Routes ----
Route::post('auth/login', [AuthController::class, 'login'])->middleware('throttle:login');
Route::post('auth/pin-login', [AuthController::class, 'pinLogin'])->middleware('throttle:login');

// ---- Authenticated Routes ----
Route::middleware(['auth:sanctum', 'user.enabled', 'throttle:api', 'track.activity'])->group(function () {

    // Auth
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::get('auth/me', [AuthController::class, 'me']);
    Route::put('auth/change-pin', [AuthController::class, 'changePin']);

    // Settings (read-only for all authenticated users — needed for logo/company/POS settings)
    Route::get('settings', [SettingsController::class, 'index']);
    Route::get('settings/{key}', [SettingsController::class, 'getByKey']);

    // Dashboard
    Route::get('dashboard', [DashboardController::class, 'index']);

        // Products
        Route::apiResource('products', ProductController::class);
    Route::apiResource('product-groups', ProductGroupController::class);

    // Customers
    Route::apiResource('customers', CustomerController::class);
    Route::post('customers/quick', [CustomerController::class, 'quickStore']);

    // POS
    Route::get('orders', [PosController::class, 'index']);
    Route::post('orders', [PosController::class, 'store']);
    Route::get('orders/{order}', [PosController::class, 'show']);
    Route::post('orders/{order}/items', [PosController::class, 'addItem']);
    Route::delete('orders/{order}/items/{item}', [PosController::class, 'removeItem']);
    Route::post('orders/{order}/items/{item}/void', [PosController::class, 'voidItem']);
    Route::post('orders/{order}/refund', [PosController::class, 'refund']);
    Route::post('orders/{order}/checkout', [PosController::class, 'checkout']);
    Route::post('orders/{order}/close', [PosController::class, 'closeOrder']);
    Route::post('orders/{order}/transfer', [PosController::class, 'transferItems']);
    Route::get('receipts/{order}', [PosController::class, 'receipt']);

    // Documents
    Route::get('documents', [DocumentController::class, 'index']);
    Route::post('documents', [DocumentController::class, 'store']);
    Route::get('documents/{document}', [DocumentController::class, 'show']);
    Route::put('documents/{document}', [DocumentController::class, 'update']);
    Route::get('documents/by-date', [DocumentController::class, 'getByDate']);
    Route::get('documents/by-customer/{customer}', [DocumentController::class, 'getByCustomer']);
    Route::get('documents/by-type/{documentType}', [DocumentController::class, 'getByType']);

    // Payments
    Route::get('payments', [PaymentController::class, 'index']);
    Route::post('payments', [PaymentController::class, 'store']);
    Route::get('payments/{payment}', [PaymentController::class, 'show']);

    // Stock
    Route::post('stock/transfer', [StockController::class, 'transfer']);
    Route::post('stock/bulk-update', [StockController::class, 'bulkUpdate']);
    Route::get('stock/pos-summary', [StockController::class, 'posSummary']);
    Route::get('stock', [StockController::class, 'index']);
    Route::get('stock/{stock}', [StockController::class, 'show']);
    Route::post('stock/adjust', [StockController::class, 'adjust']);
    Route::get('stock/movements', [StockController::class, 'movementHistory']);
    Route::post('stock/inventory-count', [StockController::class, 'inventoryCount']);

    // Warehouses
    Route::apiResource('warehouses', WarehouseController::class);

    // Taxes
    Route::apiResource('taxes', TaxController::class);

    // Pricing
    Route::apiResource('price-lists', PriceListController::class);
    Route::post('price-lists/{priceList}/items', [PriceListController::class, 'addItem']);
    Route::delete('price-lists/{priceList}/items/{item}', [PriceListController::class, 'removeItem']);

    // Promotions
    Route::apiResource('promotions', PromotionController::class);
    Route::post('promotions/{promotion}/toggle', [PromotionController::class, 'toggleEnabled']);
    Route::apiResource('promotions.items', PromotionItemController::class);

    // Company
    Route::get('company', [CompanyController::class, 'index']);
    Route::post('company', [CompanyController::class, 'store']);
    Route::get('company/{company}', [CompanyController::class, 'show']);
    Route::put('company/{company}', [CompanyController::class, 'update']);

    // Loyalty
    Route::apiResource('loyalty', LoyaltyController::class)->only(['index', 'store', 'show', 'destroy']);
    Route::post('loyalty/{card}/earn', [LoyaltyController::class, 'earnPoints']);
    Route::post('loyalty/{card}/redeem', [LoyaltyController::class, 'redeemPoints']);
    Route::get('loyalty/{card}/transactions', [LoyaltyController::class, 'transactionHistory']);

    // Floor Plans
    Route::apiResource('floor-plans', FloorPlanController::class);
    Route::post('floor-plans/{floorPlan}/tables', [FloorPlanController::class, 'addTable']);
    Route::put('floor-plans/{floorPlan}/tables/{table}', [FloorPlanController::class, 'updateTable']);
    Route::delete('floor-plans/{floorPlan}/tables/{table}', [FloorPlanController::class, 'removeTable']);

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

    // Branches (accessible to all authenticated users)
    Route::apiResource('branches', \App\Http\Controllers\Api\TenantController::class);
    Route::post('branches/{branch}/switch', [\App\Http\Controllers\Api\TenantController::class, 'switch']);
});
