<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by RouteServiceProvider and all of them will
| be assigned to the "api" middleware group.
|
*/

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware('auth:api')->group(function () {
    // Authentication
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
    });
    // Orders
    Route::apiResource('orders', OrderController::class);
    // Payments
    Route::get('/payments', [PaymentController::class, 'index']);
    Route::get('/orders/{order}/payments', [PaymentController::class, 'orderPayments']);
    Route::post('/orders/{order}/payments', [PaymentController::class, 'processPayment']);
});
