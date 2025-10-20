<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\MealController;
use App\Http\Controllers\MealPreparationController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WeeklyMenuController;
use App\Http\Controllers\WeeklyPlanController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PlanController;

//  Public routes

Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);
Route::post('forget-password', [AuthController::class, 'forgetPassword']);

// Public Category Routes
Route::get('categories', [CategoryController::class, 'index']);
Route::get('categories/active', [CategoryController::class, 'active']);
Route::get('categories/slug/{slug}', [CategoryController::class, 'getBySlug']);
Route::get('categories/{id}', [CategoryController::class, 'show']);

// Public Meal Routes
Route::get('meals', [MealController::class, 'index']);
Route::get('meals/categories', [MealController::class, 'getCategories']);
Route::get('meals/featured', [MealController::class, 'featured']);
Route::get('meals/diet', [MealController::class, 'getByDiet']);
Route::get('meals/slug/{slug}', [MealController::class, 'getBySlug']);
Route::get('meals/{id}', [MealController::class, 'show']);

// Public Weekly Menu Routes
Route::get('weekly-menus', [WeeklyMenuController::class, 'index']);
Route::get('weekly-menus/current', [WeeklyMenuController::class, 'getCurrentWeek']);
Route::get('weekly-menus/upcoming', [WeeklyMenuController::class, 'getUpcoming']);
Route::get('weekly-menus/{id}', [WeeklyMenuController::class, 'show']);
Route::get('weekly-menus/{id}/meals', [WeeklyMenuController::class, 'getMeals']);

// Protected routes
Route::group(['middleware' => ['auth:sanctum']], function (): void {

    Route::post('logout', [AuthController::class, 'logout']);
    Route::resource('orders', OrderController::class);

    // Meal Preparation Routes (Admin/Manager/Chef)
    Route::get('meal-preparations', [MealPreparationController::class, 'index']);
    Route::get('meal-preparations/today', [MealPreparationController::class, 'today']);
    Route::get('meal-preparations/upcoming', [MealPreparationController::class, 'upcoming']);
    Route::get('meal-preparations/statistics', [MealPreparationController::class, 'statistics']);
    Route::get('meal-preparations/status/{status}', [MealPreparationController::class, 'byStatus']);
    Route::get('meal-preparations/{id}', [MealPreparationController::class, 'show']);
    Route::patch('meal-preparations/{id}/status', [MealPreparationController::class, 'updateStatus']);
    Route::patch('meal-preparations/{id}/notes', [MealPreparationController::class, 'updateNotes']);

    // Protected Category Routes (Admin/Manager only)
    Route::post('categories', [CategoryController::class, 'store']);
    Route::put('categories/{id}', [CategoryController::class, 'update']);
    Route::delete('categories/{id}', [CategoryController::class, 'destroy']);
    Route::post('categories/upload-image', [CategoryController::class, 'uploadImage']);
    Route::delete('categories/delete-image', [CategoryController::class, 'deleteImage']);

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

    // Protected Weekly Menu Routes (Admin/Manager only)
    Route::post('weekly-menus', [WeeklyMenuController::class, 'store']);
    Route::put('weekly-menus/{id}', [WeeklyMenuController::class, 'update']);
    Route::delete('weekly-menus/{id}', [WeeklyMenuController::class, 'destroy']);
    Route::post('weekly-menus/{id}/meals', [WeeklyMenuController::class, 'attachMeals']);
    Route::delete('weekly-menus/{id}/meals/{mealId}', [WeeklyMenuController::class, 'detachMeal']);
    Route::post('weekly-menus/{id}/publish', [WeeklyMenuController::class, 'publish']);

    // User Management Routes (Admin only)
    Route::get('users', [UserController::class, 'index']);
    Route::post('users', [UserController::class, 'store']);
    Route::get('users/{user}', [UserController::class, 'show']);
    Route::put('users/{user}', [UserController::class, 'update']);
    Route::delete('users/{user}', [UserController::class, 'destroy']);

    // Order Management Routes (Admin only)
    Route::apiResource('orders', OrderController::class);
    Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus']);
    // PLAN
    Route::apiResource('plans', PlanController::class);
});
