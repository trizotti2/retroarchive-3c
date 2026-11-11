<?php

use App\Http\Controllers\AddressController;
use App\Http\Controllers\AdminCategoryController;
use App\Http\Controllers\AdminOrderController;
use App\Http\Controllers\AdminProductController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show'])->whereNumber('id');

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:10,1')->block();
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1')->block();

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [AuthController::class, 'me'])->block();
        Route::post('/logout', [AuthController::class, 'logout'])->block();
    });
});

Route::get('/cart', [CartController::class, 'show'])->block();
Route::post('/cart/items', [CartController::class, 'store'])->block();
Route::patch('/cart/items/{id}', [CartController::class, 'update'])->whereNumber('id')->block();
Route::delete('/cart/items/{id}', [CartController::class, 'destroy'])->whereNumber('id')->block();

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', fn (Request $request) => $request->user())->block();
    Route::patch('/profile', [ProfileController::class, 'update'])->block();

    Route::get('/addresses', [AddressController::class, 'index']);
    Route::post('/addresses', [AddressController::class, 'store']);
    Route::patch('/addresses/{id}', [AddressController::class, 'update'])->whereNumber('id');
    Route::delete('/addresses/{id}', [AddressController::class, 'destroy'])->whereNumber('id');

    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders', [OrderController::class, 'store'])->block();
    Route::get('/orders/{id}', [OrderController::class, 'show'])->whereNumber('id');
    Route::post('/orders/{id}/pay', [OrderController::class, 'pay'])->whereNumber('id')->block();
    Route::post('/orders/{id}/cancel', [OrderController::class, 'cancel'])->whereNumber('id')->block();
});

Route::prefix('admin')->middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::apiResource('products', AdminProductController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::apiResource('categories', AdminCategoryController::class)->only(['store', 'update', 'destroy']);

    Route::get('/orders', [AdminOrderController::class, 'index']);
    Route::get('/users', [AdminUserController::class, 'index']);
    Route::patch('/orders/{id}/status', [AdminOrderController::class, 'update'])->whereNumber('id')->block();
});
