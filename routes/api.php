<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\MealController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

//  Public routes

Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);
Route::post('forget-password', [AuthController::class, 'forgetPassword']);
Route::resource('products', ProductController::class);

// Public Meal Routes
Route::get('meals', [MealController::class, 'index']);
Route::get('meals/categories', [MealController::class, 'getCategories']);
Route::get('meals/featured', [MealController::class, 'featured']);
Route::get('meals/diet', [MealController::class, 'getByDiet']);
Route::get('meals/slug/{slug}', [MealController::class, 'getBySlug']);
Route::get('meals/{id}', [MealController::class, 'show']);

// Protected routes
Route::group(['middleware' => ['auth:sanctum']], function (): void {

    Route::post('logout', [AuthController::class, 'logout']);
    Route::resource('orders', OrderController::class);
    
    // Protected Meal Routes (Admin/Manager only)
    Route::post('meals', [MealController::class, 'store']);
    Route::put('meals/{id}', [MealController::class, 'update']);
    Route::delete('meals/{id}', [MealController::class, 'destroy']);
    Route::post('meals/{id}/restore', [MealController::class, 'restore']);
    Route::delete('meals/{id}/force', [MealController::class, 'forceDelete']);
    
    // Image Upload Routes
    Route::post('meals/upload-image', [MealController::class, 'uploadImage']);
    Route::post('meals/upload-gallery', [MealController::class, 'uploadGalleryImages']);
    Route::delete('meals/delete-image', [MealController::class, 'deleteImage']);
});

 
