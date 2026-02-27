<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CartController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// ==========================================================================
// AUTHENTICATED USER
// ==========================================================================

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// ==========================================================================
// CART API (Public - uses session/cookie for guest users)
// ==========================================================================

Route::prefix('v1')->group(function () {
    
    // Cart operations (with session support for guest users)
    Route::middleware('web')->prefix('cart')->group(function () {
        Route::get('/', [CartController::class, 'index']);
        Route::post('/add', [CartController::class, 'add']);
        Route::put('/item/{itemId}', [CartController::class, 'update']);
        Route::delete('/item/{itemId}', [CartController::class, 'remove']);
        Route::delete('/clear', [CartController::class, 'clear']);
        Route::post('/coupon', [CartController::class, 'applyCoupon']);
        Route::delete('/coupon', [CartController::class, 'removeCoupon']);
        Route::get('/count', [CartController::class, 'count']);
    });
    
    // Address operations (with session support for authenticated users)
    Route::middleware('web')->prefix('addresses')->group(function () {
        Route::get('/', [App\Http\Controllers\Api\AddressController::class, 'index']);
        Route::post('/', [App\Http\Controllers\Api\AddressController::class, 'store']);
        Route::put('/{id}', [App\Http\Controllers\Api\AddressController::class, 'update']);
        Route::delete('/{id}', [App\Http\Controllers\Api\AddressController::class, 'destroy']);
        Route::post('/{id}/set-default', [App\Http\Controllers\Api\AddressController::class, 'setDefault']);
    });
    
    // Wishlist operations (with session support for authenticated users)
    Route::middleware('web')->prefix('wishlist')->group(function () {
        Route::get('/', [App\Http\Controllers\Api\WishlistController::class, 'index']);
        Route::post('/add', [App\Http\Controllers\Api\WishlistController::class, 'add']);
        Route::delete('/{productId}', [App\Http\Controllers\Api\WishlistController::class, 'remove']);
        Route::post('/toggle', [App\Http\Controllers\Api\WishlistController::class, 'toggle']);
        Route::get('/check/{productId}', [App\Http\Controllers\Api\WishlistController::class, 'check']);
    });
    
    // Review operations (with session support for authenticated users)
    Route::middleware('web')->prefix('reviews')->group(function () {
        Route::get('/product/{productId}', [App\Http\Controllers\Api\ReviewController::class, 'index']);
        Route::post('/', [App\Http\Controllers\Api\ReviewController::class, 'store']);
        Route::put('/{id}', [App\Http\Controllers\Api\ReviewController::class, 'update']);
        Route::delete('/{id}', [App\Http\Controllers\Api\ReviewController::class, 'destroy']);
        Route::get('/can-review/{productId}', [App\Http\Controllers\Api\ReviewController::class, 'canReview']);
    });
    
    // Search operations (public)
    Route::middleware('web')->prefix('search')->group(function () {
        Route::get('/autocomplete', [App\Http\Controllers\Api\SearchController::class, 'autocomplete']);
        Route::get('/', [App\Http\Controllers\Api\SearchController::class, 'search']);
        Route::get('/popular', [App\Http\Controllers\Api\SearchController::class, 'popularSearches']);
    });
    
    // Products API for infinite scroll
    Route::middleware('web')->get('/products', [App\Http\Controllers\Api\ProductController::class, 'index']);

    // Delivery Estimation
    Route::middleware('web')->get('/delivery/estimate', [App\Http\Controllers\Api\DeliveryController::class, 'estimate']);
    
});

// ==========================================================================
// FUTURE API ENDPOINTS
// ==========================================================================

// Product search and filtering
// Route::prefix('v1')->group(function () {
//     Route::get('/products', [ProductApiController::class, 'index']);
//     Route::get('/products/search', [ProductApiController::class, 'search']);
//     Route::get('/products/{id}', [ProductApiController::class, 'show']);
//     Route::get('/categories', [CategoryApiController::class, 'index']);
// });

// Wishlist operations (authenticated)
// Route::middleware('auth:sanctum')->prefix('v1')->group(function () {
//     Route::get('/wishlist', [WishlistController::class, 'index']);
//     Route::post('/wishlist', [WishlistController::class, 'add']);
//     Route::delete('/wishlist/{id}', [WishlistController::class, 'remove']);
// });
