<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// Customer/Storefront Controller
use App\Http\Controllers\Customer\StorefrontController;
use App\Http\Controllers\Customer\CustomerAuthController;

// Seller Controller
use App\Http\Controllers\Seller\SellerDashboardController;

// Admin Controllers
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\SigninlogController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\LogActivityController;
use App\Http\Controllers\MarketplaceAdminController;

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

// ==========================================================================
// PUBLIC MARKETPLACE ROUTES (Storefront)
// ==========================================================================

Route::get('/', [StorefrontController::class, 'home'])->name('home');
Route::get('/products', [StorefrontController::class, 'products'])->name('marketplace.products');
Route::get('/product/{slug}', [StorefrontController::class, 'productDetail'])->name('marketplace.product.detail');
Route::get('/cart', [StorefrontController::class, 'cart'])->name('marketplace.cart');

// Checkout routes
Route::get('/checkout', [App\Http\Controllers\Customer\CheckoutController::class, 'index'])->name('marketplace.checkout');
Route::post('/checkout/create-razorpay-order', [App\Http\Controllers\Customer\CheckoutController::class, 'createRazorpayOrder'])->name('checkout.create-razorpay-order');
Route::post('/checkout/verify-payment', [App\Http\Controllers\Customer\CheckoutController::class, 'verifyPayment'])->name('checkout.verify-payment');
Route::get('/order-confirmation/{orderNumber}', [App\Http\Controllers\Customer\CheckoutController::class, 'confirmation'])->name('order.confirmation');

// Customer Auth (OTP-based)
Route::prefix('customer')->group(function () {
    Route::get('/login', [CustomerAuthController::class, 'showLoginForm'])->name('customer.login');
    Route::get('/register', [CustomerAuthController::class, 'showRegisterForm'])->name('customer.register');
    Route::post('/send-otp', [CustomerAuthController::class, 'sendOtp'])->name('customer.send-otp');
    Route::post('/verify-otp', [CustomerAuthController::class, 'verifyOtp'])->name('customer.verify-otp');
    Route::post('/resend-otp', [CustomerAuthController::class, 'resendOtp'])->name('customer.resend-otp');
    Route::post('/logout', [CustomerAuthController::class, 'logout'])->name('customer.logout');
    Route::get('/check', [CustomerAuthController::class, 'check'])->name('customer.check');
});

// Customer Account
Route::prefix('account')->group(function () {
    Route::get('/orders', [StorefrontController::class, 'myOrders'])->name('account.orders');
    Route::get('/order/{orderNumber}', [StorefrontController::class, 'orderDetail'])->name('account.order.detail');
    Route::get('/track-order', [StorefrontController::class, 'orderTracking'])->name('account.track');
    Route::get('/profile', [StorefrontController::class, 'profile'])->name('account.profile');
    Route::get('/wishlist', [StorefrontController::class, 'wishlist'])->name('account.wishlist');
});

// Static Pages
Route::get('/about-us', [StorefrontController::class, 'about'])->name('about');
Route::get('/contact-us', [StorefrontController::class, 'contact'])->name('contact');
Route::get('/faq', [StorefrontController::class, 'faq'])->name('faq');
Route::get('/terms-conditions', [StorefrontController::class, 'terms'])->name('terms');
Route::get('/privacy-policy', [StorefrontController::class, 'privacy'])->name('privacy');
Route::get('/return-policy', [StorefrontController::class, 'returns'])->name('returns');
Route::get('/page/{slug}', [StorefrontController::class, 'page'])->name('marketplace.page');

// ==========================================================================
// SELLER PORTAL ROUTES
// ==========================================================================

use App\Http\Controllers\Seller\SellerAuthController;
use App\Http\Controllers\Seller\SellerDocumentController;
use App\Http\Controllers\Seller\SellerBankAccountController;

// Seller Landing Page (Public)
Route::get('/seller', function () {
    // If seller is already logged in, redirect to dashboard
    if (Auth::check() && Auth::user()->isSeller()) {
        return redirect()->route('seller.dashboard');
    }
    return view('seller.landing');
})->name('seller.landing');

// Seller Guest Routes (Registration & Login)
Route::prefix('seller')->middleware('guest')->group(function () {
    Route::get('/register', [SellerAuthController::class, 'showRegistrationForm'])->name('seller.register');
    Route::post('/register', [SellerAuthController::class, 'register'])->name('seller.register.post');
    Route::get('/login', [SellerAuthController::class, 'showLoginForm'])->name('seller.login');
    Route::post('/login', [SellerAuthController::class, 'login'])->name('seller.login.post');
    
    // Password Reset
    Route::get('/forgot-password', [SellerAuthController::class, 'showForgotPasswordForm'])->name('seller.password.request');
    Route::post('/forgot-password', [SellerAuthController::class, 'sendResetLink'])->name('seller.password.email');
    Route::get('/reset-password/{token}', [SellerAuthController::class, 'showResetPasswordForm'])->name('seller.password.reset');
    Route::post('/reset-password', [SellerAuthController::class, 'resetPassword'])->name('seller.password.update');
});

// Seller Authenticated Routes
Route::prefix('seller')->middleware(['auth'])->group(function () {
    // Email Verification
    Route::get('/verify-email', [SellerAuthController::class, 'showVerifyEmail'])->name('seller.verify-email');
    Route::post('/verify-email', [SellerAuthController::class, 'verifyEmail'])->name('seller.verify-email.post');
    Route::post('/resend-verification', [SellerAuthController::class, 'resendVerificationCode'])->name('seller.resend-verification');
    
    // Logout
    Route::post('/logout', [SellerAuthController::class, 'logout'])->name('seller.logout');
    
    // Dashboard & Main Pages
    Route::get('/dashboard', [SellerDashboardController::class, 'dashboard'])->name('seller.dashboard');
    
    // Products Management
    Route::get('/products', [\App\Http\Controllers\Seller\SellerProductController::class, 'index'])->name('seller.products');
    Route::get('/products/create', [\App\Http\Controllers\Seller\SellerProductController::class, 'create'])->name('seller.products.create');
    Route::post('/products', [\App\Http\Controllers\Seller\SellerProductController::class, 'store'])->name('seller.products.store');
    Route::get('/products/{product}/edit', [\App\Http\Controllers\Seller\SellerProductController::class, 'edit'])->name('seller.products.edit');
    Route::put('/products/{product}', [\App\Http\Controllers\Seller\SellerProductController::class, 'update'])->name('seller.products.update');
    Route::delete('/products/{product}', [\App\Http\Controllers\Seller\SellerProductController::class, 'destroy'])->name('seller.products.destroy');
    
    Route::get('/orders', [SellerDashboardController::class, 'orders'])->name('seller.orders');
    Route::get('/orders/{id}', [SellerDashboardController::class, 'orderDetail'])->name('seller.orders.detail');
    Route::get('/orders/{id}/invoice', [SellerDashboardController::class, 'invoice'])->name('seller.orders.invoice');
    Route::post('/orders/{id}/update-status', [SellerDashboardController::class, 'updateStatus'])->name('seller.orders.update-status');
    Route::post('/orders/{id}/update-status', [SellerDashboardController::class, 'updateStatus'])->name('seller.orders.update-status');
    Route::post('/orders/{id}/cancel', [SellerDashboardController::class, 'cancelOrder'])->name('seller.orders.cancel');
    
    // Shipping Integration
    Route::get('/orders/{id}/shipping-rates', [SellerDashboardController::class, 'fetchShippingRates'])->name('seller.orders.shipping-rates');
    Route::post('/orders/{id}/book-shipping', [SellerDashboardController::class, 'bookShipment'])->name('seller.orders.book-shipping');

    Route::get('/finance', [SellerDashboardController::class, 'finance'])->name('seller.finance');
    Route::post('/finance/payout-request', [SellerDashboardController::class, 'createPayoutRequest'])->name('seller.finance.payout-request');
    Route::get('/finance/ledger', [SellerDashboardController::class, 'financeLedger'])->name('seller.finance.ledger');

    // Settings
    Route::get('/settings', [SellerDashboardController::class, 'settings'])->name('seller.settings');
    Route::put('/settings', [SellerDashboardController::class, 'updateSettings'])->name('seller.settings.update');
    
    // Documents Management
    Route::get('/documents', [SellerDocumentController::class, 'index'])->name('seller.documents');
    Route::post('/documents', [SellerDocumentController::class, 'store'])->name('seller.documents.store');
    Route::delete('/documents/{document}', [SellerDocumentController::class, 'destroy'])->name('seller.documents.destroy');
    Route::get('/documents/{document}/download', [SellerDocumentController::class, 'download'])->name('seller.documents.download');
    
    // Bank Accounts Management
    Route::get('/bank-accounts', [SellerBankAccountController::class, 'index'])->name('seller.bank-accounts');
    Route::post('/bank-accounts', [SellerBankAccountController::class, 'store'])->name('seller.bank-accounts.store');
    Route::put('/bank-accounts/{bankAccount}', [SellerBankAccountController::class, 'update'])->name('seller.bank-accounts.update');
    Route::delete('/bank-accounts/{bankAccount}', [SellerBankAccountController::class, 'destroy'])->name('seller.bank-accounts.destroy');
    Route::post('/bank-accounts/{bankAccount}/set-primary', [SellerBankAccountController::class, 'setPrimary'])->name('seller.bank-accounts.set-primary');
    
    // Brand Management
    Route::get('/brands', [App\Http\Controllers\Seller\SellerBrandController::class, 'index'])->name('seller.brands');
    Route::post('/brands', [App\Http\Controllers\Seller\SellerBrandController::class, 'store'])->name('seller.brands.store');
    Route::put('/brands/{brand}', [App\Http\Controllers\Seller\SellerBrandController::class, 'update'])->name('seller.brands.update');
    Route::delete('/brands/{brand}', [App\Http\Controllers\Seller\SellerBrandController::class, 'destroy'])->name('seller.brands.destroy');
    Route::get('/brands/{brand}/download-proof', [App\Http\Controllers\Seller\SellerBrandController::class, 'downloadProof'])->name('seller.brands.download-proof');

});

// ==========================================================================
// ADMIN ROUTES (Vue/Inertia)
// ==========================================================================

Route::prefix('admin')->middleware(['auth', '2fa'])->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
    
   /* // Marketplace Admin Routes (Blade)
    Route::get('/marketplace/dashboard', [MarketplaceAdminController::class, 'dashboard'])->name('admin.marketplace.dashboard');
    Route::get('/marketplace/sellers', [MarketplaceAdminController::class, 'sellers'])->name('admin.marketplace.sellers');
    Route::get('/marketplace/products', [MarketplaceAdminController::class, 'products'])->name('admin.marketplace.products');
    */
    // User Management
    Route::resource('user', UserController::class);
    Route::delete('userauthdestroy', [UserController::class, 'authDestroy'])->name('user.authDestroy');
    Route::get('user/{user}/permissions', [UserController::class, 'permissions'])->name('user.permissions');
    Route::put('/user-permissions-update', [UserController::class, 'permissionsUpdate'])->name('user.permissionsUpdate');

    // Roles & Permissions
    Route::resource('role', RoleController::class);
    Route::resource('permission', PermissionController::class);

    // Activity Logs
    Route::resource('activityLog', LogActivityController::class);
    Route::delete('activityLog-bulk-destroy', [LogActivityController::class, 'bulkDestroy'])->name('activityLog.bulkDestroy');
    Route::put('activityLog-field-update', [LogActivityController::class, 'fieldUpdate'])->name('activityLog.fieldUpdate');

    // Settings
    Route::resource('setting', SettingController::class);
    Route::get('setting-list', [SettingController::class, 'list'])->name('setting.list');
    Route::delete('setting-auth-destroy', [SettingController::class, 'authDestroy'])->name('setting.authDestroy');
    Route::put('/setting-bulk-update', [SettingController::class, 'bulkUpdate'])->name('setting.bulkUpdate');

    // Tax Rates
    Route::resource('tax-rate', \App\Http\Controllers\Admin\TaxRateController::class);

    // Signin Logs
    Route::resource('signinLog', SigninlogController::class);
    Route::delete('signinLog-bulk-destroy', [SigninlogController::class, 'bulkDestroy'])->name('signinLog.bulkDestroy');
    
    // Profile
    Route::get('/profile', [UserController::class, 'profile'])->name('profile.profile');
    Route::put('/update-profile', [UserController::class, 'updateProfile'])->name('profile.updateProfile');
    
    // ==========================================================================
    // ECOMMERCE MODULES
    // ==========================================================================
    
    // User & Seller Management
    Route::resource('seller', \App\Http\Controllers\Admin\SellerManagementController::class);
    Route::post('seller/{id}/approve', [\App\Http\Controllers\Admin\SellerManagementController::class, 'approve'])->name('seller.approve');
    Route::post('seller/{id}/suspend', [\App\Http\Controllers\Admin\SellerManagementController::class, 'suspend'])->name('seller.suspend');
    Route::get('seller/{id}/overview', [\App\Http\Controllers\Admin\SellerManagementController::class, 'overview'])->name('seller.overview');
    Route::get('seller/{id}/documents', [\App\Http\Controllers\Admin\SellerManagementController::class, 'documents'])->name('sellerDocument.index');
    Route::post('seller/{seller}/documents/{document}/verify', [\App\Http\Controllers\Admin\SellerManagementController::class, 'verifyDocument'])->name('sellerDocument.verify');
    Route::get('seller/{id}/banks', [\App\Http\Controllers\Admin\SellerManagementController::class, 'banks'])->name('sellerBank.index');
    Route::post('seller/{seller}/banks/{bank}/verify', [\App\Http\Controllers\Admin\SellerManagementController::class, 'verifyBank'])->name('sellerBank.verify');
    
    
    // Seller Brand Management
    Route::resource('sellerBrand', \App\Http\Controllers\Admin\SellerBrandController::class)->only(['index', 'show', 'destroy']);
    Route::post('sellerBrand/{id}/approve', [\App\Http\Controllers\Admin\SellerBrandController::class, 'approve'])->name('sellerBrand.approve');
    Route::post('sellerBrand/{id}/reject', [\App\Http\Controllers\Admin\SellerBrandController::class, 'reject'])->name('sellerBrand.reject');

    Route::resource('customer', \App\Http\Controllers\Admin\CustomerController::class);
    
    // Catalog Management
    Route::delete('category/bulk-destroy', [\App\Http\Controllers\Admin\CategoryController::class, 'bulkDestroy'])->name('category.bulkDestroy');
    Route::resource('category', \App\Http\Controllers\Admin\CategoryController::class);
    Route::delete('product/bulk-destroy', [\App\Http\Controllers\Admin\ProductController::class, 'bulkDestroy'])->name('product.bulkDestroy');
    Route::resource('product', \App\Http\Controllers\Admin\ProductController::class);
    Route::post('product/{id}/approve', [\App\Http\Controllers\Admin\ProductController::class, 'approve'])->name('product.approve');
    Route::post('product/{id}/reject', [\App\Http\Controllers\Admin\ProductController::class, 'reject'])->name('product.reject');
    Route::delete('brand/bulk-destroy', [\App\Http\Controllers\Admin\BrandController::class, 'bulkDestroy'])->name('brand.bulkDestroy');
    Route::resource('brand', \App\Http\Controllers\Admin\BrandController::class);
    
    // Variant Types Management
    Route::resource('variantType', \App\Http\Controllers\Admin\VariantTypeController::class);
    
    // Order Management
    Route::resource('order', \App\Http\Controllers\Admin\OrderController::class);
    Route::post('order/{id}/cancel', [\App\Http\Controllers\Admin\OrderController::class, 'cancel'])->name('order.cancel');
    Route::resource('dispute', \App\Http\Controllers\Admin\DisputeController::class);
    Route::post('dispute/{id}/resolve', [\App\Http\Controllers\Admin\DisputeController::class, 'resolve'])->name('dispute.resolve');
    Route::resource('refund', \App\Http\Controllers\Admin\RefundController::class);
    Route::post('refund/{id}/approve', [\App\Http\Controllers\Admin\RefundController::class, 'approve'])->name('refund.approve');
    Route::post('refund/{id}/reject', [\App\Http\Controllers\Admin\RefundController::class, 'reject'])->name('refund.reject');
    
    // Payments & Payouts
    Route::get('commission', [\App\Http\Controllers\Admin\CommissionController::class, 'index'])->name('commission.index');
    Route::get('commission/edit', [\App\Http\Controllers\Admin\CommissionController::class, 'edit'])->name('commission.edit');
    Route::put('commission', [\App\Http\Controllers\Admin\CommissionController::class, 'update'])->name('commission.update');
    Route::resource('payout', \App\Http\Controllers\Admin\PayoutController::class);
    Route::post('payout/{id}/approve', [\App\Http\Controllers\Admin\PayoutController::class, 'approve'])->name('payout.approve');
    Route::post('payout/{id}/reject', [\App\Http\Controllers\Admin\PayoutController::class, 'reject'])->name('payout.reject');
    Route::post('payout/{id}/process', [\App\Http\Controllers\Admin\PayoutController::class, 'process'])->name('payout.process');
    
    // Promotions
    Route::resource('coupon', \App\Http\Controllers\Admin\CouponController::class);
    Route::resource('flashDeal', \App\Http\Controllers\Admin\FlashDealController::class);
    Route::resource('featuredProduct', \App\Http\Controllers\Admin\FeaturedProductController::class);
    Route::resource('banner', \App\Http\Controllers\Admin\BannerController::class);
    
    // Content Management
    Route::resource('page', \App\Http\Controllers\Admin\PageController::class);
    
    // Reports
    Route::get('salesReport', [\App\Http\Controllers\Admin\SalesReportController::class, 'index'])->name('salesReport.index');
    Route::post('salesReport/export', [\App\Http\Controllers\Admin\SalesReportController::class, 'export'])->name('salesReport.export');
    Route::get('sellerPerformance', [\App\Http\Controllers\Admin\SellerPerformanceController::class, 'index'])->name('sellerPerformance.index');
    Route::post('sellerPerformance/export', [\App\Http\Controllers\Admin\SellerPerformanceController::class, 'export'])->name('sellerPerformance.export');
    Route::get('revenueReport', [\App\Http\Controllers\Admin\RevenueReportController::class, 'index'])->name('revenueReport.index');
    Route::post('revenueReport/export', [\App\Http\Controllers\Admin\RevenueReportController::class, 'export'])->name('revenueReport.export');
});

// ==========================================================================
// UTILITY ROUTES
// ==========================================================================

Route::get('logs', [\Rap2hpoutre\LaravelLogViewer\LogViewerController::class, 'index']);

require __DIR__ . '/auth.php';
