<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\WishlistController;
use Illuminate\Support\Facades\Route;


// ========================
// AUTH ROUTES
// ========================
// Public auth routes with rate limiting
Route::post('register', [AuthController::class, 'register'])->middleware('rateLimiter:auth');
Route::post('login', [AuthController::class, 'login'])->middleware('rateLimiter:auth');

// Auth routes with prefix
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register'])->middleware('rateLimiter:auth');
    Route::post('login', [AuthController::class, 'login'])->middleware('rateLimiter:auth');
    
    // Protected auth routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('user', [AuthController::class, 'user']);
    });
});


// ========================
// PRODUCT ROUTES (PUBLIC)
// ========================
Route::prefix('products')
    ->middleware('rateLimiter:products')
    ->group(function () {
        Route::get('/', [ProductController::class, 'getAllProducts']);
        Route::get('{id}', [ProductController::class, 'getAProduct']);
        
        // Protected product management routes
        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/', [ProductController::class, 'createProduct']);
            Route::put('{id}', [ProductController::class, 'updateProduct']);
            Route::delete('{id}', [ProductController::class, 'deleteProduct']);
        });
    });


// ========================
// WISHLIST ROUTES (PROTECTED)
// ========================


Route::prefix('wishlist')
->middleware(['auth:sanctum','rateLimiter:wishlist'])
->group(function () {
        Route::post('/', [WishlistController::class, 'addProductToWishlist']);
        Route::get('/', [WishlistController::class, 'getUserWishLists']);
        Route::delete('/{productId}', [WishlistController::class, 'removeAnItemFromWishlist']);
        Route::delete('/', [WishlistController::class, 'clearUserWishList']);
    });
