<?php

use App\Http\Controllers\SubReportController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\WaiterController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\CashDrawerController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\CompanyInfoController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ColorController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\SizeController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\OwnerController;
use App\Http\Controllers\ServiceChargeController;
use App\Http\Controllers\TransactionHistoryController;
use App\Http\Controllers\StockTransactionController;
use App\Http\Controllers\BankServiceChargeController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Carbon\Carbon;

use Illuminate\Support\Facades\Gate;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get('/', function () {
//     return Inertia::render('Welcome', [
//         'canLogin' => Route::has('login'),
//         'canRegister' => Route::has('register'),
//         'laravelVersion' => Application::VERSION,
//         'phpVersion' => PHP_VERSION,
//     ]);
// });
Route::get('/dashboard', function () {
    return Inertia::location(route('dashboard'));
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/', function () {
        if (Gate::allows('hasRole', ['Cashier'])) {
            return redirect()->route('pos.index');
        }
        if (Gate::allows('hasRole', ['Waiter'])) {
            return redirect()->route('waiter.index');
        }

        return Inertia::render('Dashboard');

    })->name('dashboard');
});

    Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('categories', CategoryController::class);
    Route::put('/categories/{categpry}', [CategoryController::class, 'update'])->name('categpry.update');


    Route::resource('products', ProductController::class);
    Route::resource('suppliers', SupplierController::class);
    Route::post('suppliers/{supplier}', [SupplierController::class, 'update']);
    Route::post('products/{product}', [ProductController::class, 'update']);
    Route::post('products-variant', [ProductController::class, 'productVariantStore'])->name('productVariant');
    // Route::resource('company-info', CompanyInfoController::class)->name('companyInfo.index');
    Route::get('/company-info', [CompanyInfoController::class, 'index'])->name('companyInfo.index');
    Route::put('/company-info/{companyInfo}', [CompanyInfoController::class, 'update'])->name('companyInfo.update');
    Route::post('/company-info/{companyInfo}', [CompanyInfoController::class, 'update']);


    Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
    Route::post('/pos', [PosController::class, 'getProduct'])->name('pos.getProduct');
    Route::post('/pos/get-owner-discount', [PosController::class, 'getOwnerDiscount'])->name('pos.getOwnerDiscount');
    Route::post('/pos/get-products-by-ids', [PosController::class, 'getProductsByIds'])->name('pos.getProductsByIds');
    Route::get('/pos/next-order-id', [PosController::class, 'getNextOrderId'])->name('pos.nextOrderId');
    Route::get('/pos/last-orders', [PosController::class, 'getLastOrders'])->name('pos.lastOrders');
    Route::post('/pos/save-customer', [CustomerController::class, 'saveCustomer'])->name('pos.saveCustomer');
    Route::get('/pos/waiter-orders', [PosController::class, 'getWaiterOrders'])->name('pos.waiterOrders');
    Route::post('/pos/waiter-orders/{orderId}/confirm', [PosController::class, 'confirmWaiterOrder'])->name('pos.confirmWaiterOrder');
    Route::post('/get-coupon', [PosController::class, 'getCoupon'])->name('pos.getCoupon');
    Route::post('/pos/submit', [PosController::class, 'submit'])->name('pos.checkout');
    Route::post('/pos/tables', [PosController::class, 'storeTable'])->name('pos.storeTable');
    Route::delete('/pos/tables/{id}', [PosController::class, 'destroyTable'])->name('pos.destroyTable');

    // Waiter Routes
    Route::get('/waiter', [WaiterController::class, 'index'])->name('waiter.index');
    Route::post('/waiter/get-products', [WaiterController::class, 'getProduct'])->name('waiter.getProduct');
    Route::post('/waiter/submit-order', [WaiterController::class, 'submitOrder'])->name('waiter.submitOrder');
    Route::get('/waiter/orders', [WaiterController::class, 'getWaiterOrders'])->name('waiter.getOrders');
    Route::post('/waiter/orders/{orderId}/cancel', [WaiterController::class, 'cancelOrder'])->name('waiter.cancelOrder');

    Route::resource('payment', PaymentController::class);
    Route::resource('cash-drawer', CashDrawerController::class)->names([
        'index' => 'cashDrawer.index',
        'create' => 'cashDrawer.create',
        'store' => 'cashDrawer.store',
        'show' => 'cashDrawer.show',
        'edit' => 'cashDrawer.edit',
        'update' => 'cashDrawer.update',
    ]);
    Route::get('/cash-drawer/api/open', [CashDrawerController::class, 'getOpen'])->name('cashDrawer.getOpen');
    Route::get('/cash-drawer/report/activity', [CashDrawerController::class, 'activityReport'])->name('cashDrawer.activityReport');
    Route::get('/reports/cash-drawer', [ReportController::class, 'cashDrawerReport'])->name('reports.cashDrawer');
    Route::get('/reports/user-activity', [ReportController::class, 'userActivityReport'])->name('reports.userActivity');
    Route::get('/reports/category-wise-sales', [SubReportController::class, 'categoryWiseSales'])->name('reports.categoryWiseSales');
    Route::get('/reports/order-type-report',   [SubReportController::class, 'orderTypeReport'])->name('reports.orderTypeReport');
    Route::get('/reports/bar-sales-report',    [SubReportController::class, 'barSalesReport'])->name('reports.barSalesReport');
    Route::resource('reports', ReportController::class);
    Route::post('/customers/check', [CustomerController::class, 'checkCustomer'])->name('customers.check');
    Route::resource('customers', CustomerController::class);
    Route::resource('colors', ColorController::class);
    Route::resource('coupons', CouponController::class);
    Route::resource('owners', OwnerController::class);

    Route::post('/owners/import', [OwnerController::class, 'import'])->name('owners.import'); // optional

Route::get('/test-owners', function () {
    Carbon::setTestNow(Carbon::create(2025, 9, 1, 10)); // pretend it's Sep 1, 2025
    return app(\App\Http\Controllers\OwnerController::class)->index();
});

    Route::resource('sizes', SizeController::class);
    Route::resource('employees', EmployeeController::class);
    Route::resource('stock-transition', StockTransactionController::class);
    Route::resource('transactionHistory', TransactionHistoryController::class );
    Route::post('/transactions/delete', [TransactionHistoryController::class, 'destroy'])->name('transactions.delete');
    Route::post('/transactions/bulk-delete', [TransactionHistoryController::class, 'bulkDelete']) ->name('transactions.bulkDelete');
    Route::resource('delivery', DeliveryController::class);
    Route::resource('service-charge', ServiceChargeController::class);
    Route::resource('bank-service-charge', BankServiceChargeController::class);
    // Route::get('/stock-transition', [PosController::class, 'index'])->name('pos.index');
    // Route::post('/stock-transition', [PosController::class, 'getProduct'])->name('pos.getProduct');

    Route::get('/add_promotion', [ProductController::class, 'addPromotion']);
    Route::post('/submit_promotion', [ProductController::class, 'submitPromotion']);
    Route::get('/products/{id}/promotion-items', [ProductController::class, 'getPromotionItems']);
    Route::post('/api/products', [ProductController::class, 'fetchProducts']);
    Route::post('/api/top-categories', [CategoryController::class, 'topCategories']);
    Route::post('/api/check-customer', [CustomerController::class, 'checkCustomer']);


});

Route::get('/barcode/{id}', [CategoryController::class, 'showBarcode']);

// ─── Public Food Menu (no login required) ────────────────────────────────────
Route::get('/menu', [MenuController::class, 'index'])->name('menu.index');
