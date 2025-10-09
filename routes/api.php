<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

//  Public routes

Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);
Route::post('forget-password', [AuthController::class, 'forgetPassword']);
Route::resource('products', ProductController::class);

// Protected routes
Route::group(['middleware' => ['auth:sanctum']], function (): void {

    Route::post('logout', [AuthController::class, 'logout']);
    Route::resource('orders', OrderController::class);
});

// i want to create api config but this api work when app in mode debug

Route::get('config', function (): void {
    // test if app in debug mode

    if (env('APP_DEBUG') == true) {

        Artisan::call('migrate:refresh');
        Artisan::call('db:seed');
    }
});
