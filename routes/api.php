<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\HoldController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PaymentController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


// Requirements:
    // 1- GET /api/products/{id}
Route::get('/product/{productId}', [ProductController::class, 'getProductById']);
    // 2- POST /api/holds { product_id, qty }
Route::post('/holds', [HoldController::class, 'createHold']);
    // 3- POST /api/orders { hold_id }
Route::post('orders', [OrderController::class, 'createOrder']);
    // 4- POST /api/payments/webhook
Route::post('/payments/webhook', [PaymentController::class, 'createPaymentWebHook']);