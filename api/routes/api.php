<?php

use App\Http\Controllers\AddressController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show'])
    ->whereNumber('id');

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register'])
        ->middleware('throttle:10,1')
        ->block();

    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:10,1')
        ->block();

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout'])->block();
    });
});

Route::get('/cart', [CartController::class, 'show'])->block();
Route::post('/cart/items', [CartController::class, 'store'])->block();

Route::patch('/cart/items/{id}', [CartController::class, 'update'])
    ->whereNumber('id')
    ->block();

Route::delete('/cart/items/{id}', [CartController::class, 'destroy'])
    ->whereNumber('id')
    ->block();

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/addresses', [AddressController::class, 'index']);
    Route::post('/addresses', [AddressController::class, 'store']);

    Route::post('/orders', [OrderController::class, 'store'])->block();
});