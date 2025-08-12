<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Controller\AuthController;
use App\Http\Controllers\Controller\ProductController;
use App\Http\Controllers\Controller\CheckoutController;
use App\Http\Controllers\Controller\LoginDurationController;

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

// Public routes
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    
    // Products
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{product}', [ProductController::class, 'show']);
    
    // Checkout
    Route::post('/checkout/view', [CheckoutController::class, 'viewCheckout']);
    Route::post('/checkout', [CheckoutController::class, 'createOrder']);
    Route::post('/checkout/{order}/payment/simulate', [CheckoutController::class, 'simulatePayment']);
    
    // Login duration tracking
    Route::get('/login-duration/total', [LoginDurationController::class, 'total']);
    Route::get('/login-duration/sessions', [LoginDurationController::class, 'sessions']);
    Route::get('/login-duration/current', [LoginDurationController::class, 'current']);
});
