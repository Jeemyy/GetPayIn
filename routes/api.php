<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\HoldController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PaymentController;

// Requirements:
Route::name('api.')->group(function () {
    // 1- GET /api/products/{id}
    Route::name('product')->controller(ProductController::class)->group(function () {
        Route::get('/product/{productId}', 'getProductById');
    });
    // 2- POST /api/holds { product_id, qty }
    Route::name('create.hold')->controller(HoldController::class)->group(function () {
        Route::post('/holds', 'createHold');
    });
    // 3- POST /api/orders { hold_id }
        Route::name('create.order')->controller(OrderController::class)->group(function () {
            Route::post('orders', 'createOrder');
    });
    // 4- POST /api/payments/webhook
    Route::name('create.payment.webhook')->controller(PaymentController::class)->group(function () {
        Route::post('/payments/webhook', 'createPaymentWebHook');
    });
});
