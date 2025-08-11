<?php

use App\Http\Controllers\Controller\AuthController;
use App\Http\Controllers\Controller\CheckoutController;
use App\Http\Controllers\Controller\LoginDurationController;
use App\Http\Controllers\Controller\ProductController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::post('/register',[AuthController::class,'register']);
Route::post('/login',[AuthController::class,'login']);

Route::middleware('auth:sanctum')->group(function(){
    Route::post('/logout',[AuthController::class,'logout']);

    // (1) checkout page data
    Route::get('/products',[ProductController::class,'index']);

    // (2)-(4) checkout & payment
    Route::post('/checkout/orders',[CheckoutController::class,'createOrder']);
    Route::post('/checkout/orders/{order}/pay/simulate',[CheckoutController::class,'simulatePayment']); // or Stripe flow

    // (5) login duration
    Route::get('/me/login-duration',[LoginDurationController::class,'total']);
});
